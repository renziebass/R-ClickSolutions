<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Session-backed authentication.
 *
 * The admin SPA is same-origin with the API, so a hardened PHP session cookie
 * is used instead of a JWT: nothing auth-related is reachable from JavaScript,
 * and revocation is immediate. See README "Why sessions, not JWT".
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $resolved = false;

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(Env::string('SESSION_NAME', 'mariah_cms_session'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => Env::bool('SESSION_COOKIE_SECURE', true),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        self::enforceIdleTimeout();
    }

    private static function enforceIdleTimeout(): void
    {
        $timeout = Env::int('SESSION_IDLE_TIMEOUT', 28800);
        $last    = $_SESSION['last_activity'] ?? null;

        if ($last !== null && (time() - (int) $last) > $timeout) {
            self::logout();
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    public static function login(array $user): void
    {
        // New ID on privilege change defeats session fixation.
        session_regenerate_id(true);

        $_SESSION['user_id']       = (int) $user['id'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token']    = bin2hex(random_bytes(32));

        self::$user     = null;
        self::$resolved = false;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        self::$user     = null;
        self::$resolved = true;
    }

    /**
     * Re-reads the user (with role and permissions) from the database on every
     * request. A deactivated or deleted account loses access immediately rather
     * than at session expiry.
     */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        $id = $_SESSION['user_id'] ?? null;
        if ($id === null) {
            return self::$user = null;
        }

        $row = Database::fetchOne(
            'SELECT u.id, u.first_name, u.last_name, u.email, u.status,
                    u.last_login_at, u.created_at,
                    r.id AS role_id, r.name AS role_name, r.slug AS role_slug
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.id = ? AND u.deleted_at IS NULL',
            [(int) $id]
        );

        if ($row === null || $row['status'] !== 'active') {
            return self::$user = null;
        }

        $row['permissions'] = array_column(
            Database::fetchAll(
                'SELECT p.slug
                   FROM role_permissions rp
                   JOIN permissions p ON p.id = rp.permission_id
                  WHERE rp.role_id = ?',
                [(int) $row['role_id']]
            ),
            'slug'
        );

        $row['id']      = (int) $row['id'];
        $row['role_id'] = (int) $row['role_id'];

        return self::$user = $row;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isSuperAdmin(): bool
    {
        return (self::user()['role_slug'] ?? null) === 'super-admin';
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }
        return in_array($permission, $user['permissions'], true);
    }

    /** The shape returned by /auth/me — never includes password_hash. */
    public static function publicProfile(): ?array
    {
        $u = self::user();
        if ($u === null) {
            return null;
        }

        return [
            'id'           => $u['id'],
            'first_name'   => $u['first_name'],
            'last_name'    => $u['last_name'],
            'full_name'    => trim($u['first_name'] . ' ' . $u['last_name']),
            'email'        => $u['email'],
            'status'       => $u['status'],
            'last_login_at'=> $u['last_login_at'],
            'role'         => [
                'id'   => $u['role_id'],
                'name' => $u['role_name'],
                'slug' => $u['role_slug'],
            ],
            'permissions'  => $u['permissions'],
        ];
    }
}
