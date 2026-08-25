<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Database;
use Mariah\Core\Request;

/**
 * Note the explicit column lists everywhere: `password_hash` is never included
 * in anything this repository returns to a controller, so it cannot leak into
 * an API response by accident.
 */
final class UserRepository extends BaseRepository
{
    protected string $table  = 'users';
    protected string $entity = 'user';
    protected string $alias  = 'u';

    protected array $fillable = [
        'first_name', 'last_name', 'email', 'password_hash', 'role_id', 'status',
    ];

    protected array $sortable = [
        'name'       => 'u.first_name',
        'email'      => 'u.email',
        'role'       => 'r.name',
        'status'     => 'u.status',
        'last_login' => 'u.last_login_at',
        'created_at' => 'u.created_at',
        'id'         => 'u.id',
    ];

    protected array $searchable = ['u.first_name', 'u.last_name', 'u.email'];

    protected string $defaultSort      = 'created_at';
    protected string $defaultDirection = 'DESC';

    protected function listSelect(): string
    {
        return 'u.id, u.first_name, u.last_name, u.email, u.role_id, u.status,
                u.last_login_at, u.last_login_ip, u.created_at, u.updated_at, u.deleted_at,
                r.name AS role_name, r.slug AS role_slug';
    }

    protected function listJoins(): string
    {
        return 'JOIN roles r ON r.id = u.role_id';
    }

    protected function listFilters(Request $request): array
    {
        $conditions = [];
        $bindings   = [];

        if (($roleId = $request->q('role_id')) !== null && is_numeric($roleId)) {
            $conditions[] = 'u.role_id = ?';
            $bindings[]   = (int) $roleId;
        }

        $status = (string) $request->q('status', '');
        if (in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $conditions[] = 'u.status = ?';
            $bindings[]   = $status;
        }

        return [$conditions, $bindings];
    }

    protected function decorate(array $row): array
    {
        $row['id']        = (int) $row['id'];
        $row['role_id']   = (int) $row['role_id'];
        $row['full_name'] = trim($row['first_name'] . ' ' . $row['last_name']);

        $row['role'] = [
            'id'   => $row['role_id'],
            'name' => $row['role_name'],
            'slug' => $row['role_slug'],
        ];

        unset($row['role_name'], $row['role_slug'], $row['password_hash']);

        return $row;
    }

    /** Includes password_hash — used only by AuthService for verification. */
    public function findForAuthentication(string $email): ?array
    {
        return Database::fetchOne(
            'SELECT u.id, u.first_name, u.last_name, u.email, u.password_hash,
                    u.status, u.role_id, r.slug AS role_slug
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.email = ? AND u.deleted_at IS NULL
              LIMIT 1',
            [$email]
        );
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql    = 'SELECT 1 FROM users WHERE email = ?';
        $params = [$email];

        if ($ignoreId !== null) {
            $sql     .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        return Database::fetchValue($sql . ' LIMIT 1', $params) !== null;
    }

    public function roleSlugOf(int $userId): ?string
    {
        $slug = Database::fetchValue(
            'SELECT r.slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
            [$userId]
        );

        return $slug === null ? null : (string) $slug;
    }

    public function recordLogin(int $userId, string $ip): void
    {
        Database::run(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?',
            [$ip, $userId]
        );
    }

    public function updatePassword(int $userId, string $hash): void
    {
        Database::run(
            'UPDATE users SET password_hash = ?, updated_by = ? WHERE id = ?',
            [$hash, \Mariah\Core\Auth::id(), $userId]
        );
    }

    /** Guards against deleting or deactivating the last usable Super Admin. */
    public function activeSuperAdminCount(?int $excludingUserId = null): int
    {
        $sql = "SELECT COUNT(*)
                  FROM users u JOIN roles r ON r.id = u.role_id
                 WHERE r.slug = 'super-admin'
                   AND u.status = 'active'
                   AND u.deleted_at IS NULL";
        $params = [];

        if ($excludingUserId !== null) {
            $sql     .= ' AND u.id <> ?';
            $params[] = $excludingUserId;
        }

        return (int) Database::fetchValue($sql, $params);
    }

    public function stats(): array
    {
        $row = Database::fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'active')    AS active,
                    SUM(status = 'inactive')  AS inactive,
                    SUM(status = 'suspended') AS suspended
               FROM users WHERE deleted_at IS NULL"
        ) ?? [];

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'active'    => (int) ($row['active'] ?? 0),
            'inactive'  => (int) ($row['inactive'] ?? 0),
            'suspended' => (int) ($row['suspended'] ?? 0),
        ];
    }
}
