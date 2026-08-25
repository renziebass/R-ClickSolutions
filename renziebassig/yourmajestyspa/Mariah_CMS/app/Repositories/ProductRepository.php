<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class ProductRepository extends BaseRepository
{
    protected string $table  = 'products';
    protected string $entity = 'product';
    protected string $alias  = 'p';

    protected array $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'description',
        'price', 'compare_at_price', 'media_id', 'icon_key', 'badge_label',
        'status', 'featured', 'display_order',
    ];

    protected array $sortable = [
        'name'          => 'p.name',
        'price'         => 'p.price',
        'brand'         => 'b.name',
        'category'      => 'pc.name',
        'status'        => 'p.status',
        'featured'      => 'p.featured',
        'display_order' => 'p.display_order',
        'updated_at'    => 'p.updated_at',
        'created_at'    => 'p.created_at',
        'id'            => 'p.id',
    ];

    protected array $searchable = ['p.name', 'p.description', 'b.name'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'p.*,
                b.name  AS brand_name,
                b.slug  AS brand_slug,
                pc.name AS category_name,
                pc.slug AS category_slug,
                m.file_url AS image_url,
                m.alt_text AS image_alt';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN product_brands b      ON b.id  = p.brand_id
                LEFT JOIN product_categories pc ON pc.id = p.category_id
                LEFT JOIN media m               ON m.id  = p.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        if (($brandId = $request->q('brand_id')) !== null && is_numeric($brandId)) {
            $conditions[] = 'p.brand_id = ?';
            $bindings[]   = (int) $brandId;
        }

        if (($categoryId = $request->q('category_id')) !== null && is_numeric($categoryId)) {
            $conditions[] = 'p.category_id = ?';
            $bindings[]   = (int) $categoryId;
        }

        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $conditions[] = 'p.status = ?';
            $bindings[]   = $status;
        }

        $featured = $request->qBool('featured');
        if ($featured !== null) {
            $conditions[] = 'p.featured = ?';
            $bindings[]   = $featured ? 1 : 0;
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']               = (int) $row['id'];
        $row['brand_id']         = $row['brand_id'] === null ? null : (int) $row['brand_id'];
        $row['category_id']      = $row['category_id'] === null ? null : (int) $row['category_id'];
        $row['featured']         = (bool) $row['featured'];
        $row['price']            = (float) $row['price'];
        $row['compare_at_price'] = $row['compare_at_price'] === null ? null : (float) $row['compare_at_price'];
        $row['price_label']      = ServiceRepository::formatMoney((float) $row['price']);

        return $row;
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'active')   AS active,
                    SUM(status = 'inactive') AS inactive
               FROM products WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'    => (int) ($row['total'] ?? 0),
            'active'   => (int) ($row['active'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
        ];
    }
}
