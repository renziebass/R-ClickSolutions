<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class GiftCardRepository extends BaseRepository
{
    protected string $table  = 'gift_cards';
    protected string $entity = 'gift card';
    protected string $alias  = 'g';

    protected array $fillable = [
        'type', 'title', 'slug', 'description', 'media_id',
        'price', 'price_display', 'price_interval', 'purchase_url', 'badge_label',
        'status', 'featured', 'display_order',
    ];

    protected array $sortable = [
        'title'         => 'g.title',
        'type'          => 'g.type',
        'price'         => 'g.price',
        'status'        => 'g.status',
        'display_order' => 'g.display_order',
        'updated_at'    => 'g.updated_at',
        'id'            => 'g.id',
    ];

    protected array $searchable = ['g.title', 'g.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'g.*, m.file_url AS image_url, m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN media m ON m.id = g.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        $type = (string) $request->q('type', '');
        if (in_array($type, ['gift_card', 'membership'], true)) {
            $conditions[] = 'g.type = ?';
            $bindings[]   = $type;
        }

        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = 'g.status = ?';
            $bindings[]   = $status;
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']       = (int) $row['id'];
        $row['featured'] = (bool) $row['featured'];
        $row['price']    = $row['price'] === null ? null : (float) $row['price'];

        $row['price_label'] = $row['price_display']
            ?: ($row['price'] !== null ? ServiceRepository::formatMoney((float) $row['price']) : null);

        $row['type_label'] = $row['type'] === 'membership' ? 'Membership' : 'Gift card';

        return $row;
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'active' AND type = 'gift_card')  AS active_gift_cards,
                    SUM(status = 'active' AND type = 'membership') AS active_memberships
               FROM gift_cards WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'              => (int) ($row['total'] ?? 0),
            'active_gift_cards'  => (int) ($row['active_gift_cards'] ?? 0),
            'active_memberships' => (int) ($row['active_memberships'] ?? 0),
        ];
    }
}
