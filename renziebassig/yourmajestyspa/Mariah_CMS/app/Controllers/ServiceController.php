<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\CategoryRepository;
use Mariah\Repositories\ServiceRepository;
use Mariah\Repositories\SettingsRepository;
use Mariah\Services\ServiceCsvSchema;
use Mariah\Services\ServiceImporter;

final class ServiceController extends ResourceController
{
    private ServiceRepository $services;

    public function __construct()
    {
        $this->services = new ServiceRepository();
    }

    protected function repository(): BaseRepository { return $this->services; }
    protected function label(): string              { return 'Service'; }
    protected function entityType(): string         { return 'service'; }

    // Rules, labels and the business checks live in ServiceCsvSchema so the
    // admin form and the CSV importer cannot drift apart.

    protected function rules(bool $isUpdate): array
    {
        return ServiceCsvSchema::rules($isUpdate);
    }

    /** All TEXT columns. short_description stays plain: it is a 500-character card blurb. */
    protected function richTextFields(): array
    {
        return ['description', 'benefits', 'inclusions', 'contraindications'];
    }

    protected function fieldLabels(): array
    {
        return ServiceCsvSchema::labels();
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        if (array_key_exists('category_id', $data)) {
            ServiceCsvSchema::assertCategoryExists((int) $data['category_id']);
        }

        ServiceCsvSchema::assertPromoBelowPrice(
            $data['promo_price'] ?? ($existing['promo_price'] ?? null),
            $data['price']       ?? ($existing['price'] ?? null)
        );

        return $data;
    }

    protected function afterSave(int $id, array $data, Request $request, bool $isUpdate): void
    {
        if (array_key_exists('most_loved_rank', $data)) {
            $rank = $data['most_loved_rank'] === null ? null : (int) $data['most_loved_rank'];
            $this->services->claimMostLovedRank($rank, $id);
        }

        // Optional gallery: image_ids replaces the whole set.
        $imageIds = $request->input('image_ids');
        if (is_array($imageIds)) {
            $this->services->syncImages($id, $imageIds, (string) $request->input('image_alt', '') ?: null);
        }

        // Same contract: absent means leave the tiers alone, present means
        // replace them wholesale.
        $variants = $request->input('variants');
        if (is_array($variants)) {
            $this->services->syncVariants($id, $this->validVariants($variants));
        }
    }

    /**
     * showExtras() is merged over the row, so recomputing the two labels here
     * is what makes a tiered service read "from $150" rather than "$150".
     *
     * decorate() cannot do it: it runs per row inside paginate()'s array_map,
     * where a child query would be an N+1 across the whole page. index() below
     * solves the same problem for lists, in one query.
     */
    protected function showExtras(array $row): array
    {
        $variants = $this->services->variants((int) $row['id']);
        $labelled = ServiceRepository::applyLabels($row, $variants);

        return [
            'images'         => $this->services->images((int) $row['id']),
            'variants'       => $variants,
            'price_label'    => $labelled['price_label'],
            'duration_label' => $labelled['duration_label'],
        ];
    }

    /**
     * The list, with tier-aware labels. One extra query for the whole page
     * rather than one per row.
     */
    public function index(Request $request): never
    {
        $result   = $this->services->paginate($request);
        $variants = $this->services->variantsFor(array_column($result['rows'], 'id'));

        $rows = array_map(
            static fn (array $row): array => ServiceRepository::applyLabels(
                $row,
                $variants[(int) $row['id']] ?? []
            ),
            $result['rows']
        );

        Response::json($rows, 200, $result['meta']);
    }

