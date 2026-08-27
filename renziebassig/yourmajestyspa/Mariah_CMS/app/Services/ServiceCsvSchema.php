<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Repositories\SettingsRepository;

/**
 * The one definition of what a service is, shared by the admin form and the
 * CSV importer.
 *
 * Validation rules, field labels, the icon allowlist and the two cross-field
 * business rules all live here so the two write paths cannot drift apart. An
 * importer that accepted data the form rejects would be worse than no importer
 * at all.
 *
 * Which columns are required, and what a blank cell falls back to, are
 * configurable per site — so `configuredColumns()`, `requiredColumns()` and
 * `rules()` read the `services_import_rules` setting. `columns()` itself stays
 * pure: it is the base contract those overrides are applied to, and it is read
 * in hot paths. SettingsRepository caches per request, so the reads are free
 * after the first.
 */
final class ServiceCsvSchema
{
    /** A CSV larger than this is refused before it is read into memory. */
    public const MAX_BYTES = 2097152;      // 2 MB

    /** Row and column ceilings, so one file cannot exhaust the request. */
    public const MAX_ROWS    = 500;
    public const MAX_COLUMNS = 60;

    /**
     * The importable columns, in template order.
     *
     * Images are deliberately absent: a media id means nothing in a
     * spreadsheet, so pictures stay a job for the admin form.
     *
     * @return array<int, array{key:string, required:bool, label:string, help:string}>
     */
    public static function columns(): array
    {
        return [
            ['key' => 'name', 'required' => true, 'label' => 'name',
             'help' => 'The service name, 2 to 190 characters.'],

            ['key' => 'category', 'required' => true, 'label' => 'category',
             'help' => 'An existing category name or slug. The import never creates categories.'],

            ['key' => 'price', 'required' => true, 'label' => 'price',
             'help' => 'A number. "$1,250.00" and "1250" are both accepted.'],

            ['key' => 'slug', 'required' => false, 'label' => 'slug',
             'help' => 'Matches the row to an existing service. Blank means "match on the name".'],

            ['key' => 'short_description', 'required' => false, 'label' => 'short_description',
             'help' => 'One line under the name on the website. Max 500 characters.'],

            ['key' => 'description', 'required' => false, 'label' => 'description',
             'help' => 'The full description shown when the card is opened.'],

            ['key' => 'price_display', 'required' => false, 'label' => 'price_display',
             'help' => 'Overrides the price on the website, e.g. "from $150".'],

            ['key' => 'promo_price', 'required' => false, 'label' => 'promo_price',
             'help' => 'Must be lower than the price.'],

            ['key' => 'duration_minutes', 'required' => false, 'label' => 'duration_minutes',
             'help' => 'Whole minutes, up to 1440.'],

            ['key' => 'duration_display', 'required' => false, 'label' => 'duration_display',
             'help' => 'Overrides the duration, e.g. "1 hr & 40 mins".'],

            ['key' => 'icon_key', 'required' => false, 'label' => 'icon_key',
             'help' => 'One of: ' . implode(', ', self::iconKeys()) . '.'],

            ['key' => 'booking_url', 'required' => false, 'label' => 'booking_url',
             'help' => 'The Booker.com link for this treatment.'],

            ['key' => 'status', 'required' => false, 'label' => 'status',
             'help' => 'active or inactive. Defaults to active on new services.'],

            ['key' => 'featured', 'required' => false, 'label' => 'featured',
             'help' => 'yes or no.'],

            ['key' => 'most_loved_rank', 'required' => false, 'label' => 'most_loved_rank',
             'help' => '1, 2 or 3. Only one service can hold each rank.'],

            ['key' => 'display_order', 'required' => false, 'label' => 'display_order',
             'help' => 'Lower numbers appear first. Blank follows the file order.'],
        ];
    }

    /**
     * The columns a file must carry a header for.
     *
     * A column that is required but has a configured default does NOT need a
     * header: the default supplies the value, so demanding an empty column
     * would be busywork.
     *
     * @return string[]
     */
    public static function requiredColumns(): array
    {
        $required = [];

        foreach (self::configuredColumns() as $column) {
            if ($column['required'] && $column['default'] === null) {
                $required[] = $column['key'];
            }
        }

        return $required;
    }

    /** Every column an operator must put a value in, header or default. */
    public static function requiredKeys(): array
    {
        $required = [];

        foreach (self::configuredColumns() as $column) {
            if ($column['required']) {
                $required[] = $column['key'];
            }
        }

        return $required;
    }

