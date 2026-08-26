<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;
use Mariah\Core\Slug;

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

    /**
     * Every way an operator might write a category, mapped to its id, in one
     * query — for resolving the `category` column of an imported CSV.
     *
     * Keys are slugified, so "Body Treatments", "body treatments",
     * "BODY TREATMENTS" and "body-treatments" all resolve to the same
     * category. That is what a real spreadsheet contains.
     *
     * @return array<string, int>
     */
    public function lookupMap(): array
    {
        $map = [];

        foreach ($this->options() as $row) {
            $id = (int) $row['id'];

            $map[Slug::make((string) $row['name'])] = $id;
            $map[(string) $row['slug']]             = $id;
        }

        return $map;
    }

    /**
     * Category names as written, for the "no category named X — available: …"
     * message that makes an import error fixable.
     *
     * @return string[]
     */
    public function optionNames(): array
    {
        return array_map(
            static fn (array $row): string => (string) $row['name'],
            $this->options()
        );
    }
}
