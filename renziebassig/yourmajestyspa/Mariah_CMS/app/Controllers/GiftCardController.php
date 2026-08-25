<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Repositories\BaseRepository;
use Mariah\Repositories\GiftCardRepository;

final class GiftCardController extends ResourceController
{
    private GiftCardRepository $giftCards;

    public function __construct()
    {
        $this->giftCards = new GiftCardRepository();
    }

    protected function repository(): BaseRepository { return $this->giftCards; }
    protected function label(): string              { return 'Gift card'; }
    protected function entityType(): string         { return 'gift_card'; }
    protected function titleColumn(): string        { return 'title'; }

    protected function rules(bool $isUpdate): array
    {
        $required = $isUpdate ? '' : 'required|';

        return [
            'type'           => 'nullable|in:gift_card,membership',
            'title'          => $required . 'string|min:2|max:190',
            'slug'           => 'nullable|string|max:190',
            'description'    => 'nullable|string|max:20000',
            'media_id'       => 'nullable|int|min:0',
            'price'          => 'nullable|numeric|min:0|max:100000',
            'price_display'  => 'nullable|string|max:60',
            'price_interval' => 'nullable|in:one_time,monthly,yearly',
            'purchase_url'   => 'nullable|url|max:500',
            'badge_label'    => 'nullable|string|max:60',
            'status'         => 'nullable|in:active,inactive',
            'featured'       => 'nullable|bool',
            'display_order'  => 'nullable|int|min:0',
        ];
    }

    protected function fieldLabels(): array
    {
        return [
            'title'          => 'Title',
            'price_interval' => 'Billing interval',
            'purchase_url'   => 'Purchase link',
            'media_id'       => 'Image',
        ];
    }

    protected function prepare(array $data, Request $request, ?array $existing): array
    {
        $data = $this->resolveMediaId($data);

        $type     = $data['type']           ?? ($existing['type'] ?? 'gift_card');
        $interval = $data['price_interval'] ?? ($existing['price_interval'] ?? 'one_time');

        // A recurring gift card, or a one-time membership, is a data entry slip.
        if ($type === 'membership' && $interval === 'one_time') {
            throw HttpException::validation([
                'price_interval' => 'A membership must bill monthly or yearly.',
            ]);
        }

        if ($type === 'gift_card' && $interval !== 'one_time') {
            throw HttpException::validation([
                'price_interval' => 'A gift card must be a one-time purchase.',
            ]);
        }

        return $data;
    }
}
