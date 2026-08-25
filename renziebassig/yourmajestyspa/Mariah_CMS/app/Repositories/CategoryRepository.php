<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

final class CategoryRepository extends BaseRepository
{
    protected string $table  = 'service_categories';
    protected string $entity = 'category';
    protected string $alias  = 'c';

    protected array $fillable = [
        'name', 'slug', 'description', 'icon_key', 'media_id', 'status', 'display_order',
    ];

    protected array $sortable = [
        'name'          => 'c.name',
        'status'        => 'c.status',
        'display_order' => 'c.display_order',
        'updated_at'    => 'c.updated_at',
        'created_at'    => 'c.created_at',
        'id'            => 'c.id',
    ];

    protected array $searchable = ['c.name', 'c.description'];

    protected string $defaultSort      = 'display_order';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'c.*,
                m.file_url AS image_url,
                (SELECT COUNT(*) FROM services s
                  WHERE s.category_id = c.id AND s.deleted_at IS NULL) AS services_count';
    }

    protected function listJoins(): string
    {
        return 'LEFT JOIN media m ON m.id = c.media_id AND m.deleted_at IS NULL';
    }

    protected function listFilters(Request $request): array
    {
        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            return [['c.status = ?'], [$status]];
        }
        return [[], []];
    }

    protected function decorate(array $row): array
    {
        $row['id']             = (int) $row['id'];
        $row['services_count'] = (int) ($row['services_count'] ?? 0);
        return $row;
    }

    /** Blocks deleting a category that still holds services. */
    public function serviceCount(int $categoryId): int
    {
        return (int) Database::fetchValue(
            'SELECT COUNT(*) FROM services WHERE category_id = ? AND deleted_at IS NULL',
            [$categoryId]
        );
    }

    /** Lightweight list for form <select> menus. */
    public function options(): array
    {
        return Database::fetchAll(
            "SELECT id, name, slug, status
               FROM service_categories
              WHERE deleted_at IS NULL
              ORDER BY display_order ASC, name ASC"
        );
    }
}
