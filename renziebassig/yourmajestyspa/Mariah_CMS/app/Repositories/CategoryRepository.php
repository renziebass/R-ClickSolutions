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
        'parent_id', 'name', 'slug', 'description', 'icon_key', 'media_id',
        'status', 'display_order',
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
                (SELECT p.name FROM service_categories p WHERE p.id = c.parent_id) AS parent_name,
                (SELECT COUNT(*) FROM services s
                  WHERE s.category_id = c.id AND s.deleted_at IS NULL) AS services_count,
                (SELECT COUNT(*) FROM service_categories k
                  WHERE k.parent_id = c.id AND k.deleted_at IS NULL) AS children_count';
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
        $row['parent_id']      = $row['parent_id'] === null ? null : (int) $row['parent_id'];
        $row['services_count'] = (int) ($row['services_count'] ?? 0);
        $row['children_count'] = (int) ($row['children_count'] ?? 0);
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

    /** Blocks deleting a category that still holds sub-categories. */
    public function childCount(int $categoryId): int
    {
        return (int) Database::fetchValue(
            'SELECT COUNT(*) FROM service_categories WHERE parent_id = ? AND deleted_at IS NULL',
            [$categoryId]
        );
    }

    /** The parent of a category, or null if it is top level. */
    public function parentId(int $categoryId): ?int
    {
        $parent = Database::fetchValue(
            'SELECT parent_id FROM service_categories WHERE id = ? AND deleted_at IS NULL',
            [$categoryId]
        );

        return $parent === null ? null : (int) $parent;
    }

    /**
     * Lightweight list for form <select> menus, ordered so each sub-category
     * follows its own parent, with `depth` (0 or 1) for the client to indent
     * by. A flat list plus a depth marker is all a two-level tree needs, and
     * the existing select() builder already takes a flat list.
     *
     * `name` stays exactly as stored. lookupMap() slugifies it to resolve the
     * `category` column of an imported CSV, so decorating it here would change
     * what the importer matches on.
     *
     * Inactive categories are included; the SPA appends "(inactive)" itself.
     */
    public function options(): array
    {
        $rows = Database::fetchAll(
            "SELECT id, parent_id, name, slug, status
               FROM service_categories
              WHERE deleted_at IS NULL
              ORDER BY display_order ASC, name ASC"
        );

        $children = [];
        foreach ($rows as $row) {
            if ($row['parent_id'] !== null) {
                $children[(int) $row['parent_id']][] = $row;
            }
        }

        $ordered = [];
        $placed  = [];

        foreach ($rows as $row) {
            if ($row['parent_id'] !== null) {
                continue;   // reached through its parent, below
            }

            $row['depth'] = 0;
            $ordered[]    = $row;
            $placed[(int) $row['id']] = true;

            foreach ($children[(int) $row['id']] ?? [] as $child) {
                $child['depth'] = 1;
                $ordered[]      = $child;
                $placed[(int) $child['id']] = true;
            }
        }

        // A sub-category whose parent was soft-deleted has no parent to be
        // listed under. Without this it would vanish from every form and its
        // services could never be moved anywhere.
        foreach ($rows as $row) {
            if (isset($placed[(int) $row['id']])) {
                continue;
            }

            $row['depth'] = 0;
            $ordered[]    = $row;
        }

        return $ordered;
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