    /** @return string[] every importable column key */
    public static function columnKeys(): array
    {
        return array_column(self::columns(), 'key');
    }

    /** The setting holding the operator's per-column overrides. */
    public const RULES_SETTING = 'services_import_rules';

    /**
     * Identity columns. These may never carry a default.
     *
     * ServiceImporter::identitySlug() reads them raw, before normalisation,
     * and its answer decides whether a row creates a service or updates one.
     * A default here would change which record a row matches — silently, and
     * differently on every run.
     *
     * @var string[]
     */
    public const NO_DEFAULT = ['name', 'slug'];

    /**
     * columns() merged with the admin's configuration.
     *
     * Every entry gains an effective `required` and a `default` (raw string, or
     * null for none). The stored config is sparse, so a column nobody has
     * touched keeps exactly what columns() says.
     *
     * columns() itself stays pure and untouched — it is read in hot paths and
     * its contract is "no I/O". Only this method reads settings.
     */
    public static function configuredColumns(): array
    {
        $config = self::rulesConfig();

        return array_map(static function (array $column) use ($config): array {
            $override = $config[$column['key']] ?? [];

            $column['required'] = array_key_exists('required', $override)
                ? (bool) $override['required']
                : $column['required'];

            $default = $override['default'] ?? null;
            $default = is_string($default) && trim($default) !== '' ? trim($default) : null;

            $column['default'] = in_array($column['key'], self::NO_DEFAULT, true) ? null : $default;

            return $column;
        }, self::columns());
    }

    /** key => ['required' => bool, 'default' => string], as stored. */
    public static function rulesConfig(): array
    {
        $decoded = json_decode(SettingsRepository::string(self::RULES_SETTING), true);

        return is_array($decoded) ? $decoded : [];
    }