    /**
     * Validates the submitted price tiers.
     *
     * Hand-rolled because Validator takes a flat `field => rules` map and has
     * no vocabulary for arrays or nesting. Errors are keyed "variants.0.price"
     * so the form paints them onto the right repeater cell.
     *
     * @param  array<int, mixed> $rows
     * @return array<int, array<string, mixed>>
     */
    private function validVariants(array $rows): array
    {
        $clean  = [];
        $errors = [];
        $index  = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $price = $row['price'] ?? null;

            // A wholly blank row is the empty repeater line nobody filled in.
            if ($label === '' && ($price === null || $price === '')) {
                continue;
            }

            if ($label === '') {
                $errors["variants.{$index}.label"] = 'Give this tier a name, such as "50 min".';
            }

            if (!is_numeric($price) || (float) $price < 0) {
                $errors["variants.{$index}.price"] = 'Enter a price for this tier.';
            }

            $minutes = $row['duration_minutes'] ?? null;
            if ($minutes !== null && $minutes !== '' && (!is_numeric($minutes) || (int) $minutes < 0)) {
                $errors["variants.{$index}.duration_minutes"] = 'Duration must be a whole number of minutes.';
            }

            $url = trim((string) ($row['booking_url'] ?? ''));
            if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
                $errors["variants.{$index}.booking_url"] = 'That is not a valid link.';
            }

            $clean[] = [
                'label'            => $label,
                'duration_minutes' => $minutes,
                'price'            => $price,
                'booking_url'      => $url,
            ];
            $index++;
        }

        if ($errors !== []) {
            throw HttpException::validation($errors);
        }

        return $clean;
    }

    /**
     * duplicate() copies fillable columns only, so without this a copied
     * service would lose every price tier. The gallery is deliberately not
     * copied: two services sharing image rows would fight over which one owns
     * the primary, and picking new images is part of adapting a copy anyway.
     */
    protected function afterDuplicate(int $newId, int $sourceId): void
    {
        $tiers = $this->services->variants($sourceId);

        if ($tiers !== []) {
            $this->services->syncVariants($newId, $tiers);
        }
    }

    /**
     * Vocabularies the add/edit form and the import screen both need.
     *
     * `columns` makes ServiceCsvSchema the single source of truth for the
     * import contract: the admin renders the column reference and builds
     * templates from this, rather than keeping its own copy of the list.
     */
    public function formOptions(Request $request): never
    {
        Response::json([
            'categories' => (new CategoryRepository())->options(),
            'icons'      => ServiceCsvSchema::iconChoices(),
            'columns'    => ServiceCsvSchema::columns(),
        ]);
    }

    /**
     * Bulk import, from either an uploaded CSV (multipart, field "file") or a
     * Google Sheets link (field "source_url"). Both are the same import with a
     * different way of getting the bytes, so they share one endpoint, one
     * permission and one preview/commit protocol.
     *
     * `dry_run` defaults to 1, so a request that omits it previews rather than
     * writes. Only an explicit dry_run=0 commits.
     */
    public function import(Request $request): never
    {
        $dryRun    = (string) $request->input('dry_run', '1') !== '0';
        $sourceUrl = trim((string) $request->input('source_url', ''));

        // Handled before the upload diagnostics below, which are meaningless
        // for a JSON body.
        if ($sourceUrl !== '') {
            if (!SettingsRepository::bool('services_import_url_enabled')) {
                throw HttpException::validation(['file' =>
                    'Importing directly from a link is turned off. Turn it on under '
                    . 'Settings → Site settings, or download the sheet as a CSV and upload it.']);
            }

            Response::json(ServiceImporter::runFromUrl(
                $sourceUrl,
                $dryRun,
                $request->input('confirm_digest')
            ));
        }

        // When PHP's post_max_size is exceeded it silently empties BOTH $_POST
        // and $_FILES. The CSRF header survives, so the guard passes and the
        // request arrives looking like "no file was sent" — the most confusing
        // upload failure mode there is, and worth naming precisely.
        if (!isset($_FILES['file'])) {
            $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
            $postMax       = self::bytesFromIni((string) ini_get('post_max_size'));

            if ($postMax > 0 && $contentLength > $postMax) {
                $mb = round($postMax / 1048576, 1);
                throw HttpException::validation([
                    'file' => "That file was too large for the server to accept (limit {$mb} MB).",
                ]);
            }

            throw HttpException::validation(['file' => 'Please choose a CSV file to import.']);
        }

        Response::json(ServiceImporter::runFromUpload(
            $_FILES['file'],
            $dryRun,
            $request->input('confirm_digest')
        ));
    }

    /** "8M" / "512K" from php.ini as a byte count. */
    private static function bytesFromIni(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $unit   = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g'     => $number * 1073741824,
            'm'     => $number * 1048576,
            'k'     => $number * 1024,
            default => $number,
        };
    }
}
