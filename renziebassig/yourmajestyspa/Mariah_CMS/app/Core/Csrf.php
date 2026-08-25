<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Double-submit CSRF protection. The SPA reads the token from GET /auth/csrf
 * (or the login response) and echoes it in X-CSRF-Token on every mutating call.
 * SameSite=Lax already blocks cross-site form posts; this covers the rest.
 */
final class Csrf
{
    private const HEADER = 'X-CSRF-Token';

    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(Request $request): void
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        $expected = $_SESSION['csrf_token'] ?? '';
        $provided = $request->header(self::HEADER)
            ?? (is_string($request->input('_csrf')) ? $request->input('_csrf') : '');

        if ($expected === '' || !is_string($provided) || $provided === ''
            || !hash_equals($expected, $provided)) {
            throw new HttpException(
                419,
                'CSRF_TOKEN_MISMATCH',
                'Your session has expired. Please refresh the page and try again.'
            );
        }
    }
}
