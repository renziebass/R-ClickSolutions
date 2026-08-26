<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

/**
 * Add-ons: the enhancements a guest can attach to a treatment.
 *
 * An add-on belongs to one category and carries its own price, because the
 * price is a property of the menu it appears on rather than of the thing
 * itself — "Aromatherapy" is +$25 on the massage menu and +$20 on the facial
 * menu. Two rows, one per category, is what the real menu does; a shared
 * catalogue with one price would have to be wrong for one of them.
 */
final class AddonRepository extends BaseRepository
{
    protected string $table  = 'service_addons';
    protected string $entity = 'add-on';
    protected string $alias  = 'a';

    protected array $fillable = [
        'category_id', 'name', 'description', 'price',
        'duration_minutes', 'status', 'display_order',
    ];

    protected array $sortable = [
        'name'          => 'a.name',
        'price'         => 'a.price',
        'category'      => 'c.name',
        'status'        => 'a.status',
        'display_order' => 'a.display_order',
        'updated_at'    => 'a.updated_at',
        'created_at'    => 'a.created_at',
        'id'            => 'a.id',
    ];

    protected array $searchable = ['a.name', 'a.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'a.*,
                c.name AS category_name,
                c.slug AS category_slug';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN service_categories c ON c.id = a.category_id';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        if (($categoryId = $request->q('category_id')) !== null && is_numeric($categoryId)) {
            $conditions[] = 'a.category_id = ?';
            $bindings[]   = (int) $categoryId;
        }

        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = 'a.status = ?';
            $bindings[]   = $status;
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']               = (int) $row['id'];
        $row['category_id']      = (int) $row['category_id'];
        $row['price']            = (float) $row['price'];
        $row['duration_minutes'] = $row['duration_minutes'] === null
            ? null
            : (int) $row['duration_minutes'];

        // Written with the sign, because an add-on is always an addition to
        // something else and "$25" alone reads like the whole price.
        $row['price_label'] = '+' . ServiceRepository::formatMoney($row['price']);

        return $row;
    }

    /**
     * Live add-ons for many categories in one query, keyed by category id, for
     * the public menu. Fetching them per category would be an N+1.
     *
     * @param  int[] $categoryIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function activeFor(array $categoryIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $rows = Database::fetchAll(
            "SELECT id, category_id, name, description, price, duration_minutes
               FROM service_addons
              WHERE category_id IN ({$placeholders})
                AND status = 'active' AND deleted_at IS NULL
              ORDER BY display_order ASC, name ASC",
            $ids
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['category_id']][] = $this->decorate($row);
        }

        return $grouped;
    }
}
