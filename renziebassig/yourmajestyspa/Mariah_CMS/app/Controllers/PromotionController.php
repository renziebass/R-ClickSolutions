<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\PromotionRepository;

final class PromotionController extends ResourceController
{
    private PromotionRepository $promotions;

    public function __construct()
    {
        $this->promotions = new PromotionRepository();
    }

    protected function repository(): BaseRepository { return $this->promotions; }
    protected function label(): string              { return 'Promotion'; }
    protected function entityType(): string         { return 'promotion'; }
    protected function titleColumn(): string        { return 'title'; }

    /** Promotions publish rather than activate; the dates decide the rest. */
    protected function statusValues(): array
    {
        return ['published', 'draft', 'archived'];
    }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'title'          => $required . 'string|min:2|max:190',
            'slug'           => 'nullable|string|max:190',
            'description'    => 'nullable|string|max:20000',
            'media_id'       => 'nullable|int|min:0',
            'discount_type'  => $required . 'in:percentage,fixed,special_price',
            'discount_value' => 'nullable|numeric|min:0|max:100000',
            'original_price' => 'nullable|numeric|min:0|max:100000',
            'promo_price'    => 'nullable|numeric|min:0|max:100000',
            'badge_label'    => 'nullable|string|max:60',
            'booking_url'    => 'nullable|url|max:500',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'status'         => 'nullable|in:draft,published,archived',
            'featured'       => 'nullable|bool',
            'display_order'  => 'nullable|int|min:0',
        ];
    }

    /** The long promotion copy. */
    protected function richTextFields(): array
    {
        return ['description'];
    }

    protected function fieldLabels(): array
    {
        return [
            'title'          => 'Promotion title',
            'discount_type'  => 'Discount type',
            'discount_value' => 'Discount value',
            'original_price' => 'Original price',
            'promo_price'    => 'Promotional price',
            'start_date'     => 'Start date',
            'end_date'       => 'End date',
            'media_id'       => 'Image',
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

        $type  = $data['discount_type']  ?? ($existing['discount_type'] ?? 'percentage');
        $value = $data['discount_value'] ?? ($existing['discount_value'] ?? null);
        $promo = $data['promo_price']    ?? ($existing['promo_price'] ?? null);

        // Each discount type needs its own number; without it the badge on the
        // public page would render as an empty offer.
        if ($type === 'percentage') {
            if ($value === null || (float) $value <= 0 || (float) $value > 100) {
                throw HttpException::validation([
                    'discount_value' => 'Enter a percentage between 1 and 100.',
                ]);
            }
        } elseif ($type === 'fixed') {
            if ($value === null || (float) $value <= 0) {
                throw HttpException::validation([
                    'discount_value' => 'Enter the amount to take off, greater than zero.',
                ]);
            }
        } elseif ($type === 'special_price') {
            if ($promo === null || (float) $promo <= 0) {
                throw HttpException::validation([
                    'promo_price' => 'Enter the promotional price guests will pay.',
                ]);
            }
            $data['discount_value'] = 0;
        }

        return $data;
    }

    protected function afterSave(int $id, array $data, Request $request, bool $isUpdate): void
    {
        $serviceIds = $request->input('service_ids');
        if (is_array($serviceIds)) {
            $this->promotions->syncServices($id, $serviceIds);
        }
    }

    protected function showExtras(array $row): array
    {
        return ['service_ids' => $this->promotions->serviceIds((int) $row['id'])];
    }
}
