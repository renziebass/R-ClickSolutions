<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class BrandRepository extends BaseRepository
{
    protected string $table  = 'product_brands';
    protected string $entity = 'brand';
    protected string $alias  = 'b';

    protected array $fillable = ['name', 'slug', 'tagline', 'media_id', 'status', 'display_order'];

    protected array $sortable = [
        'name'          => 'b.name',
        'status'        => 'b.status',
        'display_order' => 'b.display_order',
        'updated_at'    => 'b.updated_at',
        'id'            => 'b.id',
    ];

    protected array $searchable = ['b.name', 'b.tagline'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'b.*, m.file_url AS image_url,
                (SELECT COUNT(*) FROM products p
                  WHERE p.brand_id = b.id AND p.deleted_at IS NULL) AS products_count';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN media m ON m.id = b.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            return [['b.status = ?'], [$status]];
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
            'SELECT COUNT(*) FROM products WHERE brand_id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public function options(): array
    {
        return Database::fetchAll(
            'SELECT id, name, slug, status FROM product_brands
              WHERE deleted_at IS NULL ORDER BY display_order ASC, name ASC'
        );
    }
}
