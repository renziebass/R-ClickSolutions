<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Database;
use Mariah\Core\HttpException;

/**
 * The one definition of what a service is, shared by the admin form and the
 * CSV importer.
 *
 * Validation rules, field labels, the icon allowlist and the two cross-field
 * business rules all live here so the two write paths cannot drift apart. An
 * importer that accepted data the form rejects would be worse than no importer
 * at all.
 *
 * Pure static, no I/O beyond the two assertions.
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

    /** @return string[] */
    public static function requiredColumns(): array
    {
        $required = [];

        foreach (self::columns() as $column) {
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
     */
    public static function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'category_id'       => $required . 'int|min:1',
            'name'              => $required . 'string|min:2|max:190',
            'slug'              => 'nullable|string|max:190',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:20000',
            'benefits'          => 'nullable|string|max:5000',
            'inclusions'        => 'nullable|string|max:5000',
            'contraindications' => 'nullable|string|max:5000',
            'complimentary_enhancement' => 'nullable|string|max:500',
            'price'             => $required . 'numeric|min:0|max:100000',
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
