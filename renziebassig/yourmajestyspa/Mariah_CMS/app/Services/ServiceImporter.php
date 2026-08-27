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
 * Bulk service import from a CSV file or a Google Sheets link.
 *
 * Preview and commit are the SAME pipeline, differing only in whether the
 * final write stage runs. On confirm the client re-sends the source — the file
 * the browser still holds, or the sheet link — so the server keeps no state
 * between the two calls and the preview cannot disagree with what is written.
 *
 * The two sources differ only in how the bytes arrive: runFromUpload() and
 * runFromUrl() both hand a string to runBytes(), which is everything else.
 * is_uploaded_file() stays on the upload path alone, because it is false for
 * any file the server fetched itself.
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

    /**
     * The operator's per-column rules, resolved once per import.
     *
     * @var array<string, array{required:bool, default:?string}>
     */
    private array $rules = [];

    /** @var array<string, int> column => how many blank cells a default filled */
    private array $defaulted = [];

    private ServiceRepository $services;
    private CategoryRepository $categories;

    private function __construct()
    {
        $this->services   = new ServiceRepository();
        $this->categories = new CategoryRepository();

        foreach (ServiceCsvSchema::configuredColumns() as $column) {
            $this->rules[$column['key']] = [
                'required' => (bool) $column['required'],
                'default'  => $column['default'],
            ];
        }
    }

    /**
     * @param array $file       one entry from $_FILES
     * @param bool  $dryRun     true previews, false writes
     * @param mixed $confirmDigest the digest the preview returned, on commit
     */
    public static function runFromUpload(array $file, bool $dryRun, mixed $confirmDigest = null): array
    {
        $importer = new self();
        $importer->assertUploadOk($file);

        $raw = (string) file_get_contents($file['tmp_name']);

        return $importer->runBytes(
            $raw,
            $importer->safeName((string) ($file['name'] ?? 'upload.csv')),
            $dryRun,
            $confirmDigest,
            'upload'
        );
    }

    /** Fetches a published Google Sheet as CSV and imports it. */
    public static function runFromUrl(string $sourceUrl, bool $dryRun, mixed $confirmDigest = null): array
    {
        $fetched  = GoogleSheetFetcher::fetchCsv($sourceUrl);
        $importer = new self();

        return $importer->runBytes(
            $fetched['bytes'],
            $importer->safeName($fetched['name']),
            $dryRun,
            $confirmDigest,
            'google_sheet',
            $fetched['url']
        );
    }

    /** Everything both sources share, from the raw bytes onward. */
    private function runBytes(
        string $raw,
        string $name,
        bool $dryRun,
        mixed $confirmDigest,
        string $source,
        ?string $sourceUrl = null
    ): array {
        $this->assertContentOk($raw);

        $digest = hash('sha256', $raw);

        // The client re-sends the same source on confirm, so this matches in
        // the happy path. It matters more for a sheet than for a file: a file
        // in the browser cannot change under you, a shared sheet can.
        if (!$dryRun && is_string($confirmDigest) && $confirmDigest !== '' && $confirmDigest !== $digest) {
            throw HttpException::conflict($source === 'google_sheet'
                ? 'The sheet changed since it was previewed — someone edited it in the last few '
                  . 'minutes. Nothing was imported. Preview it again to see what it now contains.'
                : 'The file changed since it was previewed. Please preview it again.');
        }

        [$headers, $rows, $ignored] = $this->readRows($raw);

        $plan = $this->plan($rows);

        // Say so plainly. A default that silently altered rows would be the
        // kind of thing someone discovers weeks later on the live site.
        foreach ($this->defaulted as $column => $count) {
            $this->warnings[] = $count . ' new ' . ($count === 1 ? 'service' : 'services')
                . ' had no ' . $column . ', so the default "'
                . ServiceCsvSchema::clip($this->rules[$column]['default']) . '" was used.';
        }

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
                'name'   => $name,
                'size'   => strlen($raw),
                'digest' => $digest,
                // Additive; the existing client ignores what it does not read.
                // Both also reach the audit log, so the trail records where a
                // menu rewrite came from.
                'source'     => $source,
                'source_url' => $sourceUrl,
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
    // Source checks
    //
    // Split deliberately: assertUploadOk() is transport — things only an
    // HTTP upload can be wrong about — while assertContentOk() judges bytes
    // and so serves the sheet path too.
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

        // Only an upload carries an operator-supplied filename.
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'txt'], true)) {
            throw HttpException::validation([
                'file' => 'Only .csv files can be imported. In Excel choose File → Save As → CSV UTF-8; '
                        . 'in Google Sheets choose File → Download → Comma-separated values.',
            ]);
        }
    }

    /**
     * Content checks, on bytes rather than a path, so an uploaded file and a
     * fetched sheet are judged identically.
     */
    private function assertContentOk(string $raw): void
    {
        if ($raw === '') {
            throw HttpException::validation(['file' => 'That file is empty.']);
        }

        // Backstop. The upload path already checked $_FILES['size'] before
        // reading, and the fetcher aborts mid-transfer, so reaching this means
        // one of those was bypassed.
        $maxBytes = Env::int('SERVICE_IMPORT_MAX_BYTES', ServiceCsvSchema::MAX_BYTES);

        if (strlen($raw) > $maxBytes) {
            $mb      = round($maxBytes / 1048576, 1);
            $message = 'That file is ' . round(strlen($raw) / 1048576, 1)
                     . ' MB. The limit is ' . $mb . ' MB.';
            throw HttpException::validation(['file' => $message], $message);
        }

        // An .xlsx is a zip archive. Renaming it to .csv is a common mistake
        // and the resulting parse errors are baffling, so name it precisely.
        if (str_starts_with($raw, "PK\x03\x04")) {
            throw HttpException::validation([
                'file' => 'That is an Excel workbook (.xlsx) renamed to .csv. '
                        . 'In Excel choose File → Save As → CSV UTF-8, or in Google Sheets '
                        . 'File → Download → Comma-separated values (.csv).',
            ]);
        }

        // A saved web page renamed .csv on the upload path; a backstop behind
        // GoogleSheetFetcher's richer sign-in-page detection on the sheet path.
        $head = strtolower(ltrim(substr($raw, 0, 512)));

        if (str_starts_with($head, '<!doctype html') || str_starts_with($head, '<html')) {
            throw HttpException::validation([
                'file' => 'That is a web page, not a CSV. If it came from Google Sheets, the sheet '
                        . 'is probably not shared — or use File → Download → Comma-separated values.',
            ]);
        }

        // finfo is loose on CSV — real ones report text/plain as often as
        // text/csv — so this only rules out obviously binary content. Reading
        // the buffer rather than a path is what lets one method serve both
        // sources.
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = (string) $finfo->buffer($raw);

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
        // Excel's "CSV UTF-8" and Google Sheets' CSV export both write a BOM,
        // which would otherwise turn the first header into "\xEF\xBB\xBFname"
        // and match nothing — the classic "it says name is missing but name is
        // right there".
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        // Excel for Windows defaults to CP1252, which is what mangles the
        // en-dashes and accents in spa copy.
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $converted = @mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');

            if (!is_string($converted) || !mb_check_encoding($converted, 'UTF-8')) {
                throw HttpException::validation([
                    'file' => 'That file is not readable as text. Re-save it as CSV UTF-8 — '
                            . 'a file downloaded from Google Sheets always is.',
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
                        . 'The Import screen lists the expected columns, and has a template '
                        . 'you can compare against.',
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
                // Excel and Google Sheets both omit trailing empty cells, so a
                // short row is padded rather than rejected.
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

    /**
     * European Excel writes semicolons; some exports use tabs. Google Sheets
     * always exports commas, so this only matters on the upload path.
     */
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

        // The name identifies the row, so it is required whatever the rules
        // say — without it there is nothing to create and nothing to match.
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
        // Not in normalisableColumns() — it resolves against the live category
        // list rather than coercing — so it applies its own default here.
        $categoryValue = (string) ($row['category'] ?? '');
        $categoryRule  = $this->rules['category'] ?? ['required' => true, 'default' => null];

        if ($categoryValue === '' && !$isUpdate && $categoryRule['default'] !== null) {
            $categoryValue = (string) $categoryRule['default'];
            $this->defaulted['category'] = ($this->defaulted['category'] ?? 0) + 1;
        }

        if ($categoryValue === '') {
            // An update keeps whatever category is stored; only a new service
            // has to be told where it belongs.
            if ($categoryRule['required'] && !$isUpdate) {
                $entry['errors']['category'] = 'Category is required.';
            }
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
            $rule = $this->rules[$key] ?? ['required' => false, 'default' => null];

            // A column absent from the file reads as blank rather than being
            // skipped, so a configured default can still supply it — that is
            // what lets a required column with a default need no header.
            $rawValue = array_key_exists($key, $row) ? (string) $row[$key] : '';

            if ($rawValue === '') {
                // Defaults fill gaps on NEW services only. On an update a blank
                // cell still means "leave the stored value alone" — the promise
                // that lets someone import a file carrying only the columns
                // they actually changed.
                if (!$isUpdate && $rule['default'] !== null) {
                    $filled = $this->normalise($key, $type, (string) $rule['default']);

                    // Defaults are validated when they are saved, so this is
                    // belt and braces: an unusable one is skipped rather than
                    // failing every row in the file.
                    if (!($filled instanceof \RuntimeException)) {
                        $data[$key] = $filled;
                        $this->defaulted[$key] = ($this->defaulted[$key] ?? 0) + 1;
                    }
                }

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
                // importRules, not rules: the operator's required-column
                // toggles apply here and nowhere else.
                ServiceCsvSchema::importRules($isUpdate),
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
        return ServiceCsvSchema::normalisableColumns();
    }

    /**
     * Raw CSV text into a real value, or a RuntimeException carrying the
     * row message. Lives on the schema so a configured default is coerced
     * by exactly the same code that will coerce the real cells.
     */
    private function normalise(string $key, string $type, string $value): mixed
    {
        return ServiceCsvSchema::normalise($key, $type, $value, $this->warnings);
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
