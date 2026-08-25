<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;

final class RoleRepository extends BaseRepository
{
    protected string $table  = 'roles';
    protected string $entity = 'role';
    protected string $alias  = 'r';
    protected bool $softDeletes = false;   // roles are hard-deleted, and only when unused

    protected array $fillable = ['name', 'slug', 'description'];

    protected array $sortable = [
        'name'       => 'r.name',
        'created_at' => 'r.created_at',
        'id'         => 'r.id',
    ];

    protected array $searchable = ['r.name', 'r.description'];

    protected string $defaultSort      = 'id';
    protected string $defaultDirection = 'ASC';

    protected function listSelect(): string
    {
        return 'r.*,
                (SELECT COUNT(*) FROM users u
                  WHERE u.role_id = r.id AND u.deleted_at IS NULL) AS users_count,
                (SELECT COUNT(*) FROM role_permissions rp
                  WHERE rp.role_id = r.id) AS permissions_count';
    }

    protected function decorate(array $row): array
    {
        $row['id']                = (int) $row['id'];
        $row['is_system']         = (bool) $row['is_system'];
        $row['users_count']       = (int) ($row['users_count'] ?? 0);
        $row['permissions_count'] = (int) ($row['permissions_count'] ?? 0);
        return $row;
    }

    public function permissionSlugs(int $roleId): array
    {
        return array_column(
            Database::fetchAll(
                'SELECT p.slug FROM role_permissions rp
                   JOIN permissions p ON p.id = rp.permission_id
                  WHERE rp.role_id = ?
                  ORDER BY p.group_name, p.slug',
                [$roleId]
            ),
            'slug'
        );
    }

    /** @param string[] $slugs */
    public function syncPermissions(int $roleId, array $slugs): void
    {
        Database::transaction(function () use ($roleId, $slugs): void {
            Database::run('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);

            if ($slugs === []) {
                return;
            }

            $placeholders = implode(', ', array_fill(0, count($slugs), '?'));
            $ids = array_column(
                Database::fetchAll(
                    "SELECT id FROM permissions WHERE slug IN ({$placeholders})",
                    array_values($slugs)
                ),
                'id'
            );

            foreach ($ids as $permissionId) {
                Database::run(
                    'INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, (int) $permissionId]
                );
            }
        });
    }

    public function userCount(int $roleId): int
    {
        return (int) Database::fetchValue(
            'SELECT COUNT(*) FROM users WHERE role_id = ? AND deleted_at IS NULL',
            [$roleId]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        $row = Database::fetchOne('SELECT * FROM roles WHERE slug = ?', [$slug]);
        return $row === null ? null : $this->decorate($row);
    }

    public function options(): array
    {
        return Database::fetchAll('SELECT id, name, slug FROM roles ORDER BY id ASC');
    }

    /** Full permission catalogue, grouped for the role editor UI. */
    public function permissionCatalogue(): array
    {
        $rows    = Database::fetchAll(
            'SELECT id, slug, name, group_name FROM permissions ORDER BY group_name, id'
        );
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['group_name']][] = [
                'id'   => (int) $row['id'],
                'slug' => $row['slug'],
                'name' => $row['name'],
            ];
        }

        return $grouped;
    }
}
