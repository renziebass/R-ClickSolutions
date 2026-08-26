<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\Env;
use Mariah\Core\HttpException;
use Mariah\Core\Slug;
use Mariah\Core\Validator;
use Mariah\Repositories\CategoryRepository;
use Mariah\Repositories\ServiceRepository;

/**
 * Bulk service import from a CSV file.
 *
 * Preview and commit are the SAME pipeline, differing only in whether the
 * final write stage runs. The browser re-uploads the file it still holds when
 * the operator confirms, so the server keeps no state between the two calls
 * and the preview cannot disagree with what is written.
 *
 * The write is all-or-nothing. Database::transaction() flattens nesting rather
 * than using savepoints, so "skip the bad rows and import the rest" cannot be
 * done safely here: a caught PDOException leaves the transaction in an
 * indeterminate state, and a deadlock rolls the whole thing back while later
 * statements carry on outside any transaction. Every row is therefore validated
 * before any row is written.
 *
 * Nothing is ever exported, so CSV formula injection ("=cmd|...") is not a
 * concern — the only CSV this app emits is the blank template, built in the
 * browser and containing no stored data.
 */
final class ServiceImporter
{
    /** @var array<string, int> slug => the line that claimed it */
    private array $claimedSlugs = [];

    /** @var array<int, int> most_loved_rank => the line that claimed it */
    private array $claimedRanks = [];

    private array $warnings = [];

    private ServiceRepository $services;
    private CategoryRepository $categories;

    private function __construct()
    {
        $this->services   = new ServiceRepository();
        $this->categories = new CategoryRepository();
    }

    /**
     * @param array $file       one entry from $_FILES
     * @param bool  $dryRun     true previews, false writes
     * @param mixed $confirmDigest the digest the preview returned, on commit
     */
    public static function run(array $file, bool $dryRun, mixed $confirmDigest = null): array
    {
        return (new self())->handle($file, $dryRun, $confirmDigest);
    }

    private function handle(array $file, bool $dryRun, mixed $confirmDigest): array
    {
        $this->assertUploadOk($file);

        $raw    = (string) file_get_contents($file['tmp_name']);
        $digest = hash('sha256', $raw);

        // The browser re-sends the same File object, so this always matches in
        // the happy path. It catches a file swapped in another tab between
        // previewing and confirming.
        if (!$dryRun && is_string($confirmDigest) && $confirmDigest !== '' && $confirmDigest !== $digest) {
            throw HttpException::conflict(
                'The file changed since it was previewed. Please preview it again.'
            );
        }

        [$headers, $rows, $ignored] = $this->readRows($raw);

        $plan = $this->plan($rows);

        $summary = [
            'rows'      => count($plan),
            'create'    => 0,
            'update'    => 0,
            'unchanged' => 0,
            'error'     => 0,
            'created'   => 0,
            'updated'   => 0,
        ];

        foreach ($plan as $row) {
            $summary[$row['action']]++;
        }

        $result = [
            'dry_run'   => $dryRun,
            'committed' => false,
            'file'      => [
                'name'   => $this->safeName($file['name'] ?? 'upload.csv'),
                'size'   => strlen($raw),
                'digest' => $digest,
            ],
            'columns'   => ['recognised' => $headers, 'ignored' => $ignored],
            'summary'   => $summary,
            // Deduped: a bad icon repeated down 500 rows is one problem, not
            // 500 warnings.
            'warnings'  => array_values(array_unique($this->warnings)),
            'rows'      => array_map([$this, 'publicRow'], $plan),
        ];

        if ($dryRun) {
            return $result;
        }

        if ($summary['error'] > 0) {
            $result['message'] = 'Nothing was imported. '
                . $summary['error'] . ' row(s) still have errors.';
            return $result;
        }

        if ($summary['create'] === 0 && $summary['update'] === 0) {
            $result['committed'] = true;
            $result['message']   = 'Every row already matched what is stored. Nothing changed.';
            return $result;
        }

        return array_merge($result, $this->writeAll($plan, $result));
    }