    /** Column => how its raw CSV text becomes a real PHP value. */
    public static function normalisableColumns(): array
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
     * Raw CSV text into the real PHP value for that column.
     *
     * Casting is mandatory, not cosmetic: Validator's min/max rules branch on
     * `is_numeric($value) && !is_string($value)`, so the string "150" would be
     * length-checked rather than magnitude-checked, and "999999999" would sail
     * past max:100000 and overflow DECIMAL(10,2).
     *
     * Lives here rather than in the importer so a configured default can be
     * validated through the exact same coercion the import will apply to it —
     * a bad default is then refused when it is saved, not on the next import.
     *
     * @param  string[] $warnings collected by reference; an unknown icon is a
     *                            warning rather than an error
     * @return mixed the value, or a RuntimeException carrying the row message
     */
    public static function normalise(string $key, string $type, string $value, array &$warnings = []): mixed
    {
        switch ($type) {
            case 'money':
                $cleaned = str_replace([',', ' ', "\xC2\xA0", '$'], '', $value);

                if (!is_numeric($cleaned)) {
                    return new \RuntimeException(
                        '"' . self::clip($value) . '" is not a number. Use digits, e.g. 150 or 150.00.'
                    );
                }

                return (float) $cleaned;

            case 'int':
                $cleaned = str_replace([',', ' ', "\xC2\xA0"], '', $value);

                if (!preg_match('/^-?\d+$/', $cleaned)) {
                    return new \RuntimeException(
                        '"' . self::clip($value) . '" is not a whole number.'
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
                    '"' . self::clip($value) . '" is not yes or no. Use yes, no, true, false, 1 or 0.'
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
                    '"' . self::clip($value) . '" is not a status. Use active or inactive.'
                );

            case 'icon':
                $icon = self::resolveIcon($value);

                if (!$icon['known']) {
                    // A wrong icon degrades to no icon rather than breaking the
                    // page, so this is a warning and the value is dropped.
                    $warnings[] = 'Unknown icon "' . self::clip($value)
                        . '" was ignored. Valid icons: ' . implode(', ', self::iconKeys()) . '.';
                    return null;
                }

                return $icon['key'];

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

    /** Keeps a 20,000-character description from being echoed back 500 times. */
    public static function clip(mixed $value, int $limit = 120): string
    {
        $text = trim((string) $value);

        return mb_strlen($text) <= $limit ? $text : mb_substr($text, 0, $limit) . '…';
    }

    /**
     * Spellings a real spreadsheet uses, mapped onto our column keys.
     * Applied after the header has been normalised (lowercase, underscores).
     *
     * @return array<string, string>
     */
    public static function headerAliases(): array
    {
        return [
            'service'             => 'name',
            'service_name'        => 'name',
            'treatment'           => 'name',
            'title'               => 'name',
            'category_name'       => 'category',
            'dept'                => 'category',
            'department'          => 'category',
            'cost'                => 'price',
            'rate'                => 'price',
            'sale_price'          => 'promo_price',
            'promo'               => 'promo_price',
            'promotional_price'   => 'promo_price',
            'duration'            => 'duration_minutes',
            'duration_mins'       => 'duration_minutes',
            'minutes'             => 'duration_minutes',
            'mins'                => 'duration_minutes',
            'sort_order'          => 'display_order',
            'order'               => 'display_order',
            'position'            => 'display_order',
            'most_loved'          => 'most_loved_rank',
            'rank'                => 'most_loved_rank',
            'blurb'               => 'short_description',
            'tagline'             => 'short_description',
            'summary'             => 'short_description',
            'book_url'            => 'booking_url',
            'booking_link'        => 'booking_url',
            'icon'                => 'icon_key',
            'active'              => 'status',
        ];
    }

    /**
     * Validation rules for a service. $isUpdate relaxes `required` into
     * optional, exactly as ResourceController expects.
     *
     * Deliberately NOT configurable: this is what the admin form enforces, and
     * a setting named "import rules" must not quietly change what someone
     * typing a service by hand is allowed to leave blank. The importer calls
     * importRules() instead.
     */
    public static function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? [] : ['name', 'category', 'price'];

        return self::rulesFor($required);
    }

    /**
     * The same rules with the operator's configured required columns applied.
     * Used only by ServiceImporter.
     */
    public static function importRules(bool $isUpdate): array
    {
        return self::rulesFor($isUpdate ? [] : self::requiredKeys());
    }

    /** @param string[] $required column names that must carry a value */
    private static function rulesFor(array $required): array
    {
        // `category` is the column name an operator sees; `category_id` is what
        // it resolves to once the category has been looked up.
        $need = static function (string $column) use ($required): string {
            return in_array($column, $required, true) ? 'required|' : '';
        };

        return [
            'category_id'       => $need('category') . 'int|min:1',
            'name'              => $need('name') . 'string|min:2|max:190',
            'slug'              => 'nullable|string|max:190',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:20000',
            // Raised from 5,000: these three hold rich text now, and markup
            // costs characters the author never typed.
            'benefits'          => 'nullable|string|max:8000',
            'inclusions'        => 'nullable|string|max:8000',
            'contraindications' => 'nullable|string|max:8000',
            'complimentary_enhancement' => 'nullable|string|max:500',
            'price'             => $need('price') . 'numeric|min:0|max:100000',
            'price_display'     => 'nullable|string|max:60',
            'promo_price'       => 'nullable|numeric|min:0|max:100000',
            'duration_minutes'  => 'nullable|int|min:0|max:1440',
            'duration_display'  => 'nullable|string|max:60',
            'icon_key'          => 'nullable|string|max:40',
            'booking_url'       => 'nullable|url|max:500',
            'media_id'          => 'nullable|int|min:0',
            'status'            => 'nullable|in:active,inactive',
            'featured'          => 'nullable|bool',
            'most_loved_rank'   => 'nullable|int|between:1,3',
            'display_order'     => 'nullable|int|min:0',
        ];
    }

    /** Friendly field names for validation messages. */
    public static function labels(): array
    {
        return [
            'category_id'       => 'Category',
            'name'              => 'Service name',
            'short_description' => 'Short description',
            'description'       => 'Full description',
            'contraindications' => 'Who should avoid this',
            'complimentary_enhancement' => 'Complimentary enhancement',
            'price'             => 'Price',
            'price_display'     => 'Price display text',
            'promo_price'       => 'Promotional price',
            'duration_minutes'  => 'Duration',
            'duration_display'  => 'Duration display text',
            'booking_url'       => 'Booking link',
            'media_id'          => 'Image',
            'most_loved_rank'   => 'Most Loved rank',
        ];
    }

    /**
     * Sprite symbol ids that exist in the public page's SVG sprite.
     *
     * @return string[]
     */
    public static function iconKeys(): array
    {
        return array_column(self::iconChoices(), 'key');
    }

    /**
     * What an operator wrote in the icon column, resolved to a sprite key.
     *
     * `i-drop` is a machine key, and nobody maintaining a treatment menu in a
     * spreadsheet is going to type one. This accepts what a real sheet holds —
     * the label, a word from the label, or a phrase meaning "leave it empty" —
     * the same way headerAliases() accepts "Service Name" for `name`, and the
     * same way `status` already accepts "live" and "published".
     *
     * @return array{key: ?string, known: bool}
     *         known=false means "say so"; known=true with a null key means the
     *         operator deliberately asked for no icon.
     */
    public static function resolveIcon(string $value): array
    {
        $needle = self::normaliseTerm($value);

        if ($needle === '') {
            return ['key' => null, 'known' => true];
        }

        // Phrases that plainly mean "no icon". The literal NULL is NOT here:
        // the importer handles it earlier and it means "clear this column",
        // which is a different intent even though the stored result matches.
        // A bare "-" or "—" normalises to an empty string and is caught above.
        if (in_array($needle, ['no icon', 'none', 'no', 'n a', 'na', 'nil'], true)) {
            return ['key' => null, 'known' => true];
        }

        $lookup = self::iconLookup();

        return array_key_exists($needle, $lookup)
            ? ['key' => $lookup[$needle], 'known' => true]
            : ['key' => null, 'known' => false];
    }

    /**
     * Every spelling that resolves to an icon key, built from iconChoices() so
     * the vocabulary has one home.
     *
     * A word appearing in two labels is dropped rather than guessed at — that
     * way adding an icon later can never silently start matching an existing
     * one to the wrong key.
     *
     * @return array<string, string>
     */
    private static function iconLookup(): array
    {
        static $lookup = null;

        if ($lookup !== null) {
            return $lookup;
        }

        $claims = [];   // term => [keys that want it]

        foreach (self::iconChoices() as $choice) {
            $key   = $choice['key'];
            $terms = [
                self::normaliseTerm($key),                       // i-drop
                self::normaliseTerm($choice['label']),           // drop facial
                // "i-spark" also answers to "spark".
                self::normaliseTerm((string) preg_replace('/^i-/', '', $key)),
            ];

            // Each word of the label on its own: "Stone (hot stone)" gives
            // "stone" and "hot stone", which is what a sheet actually says.
            foreach (preg_split('/[()\/,]+/', $choice['label']) ?: [] as $part) {
                $terms[] = self::normaliseTerm($part);
            }

            foreach (array_filter(array_unique($terms)) as $term) {
                $claims[$term][$key] = true;
            }
        }

        $lookup = [];

        foreach ($claims as $term => $keys) {
            if (count($keys) === 1) {
                $lookup[$term] = array_key_first($keys);
            }
        }

        return $lookup;
    }

    /** Lowercased, punctuation-stripped, single-spaced — "Hot  Stone" => "hot stone". */
    private static function normaliseTerm(string $value): string
    {
        $value = strtolower(trim($value));
        $value = (string) preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    /** The same list with the labels the admin form's dropdown shows. */
    public static function iconChoices(): array
    {
        return [
            ['key' => 'i-hands', 'label' => 'Hands (massage)'],
            ['key' => 'i-leaf',  'label' => 'Leaf (wellness)'],
            ['key' => 'i-drop',  'label' => 'Drop (facial)'],
            ['key' => 'i-stone', 'label' => 'Stone (hot stone)'],
            ['key' => 'i-boat',  'label' => 'Boat (waterfront)'],
            ['key' => 'i-crown', 'label' => 'Crown (luxury)'],
            ['key' => 'i-spark', 'label' => 'Sparkle (signature)'],
            ['key' => 'i-gift',  'label' => 'Gift'],
        ];
    }

    // -----------------------------------------------------------------
    // Business rules
    //
    // Both throw HttpException::validation. The form controller lets it fly
    // and the request becomes a 422; the importer catches it per row and
    // records the message against that line. One implementation, two callers,
    // no drift.
    // -----------------------------------------------------------------

    public static function assertCategoryExists(int $categoryId): void
    {
        $exists = Database::fetchValue(
            'SELECT 1 FROM service_categories WHERE id = ? AND deleted_at IS NULL',
            [$categoryId]
        );

        if ($exists === null) {
            throw HttpException::validation(['category_id' => 'Please choose a valid category.']);
        }
    }

    /** A promotional price above the regular price is always a data entry slip. */
    public static function assertPromoBelowPrice(mixed $promoPrice, mixed $price): void
    {
        if ($promoPrice === null || $price === null) {
            return;
        }

        if ((float) $promoPrice >= (float) $price) {
            throw HttpException::validation([
                'promo_price' => 'The promotional price must be lower than the regular price.',
            ]);
        }
    }
}
