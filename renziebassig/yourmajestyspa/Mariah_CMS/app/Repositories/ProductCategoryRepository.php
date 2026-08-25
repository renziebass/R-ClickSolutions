<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class ProductCategoryRepository extends BaseRepository
{
    protected string $table  = 'product_categories';
    protected string $entity = 'product category';
    protected string $alias  = 'pc';

    protected array $fillable = ['name', 'slug', 'description', 'status', 'display_order'];

    protected array $sortable = [
        'name'          => 'pc.name',
        'status'        => 'pc.status',
        'display_order' => 'pc.display_order',
        'updated_at'    => 'pc.updated_at',
        'id'            => 'pc.id',
    ];

    protected array $searchable = ['pc.name', 'pc.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'pc.*,
                (SELECT COUNT(*) FROM products p
                  WHERE p.category_id = pc.id AND p.deleted_at IS NULL) AS products_count';
    }

    protected function listFilters(Request $request): array
    {
        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            return [['pc.status = ?'], [$status]];
        }
        return [[], []];
    }

    protected function decorate(array $row): array
    {
        $row['id']             = (int) $row['id'];
        $row['products_count'] = (int) ($row['products_count'] ?? 0);
        return $row;
    }

    public function productCount(int $id): int
    {
        return (int) Database::fetchValue(
            'SELECT COUNT(*) FROM products WHERE category_id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public function options(): array
    {
        return Database::fetchAll(
            'SELECT id, name, slug, status FROM product_categories
              WHERE deleted_at IS NULL ORDER BY display_order ASC, name ASC'
        );
    }
}