    // -----------------------------------------------------------------
    // Upload
    // -----------------------------------------------------------------

    /**
     * MediaService is not reusable here — its gates are an image extension
     * allowlist, an image MIME sniff and getimagesize(). Only the shape of its
     * UPLOAD_ERR_* mapping carries over.
     */
    private function assertUploadOk(array $file): void
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK) {
            $message = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                    'That file is too large for the server to accept.',
                UPLOAD_ERR_PARTIAL    => 'The upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE    => 'Please choose a CSV file to import.',
                UPLOAD_ERR_NO_TMP_DIR,
                UPLOAD_ERR_CANT_WRITE => 'The server could not save the upload. Contact your host.',
                UPLOAD_ERR_EXTENSION  => 'The upload was blocked by the server.',
                default               => 'The file could not be uploaded.',
            };

            throw HttpException::validation(['file' => $message], $message);
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');

        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw HttpException::badRequest('Upload could not be verified.');
        }

        $maxBytes = Env::int('SERVICE_IMPORT_MAX_BYTES', ServiceCsvSchema::MAX_BYTES);
        $size     = (int) ($file['size'] ?? 0);

        if ($size === 0) {
            throw HttpException::validation(['file' => 'That file is empty.']);
        }

        // Checked before the file is read, so an oversized upload never lands
        // in memory.
        if ($size > $maxBytes) {
            $mb      = round($maxBytes / 1048576, 1);
            $message = 'That file is ' . round($size / 1048576, 1) . ' MB. The limit is ' . $mb . ' MB.';
            throw HttpException::validation(['file' => $message], $message);
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'txt'], true)) {
            throw HttpException::validation(
                ['file' => 'Only .csv files can be imported. In Excel choose File → Save As → CSV UTF-8.']
            );
        }

        // An .xlsx is a zip archive. Renaming it to .csv is a common mistake
        // and the resulting parse errors are baffling, so name it precisely.
        $head = (string) file_get_contents($tmpPath, false, null, 0, 4);

        if (str_starts_with($head, "PK\x03\x04")) {
            throw HttpException::validation([
                'file' => 'That is an Excel workbook (.xlsx) renamed to .csv. '
                        . 'In Excel choose File → Save As → CSV UTF-8.',
            ]);
        }

        // finfo is loose on CSV — real ones report text/plain as often as
        // text/csv — so this only rules out obviously binary uploads.
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = (string) $finfo->file($tmpPath);

            $accepted = [
                'text/plain', 'text/csv', 'application/csv', 'text/x-csv',
                'application/vnd.ms-excel', 'inode/x-empty',
            ];

            if ($mime !== '' && !in_array($mime, $accepted, true)) {
                throw HttpException::validation(
                    ['file' => 'That file does not look like a CSV. It was read as "' . $mime . '".']
                );
            }
        }
    }

    // -----------------------------------------------------------------
    // Parsing
    // -----------------------------------------------------------------

    /**
     * @return array{0: string[], 1: array<int, array<string, string>>, 2: string[]}
     *         [recognised column keys, rows keyed by column, ignored headers]
     */
    private function readRows(string $raw): array
    {
        // Excel's "CSV UTF-8" writes a BOM, which would otherwise turn the
        // first header into "\xEF\xBB\xBFname" and match nothing — the classic
        // "it says name is missing but name is right there".
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        // Excel for Windows defaults to CP1252, which is what mangles the
        // en-dashes and accents in spa copy.
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $converted = @mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');

            if (!is_string($converted) || !mb_check_encoding($converted, 'UTF-8')) {
                throw HttpException::validation([
                    'file' => 'That file is not readable as text. Re-save it as CSV UTF-8.',
                ]);
            }

            $raw = $converted;
        }

        // Normalises CRLF and legacy Mac \r-only endings. Doing this to a
        // newline inside a quoted description is harmless.
        $raw = (string) preg_replace("/\r\n?/", "\n", $raw);

        $delimiter = $this->sniffDelimiter($raw);

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        // The empty $escape selects RFC-4180 behaviour, so a backslash in a
        // description does not swallow the next character.
        $headerCells = fgetcsv($handle, 0, $delimiter, '"', '');

        if (!is_array($headerCells) || $headerCells === [null]) {
            fclose($handle);
            throw HttpException::validation(['file' => 'That file has no header row.']);
        }

        if (count($headerCells) > ServiceCsvSchema::MAX_COLUMNS) {
            fclose($handle);
            throw HttpException::validation([
                'file' => 'That file has ' . count($headerCells) . ' columns. The limit is '
                        . ServiceCsvSchema::MAX_COLUMNS . '.',
            ]);
        }

        [$map, $recognised, $ignored] = $this->mapHeaders($headerCells);

        $missing = array_diff(ServiceCsvSchema::requiredColumns(), $recognised);

        if ($missing !== []) {
            fclose($handle);

            $found = array_filter(array_map('trim', array_map('strval', $headerCells)));

            // Listing what WAS found is what makes this fixable in one pass.
            throw HttpException::validation([
                'file' => 'The file is missing required columns: ' . implode(', ', $missing) . '. '
                        . 'The columns found were: ' . (implode(', ', $found) ?: 'none') . '. '
                        . 'Download the template to see the expected format.',
            ]);
        }

        $rows       = [];
        $lineNumber = 1;

        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            $lineNumber++;

            if (!is_array($cells)) {
                continue;
            }

            // fgetcsv hands back [null] for a blank line.
            $isBlank = true;
            foreach ($cells as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $isBlank = false;
                    break;
                }
            }
            if ($isBlank) {
                continue;
            }

            if (count($cells) > count($headerCells)) {
                $this->warnings[] = 'Row ' . $lineNumber . ' has ' . count($cells) . ' values but the header '
                    . 'has ' . count($headerCells) . ' columns; the extras were ignored.';
            }

            $row = ['__line' => $lineNumber];

            foreach ($map as $index => $key) {
                // Excel routinely omits trailing empty cells, so a short row is
                // padded rather than rejected.
                $row[$key] = isset($cells[$index]) ? trim((string) $cells[$index]) : '';
            }

            $rows[] = $row;

            if (count($rows) > ServiceCsvSchema::MAX_ROWS) {
                fclose($handle);
                throw HttpException::validation([
                    'file' => 'That file has more than ' . ServiceCsvSchema::MAX_ROWS
                            . ' rows. Split it and import in parts.',
                ]);
            }
        }

        fclose($handle);

        if ($ignored !== []) {
            $this->warnings[] = 'Ignored ' . count($ignored) . ' column(s) that are not part of the '
                . 'service import: ' . implode(', ', $ignored) . '.';
        }

        return [$recognised, $rows, $ignored];
    }

    /** European Excel writes semicolons; some exports use tabs. */
    private function sniffDelimiter(string $raw): string
    {
        $firstLine = strtok($raw, "\n");

        if ($firstLine === false) {
            return ',';
        }

        $best      = ',';
        $bestCount = 0;

        foreach ([',', ';', "\t"] as $candidate) {
            $cells = str_getcsv($firstLine, $candidate, '"', '');
            $count = count($cells);

            if ($count > $bestCount && $count >= 2) {
                $best      = $candidate;
                $bestCount = $count;
            }
        }

        return $best;
    }

    /**
     * @return array{0: array<int, string>, 1: string[], 2: string[]}
     *         [column index => key, recognised keys, ignored headers]
     */
    private function mapHeaders(array $headerCells): array
    {
        $aliases    = ServiceCsvSchema::headerAliases();
        $known      = ServiceCsvSchema::columnKeys();
        $map        = [];
        $recognised = [];
        $ignored    = [];

        foreach ($headerCells as $index => $cell) {
            $original = trim((string) $cell);

            if ($original === '') {
                continue;
            }

            $key = $this->normaliseHeader($original);
            $key = $aliases[$key] ?? $key;

            if (!in_array($key, $known, true)) {
                $ignored[] = $original;
                continue;
            }

            if (in_array($key, $recognised, true)) {
                throw HttpException::validation([
                    'file' => 'Two columns both map to "' . $key . '". Remove one and try again.',
                ]);
            }

            $map[$index]  = $key;
            $recognised[] = $key;
        }

        return [$map, $recognised, $ignored];
    }

    private function normaliseHeader(string $header): string
    {
        $header = ltrim($header, "\xEF\xBB\xBF");
        $header = strtolower(trim($header));
        $header = (string) preg_replace('/[\s_\-]+/', '_', $header);

        return trim($header, '_');
    }

    // -----------------------------------------------------------------
    // Planning — decides what would happen, writes nothing
    // -----------------------------------------------------------------

    private function plan(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $categoryMap = $this->categories->lookupMap();
        $categoryNames = $this->categories->optionNames();

        // Resolve every identity first, so existing services can be fetched in
        // one query rather than one per row.
        foreach ($rows as $index => $row) {
            $rows[$index]['__slug'] = $this->identitySlug($row);
        }

        $existing = $this->services->findBySlugs(
            array_values(array_filter(array_column($rows, '__slug')))
        );

        $rankHolders = $this->services->mostLovedHolders();
        $plan        = [];

        foreach ($rows as $row) {
            $plan[] = $this->planRow($row, $existing, $categoryMap, $categoryNames, $rankHolders);
        }

        return $plan;
    }

    /** Slug column when given, otherwise derived from the name. */
    private function identitySlug(array $row): string
    {
        $source = ($row['slug'] ?? '') !== '' ? $row['slug'] : ($row['name'] ?? '');

        return $source === '' ? '' : Slug::make((string) $source);
    }

    private function planRow(
        array $row,
        array $existing,
        array $categoryMap,
        array $categoryNames,
        array $rankHolders
    ): array {
        $line = (int) $row['__line'];
        $slug = (string) $row['__slug'];

        $entry = [
            'line'       => $line,
            'action'     => 'error',
            'name'       => $this->clip($row['name'] ?? ''),
            'slug'       => $slug,
            'category'   => $this->clip($row['category'] ?? ''),
            'price'      => null,
            'service_id' => null,
            'changes'    => null,
            'errors'     => [],
            'data'       => [],
        ];

        if (($row['name'] ?? '') === '') {
            $entry['errors']['name'] = 'Service name is required.';
            return $entry;
        }

        if ($slug === '') {
            $entry['errors']['name'] = 'This name produces no usable slug. Use letters or numbers.';
            return $entry;
        }

        // A duplicate inside one file is a pasted row, not a rename. Silently
        // suffixing it "-2" would create a second service instead of saying so.
        if (isset($this->claimedSlugs[$slug])) {
            $entry['errors']['slug'] = 'Row ' . $line . ' has the same slug ("' . $slug . '") as row '
                . $this->claimedSlugs[$slug] . '. Each row must be a different service.';
            return $entry;
        }
        $this->claimedSlugs[$slug] = $line;

        $current  = $existing[$slug] ?? null;
        $isUpdate = $current !== null;

        if ($isUpdate && ($current['deleted_at'] ?? null) !== null) {
            $entry['errors']['slug'] = 'A deleted service already uses the slug "' . $slug . '". '
                . 'Restore it from Services → Deleted items first, or use a different name.';
            return $entry;
        }

        $entry['service_id'] = $isUpdate ? (int) $current['id'] : null;

        // --- category -------------------------------------------------
        $categoryValue = (string) ($row['category'] ?? '');

        if ($categoryValue === '') {
            $entry['errors']['category'] = 'Category is required.';
        } else {
            $categoryId = $categoryMap[Slug::make($categoryValue)] ?? null;

            if ($categoryId === null) {
                // Naming the valid categories is what makes this fixable in
                // one pass instead of by trial and error.
                $entry['errors']['category'] = 'No category named "' . $this->clip($categoryValue) . '". '
                    . ($categoryNames === []
                        ? 'No categories exist yet — create one under Content → Categories first.'
                        : 'Available: ' . implode(', ', $categoryNames) . '.');
            }
        }

        // --- normalise every supplied cell ----------------------------
        $data = [];

        if (!isset($entry['errors']['category']) && $categoryValue !== '') {
            $data['category_id'] = $categoryMap[Slug::make($categoryValue)];
        }

        $data['name'] = (string) $row['name'];

        foreach ($this->normalisableColumns() as $key => $type) {
            if (!array_key_exists($key, $row)) {
                continue;   // column absent from the file entirely
            }

            $rawValue = (string) $row[$key];

            // Blank means "leave alone": the key is omitted, so Validator never
            // writes it and an update keeps whatever is stored.
            if ($rawValue === '') {
                continue;
            }

            // The literal NULL clears a field on purpose.
            if (strcasecmp($rawValue, 'null') === 0) {
                $data[$key] = null;
                continue;
            }

            $normalised = $this->normalise($key, $type, $rawValue);

            if ($normalised instanceof \RuntimeException) {
                $entry['errors'][$key] = $normalised->getMessage();
                continue;
            }

            $data[$key] = $normalised;
        }

        if ($entry['errors'] !== []) {
            return $entry;
        }

        // --- validate, exactly as the admin form does -----------------
        try {
            $clean = Validator::make($data)->validate(
                ServiceCsvSchema::rules($isUpdate),
                ServiceCsvSchema::labels()
            );

            if (array_key_exists('category_id', $clean)) {
                ServiceCsvSchema::assertCategoryExists((int) $clean['category_id']);
            }

            ServiceCsvSchema::assertPromoBelowPrice(
                $clean['promo_price'] ?? ($current['promo_price'] ?? null),
                $clean['price']       ?? ($current['price'] ?? null)
            );
        } catch (HttpException $e) {
            $entry['errors'] = $e->fields() ?: ['name' => $e->getMessage()];
            return $entry;
        }

        // --- Most Loved podium ----------------------------------------
        $rank = $clean['most_loved_rank'] ?? null;

        if ($rank !== null) {
            $rank = (int) $rank;

            // claimMostLovedRank() steals the rank, so three rows claiming
            // rank 1 would silently leave only the last one ranked.
            if (isset($this->claimedRanks[$rank])) {
                $entry['errors']['most_loved_rank'] = 'Row ' . $line . ' and row '
                    . $this->claimedRanks[$rank] . ' both claim Most Loved rank ' . $rank
                    . '. Only one service can hold each rank.';
                return $entry;
            }

            $this->claimedRanks[$rank] = $line;

            // Stealing from a service outside this file is legitimate, but the
            // operator should know it is about to happen.
            $holder = $rankHolders[$rank] ?? null;
            if ($holder !== null && (int) $holder['id'] !== ($entry['service_id'] ?? 0)) {
                $this->warnings[] = 'Most Loved rank ' . $rank . ' will be taken from "'
                    . $holder['name'] . '".';
            }
        }

        $entry['price'] = $clean['price'] ?? ($current['price'] ?? null);
        $entry['data']  = $clean;

        if (!$isUpdate) {
            $entry['action'] = 'create';
            return $entry;
        }

        $changes = $this->diff($current, $clean);

        // BaseRepository::update() stamps updated_by BEFORE its empty-data
        // guard, so an unchanged row would still fire an UPDATE and bump
        // updated_at on every row in the file, scrambling the dashboard's
        // "recently updated" panel.
        $entry['action']  = $changes === [] ? 'unchanged' : 'update';
        $entry['changes'] = $changes === [] ? null : $changes;

        return $entry;
    }

    /** Column => how its raw CSV text becomes a real PHP value. */
    private function normalisableColumns(): array
    {
        return [
            'slug'              => 'text',
            'short_description' => 'text',
            'description'       => 'text',
            'price'             => 'money',
            'price_display'     => 'text',
            'promo_price'       => 'money',
            'duration_minutes'  => 'int',
            'duration_display'  => 'text',
            'icon_key'          => 'icon',
            'booking_url'       => 'url',
            'status'            => 'status',
            'featured'          => 'bool',
            'most_loved_rank'   => 'int',
            'display_order'     => 'int',
        ];
    }

    /**
     * Casting is mandatory, not cosmetic: Validator's min/max rules branch on
     * `is_numeric($value) && !is_string($value)`, so the string "150" would be
     * length-checked rather than magnitude-checked, and "999999999" would sail
     * past max:100000 and overflow DECIMAL(10,2).
     *
     * @return mixed the value, or a RuntimeException carrying the row message
     */
    private function normalise(string $key, string $type, string $value): mixed
    {
        switch ($type) {
            case 'money':
                $cleaned = str_replace([',', ' ', "\xC2\xA0", '$'], '', $value);

                if (!is_numeric($cleaned)) {
                    return new \RuntimeException(
                        '"' . $this->clip($value) . '" is not a number. Use digits, e.g. 150 or 150.00.'
                    );
                }

                return (float) $cleaned;

            case 'int':
                $cleaned = str_replace([',', ' ', "\xC2\xA0"], '', $value);

                if (!preg_match('/^-?\d+$/', $cleaned)) {
                    return new \RuntimeException(
                        '"' . $this->clip($value) . '" is not a whole number.'
                    );
                }

                return (int) $cleaned;

            case 'bool':
                $lower = strtolower($value);

                if (in_array($lower, ['1', 'true', 'yes', 'y', 'on', 'x'], true)) {
                    return 1;
                }
                if (in_array($lower, ['0', 'false', 'no', 'n', 'off'], true)) {
                    return 0;
                }

                return new \RuntimeException(
                    '"' . $this->clip($value) . '" is not yes or no. Use yes, no, true, false, 1 or 0.'
                );

            case 'status':
                $lower = strtolower($value);

                if (in_array($lower, ['active', 'live', 'on', 'published', 'enabled'], true)) {
                    return 'active';
                }
                if (in_array($lower, ['inactive', 'hidden', 'off', 'draft', 'disabled'], true)) {
                    return 'inactive';
                }

                return new \RuntimeException(
                    '"' . $this->clip($value) . '" is not a status. Use active or inactive.'
                );

            case 'icon':
                if (!in_array($value, ServiceCsvSchema::iconKeys(), true)) {
                    // A wrong icon degrades to no icon rather than breaking the
                    // page, so this is a warning and the value is dropped.
                    $this->warnings[] = 'Unknown icon "' . $this->clip($value)
                        . '" was ignored. Valid icons: ' . implode(', ', ServiceCsvSchema::iconKeys()) . '.';
                    return null;
                }

                return $value;

            case 'url':
                // A bare "www.booker.com/..." would fail Validator's url rule,
                // which requires a scheme.
                if (!preg_match('#^https?://#i', $value)) {
                    return 'https://' . ltrim($value, '/');
                }

                return $value;

            default:
                return $value;
        }
    }

    /**
     * Changed columns only. Compared numerically where the column is numeric,
     * because MySQL returns price as "150.00" while the parsed value is 150.0.
     */
    private function diff(array $current, array $clean): array
    {
        $numeric = ['price', 'promo_price', 'duration_minutes', 'most_loved_rank',
                    'display_order', 'featured', 'category_id'];
        $changes = [];

        foreach ($clean as $key => $new) {
            if ($key === 'slug') {
                continue;   // an update never re-slugs
            }
            if (!array_key_exists($key, $current)) {
                continue;
            }

            $old = $current[$key];

            $same = in_array($key, $numeric, true)
                ? ($old === null && $new === null) || ((float) $old === (float) $new)
                : (string) $old === (string) $new;

            if (!$same) {
                $changes[$key] = ['from' => $old, 'to' => $new];
            }
        }

        return $changes;
    }

    // -----------------------------------------------------------------
    // Writing — one transaction, all or nothing
    // -----------------------------------------------------------------

    private function writeAll(array $plan, array $result): array
    {
        $order   = $this->services->nextDisplayOrder();
        $created = [];
        $updated = [];
        $failed  = null;

        try {
            Database::transaction(function () use ($plan, &$order, &$created, &$updated, &$failed): void {
                foreach ($plan as $row) {
                    if ($row['action'] === 'unchanged') {
                        continue;
                    }

                    // Held so a failure can name the row it died on; the write
                    // count cannot be used as an index because unchanged rows
                    // are skipped.
                    $failed = $row;

                    $data = $row['data'];

                    if ($row['action'] === 'create') {
                        $data['slug'] = $row['slug'];

                        // File order becomes display order when the column is
                        // absent. Seeded once, not per row — 500 MAX() queries
                        // would time out on shared hosting.
                        if (!array_key_exists('display_order', $data) || $data['display_order'] === null) {
                            $data['display_order'] = $order++;
                        }

                        $id        = $this->services->create($data);
                        $created[] = $id;
                    } else {
                        $id = (int) $row['service_id'];
                        unset($data['slug']);
                        $this->services->update($id, $data);
                        $updated[] = $id;
                    }

                    if (array_key_exists('most_loved_rank', $data)) {
                        $rank = $data['most_loved_rank'] === null ? null : (int) $data['most_loved_rank'];
                        $this->services->claimMostLovedRank($rank, $id);
                    }
                }
            });
        } catch (\PDOException $e) {
            // The front controller's global 1062 handler emits a 409 with no
            // row number, which is useless for a 500-row file.
            $isDuplicate = ($e->errorInfo[1] ?? null) === 1062;

            if (!$isDuplicate) {
                throw $e;
            }

            return [
                'committed' => false,
                'abort'     => [
                    'line'    => $failed['line'] ?? null,
                    'name'    => $failed['name'] ?? null,
                    'message' => 'Another service already uses the slug "'
                        . ($failed['slug'] ?? '') . '". This can happen if someone saved a service '
                        . 'while you were reviewing. Nothing was imported — preview the file again.',
                ],
            ];
        }

        $summary              = $result['summary'];
        $summary['created']   = count($created);
        $summary['updated']   = count($updated);

        $fileName = $result['file']['name'];

        // One summary line. 500 individual rows would bury the day's real
        // activity in the audit log.
        AuditLogger::record(
            'imported',
            'service',
            null,
            'Imported ' . ($summary['created'] + $summary['updated']) . ' services from "'
                . $fileName . '" (' . $summary['created'] . ' created, ' . $summary['updated'] . ' updated)',
            [
                'file'      => $fileName,
                'rows'      => $summary['rows'],
                'created'   => $created,
                'updated'   => $updated,
                'unchanged' => $summary['unchanged'],
            ]
        );

        // Fill the new ids in so the results screen can link to each service.
        $rows      = $result['rows'];
        $createdIx = 0;

        foreach ($rows as $index => $row) {
            if ($row['action'] === 'create' && isset($created[$createdIx])) {
                $rows[$index]['service_id'] = $created[$createdIx];
                $createdIx++;
            }
        }

        return ['committed' => true, 'summary' => $summary, 'rows' => $rows];
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /** The row as the client sees it — no internal payload, nothing oversized. */
    private function publicRow(array $row): array
    {
        unset($row['data']);

        if (is_array($row['changes'])) {
            foreach ($row['changes'] as $key => $change) {
                $row['changes'][$key] = [
                    'from' => is_scalar($change['from']) ? $this->clip((string) $change['from']) : null,
                    'to'   => is_scalar($change['to'])   ? $this->clip((string) $change['to'])   : null,
                ];
            }
        }

        return $row;
    }

    /** Keeps a 20,000-character description from being echoed back 500 times. */
    private function clip(mixed $value, int $limit = 120): string
    {
        $text = trim((string) $value);

        return mb_strlen($text) <= $limit ? $text : mb_substr($text, 0, $limit) . '…';
    }

    private function safeName(string $name): string
    {
        return $this->clip(basename($name), 100);
    }
}
