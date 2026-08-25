<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\CategoryRepository;
use Mariah\Repositories\ServiceRepository;

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

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'category_id'       => $required . 'int|min:1',
            'name'              => $required . 'string|min:2|max:190',
            'slug'              => 'nullable|string|max:190',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string|max:20000',
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

    protected function fieldLabels(): array
    {
        return [
            'category_id'       => 'Category',
            'name'              => 'Service name',
            'short_description' => 'Short description',
            'description'       => 'Full description',
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

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        if (array_key_exists('category_id', $data)) {
            $exists = Database::fetchValue(
                'SELECT 1 FROM service_categories WHERE id = ? AND deleted_at IS NULL',
                [(int) $data['category_id']]
            );

            if ($exists === null) {
                throw HttpException::validation(['category_id' => 'Please choose a valid category.']);
            }
        }

        // A promotional price above the regular price is always a data entry slip.
        $price      = $data['price']       ?? ($existing['price'] ?? null);
        $promoPrice = $data['promo_price'] ?? ($existing['promo_price'] ?? null);

        if ($promoPrice !== null && $price !== null && (float) $promoPrice >= (float) $price) {
            throw HttpException::validation([
                'promo_price' => 'The promotional price must be lower than the regular price.',
            ]);
        }

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
    }

    protected function showExtras(array $row): array
    {
        return ['images' => $this->services->images((int) $row['id'])];
    }

    /** Category and icon choices for the add/edit form. */
    public function formOptions(Request $request): never
    {
        Response::json([
            'categories' => (new CategoryRepository())->options(),
            // Sprite symbol ids that exist in the public page's SVG sprite.
            'icons'      => [
                ['key' => 'i-hands', 'label' => 'Hands (massage)'],
                ['key' => 'i-leaf',  'label' => 'Leaf (wellness)'],
                ['key' => 'i-drop',  'label' => 'Drop (facial)'],
                ['key' => 'i-stone', 'label' => 'Stone (hot stone)'],
                ['key' => 'i-boat',  'label' => 'Boat (waterfront)'],
                ['key' => 'i-crown', 'label' => 'Crown (luxury)'],
                ['key' => 'i-spark', 'label' => 'Sparkle (signature)'],
                ['key' => 'i-gift',  'label' => 'Gift'],
            ],
        ]);
    }
}
