<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\SpecialRepository;

final class SpecialController extends ResourceController
{
    private SpecialRepository $specials;

    public function __construct()
    {
        $this->specials = new SpecialRepository();
    }

    protected function repository(): BaseRepository { return $this->specials; }
    protected function label(): string              { return 'Special'; }
    protected function entityType(): string         { return 'special'; }
    protected function titleColumn(): string        { return 'title'; }

    protected function statusValues(): array
    {
        return ['published', 'draft', 'archived'];
    }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'title'            => $required . 'string|min:2|max:190',
            'slug'             => 'nullable|string|max:190',
            'description'      => 'nullable|string|max:20000',
            'media_id'         => 'nullable|int|min:0',
            'badge_label'      => 'nullable|string|max:60',
            'price'            => 'nullable|numeric|min:0|max:100000',
            'price_display'    => 'nullable|string|max:60',
            'compare_at_price' => 'nullable|numeric|min:0|max:100000',
            'booking_url'      => 'nullable|url|max:500',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'status'           => 'nullable|in:draft,published,archived',
            'featured'         => 'nullable|bool',
            'display_order'    => 'nullable|int|min:0',
        ];
    }

    /** The long special copy. */
    protected function richTextFields(): array
    {
        return ['description'];
    }

    protected function fieldLabels(): array
    {
        return [
            'title'            => 'Special title',
            'badge_label'      => 'Badge',
            'price'            => 'Price',
            'price_display'    => 'Price display text',
            'compare_at_price' => 'Original price',
            'start_date'       => 'Start date',
            'end_date'         => 'End date',
            'media_id'         => 'Image',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        $start = $data['start_date'] ?? ($existing['start_date'] ?? null);
        $end   = $data['end_date']   ?? ($existing['end_date']   ?? null);

        if ($start && $end && substr((string) $end, 0, 10) < substr((string) $start, 0, 10)) {
            throw HttpException::validation([
                'end_date' => 'The end date must be on or after the start date.',
            ]);
        }

        // The struck-through "was" price must be higher, or the card reads as a
        // price increase.
        $price   = $data['price']            ?? ($existing['price'] ?? null);
        $compare = $data['compare_at_price'] ?? ($existing['compare_at_price'] ?? null);

        if ($price !== null && $compare !== null && (float) $compare <= (float) $price) {
            throw HttpException::validation([
                'compare_at_price' => 'The original price must be higher than the special price.',
            ]);
        }

        // A special with neither a number nor display text renders a blank price.
        $displayText = $data['price_display'] ?? ($existing['price_display'] ?? null);
        if ($price === null && ($displayText === null || $displayText === '')) {
            throw HttpException::validation([
                'price' => 'Enter a price, or price display text such as "From $109 / mo".',
            ]);
        }

        return $data;
    }
}
