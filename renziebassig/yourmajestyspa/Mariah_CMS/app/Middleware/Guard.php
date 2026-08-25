<?php
declare(strict_types=1);

namespace Mariah\Middleware;

use Mariah\Core\Auth;
use Mariah\Core\Csrf;
use Mariah\Core\HttpException;
use Mariah\Core\Request;

/**
 * Route guards. These run server-side on every protected endpoint — the SPA
 * hiding a button is cosmetic and is never the authorization boundary.
 */
final class Guard
{
    /** Authenticated, active, non-deleted user + valid CSRF on mutations. */
    public static function auth(): callable
    {
        return static function (Request $request): void {
            if (!Auth::check()) {
                throw HttpException::unauthorized();
            }
            Csrf::verify($request);
        };
    }

    /** Authentication plus a specific permission slug. */
    public static function permission(string $slug): callable
    {
        return static function (Request $request) use ($slug): void {
            if (!Auth::check()) {
                throw HttpException::unauthorized();
            }
            Csrf::verify($request);

            if (!Auth::can($slug)) {
                throw HttpException::forbidden(
                    "Your role does not include the \"{$slug}\" permission."
                );
            }
        };
    }

    /** Any one of the given permissions is enough. */
    public static function anyPermission(string ...$slugs): callable
    {
        return static function (Request $request) use ($slugs): void {
            if (!Auth::check()) {
                throw HttpException::unauthorized();
            }
            Csrf::verify($request);

            foreach ($slugs as $slug) {
                if (Auth::can($slug)) {
                    return;
                }
            }
            throw HttpException::forbidden();
        };
    }

    /** Super Admin only — reserved for role management and system settings. */
    public static function superAdmin(): callable
    {
        return static function (Request $request): void {
            if (!Auth::check()) {
                throw HttpException::unauthorized();
            }
            Csrf::verify($request);

            if (!Auth::isSuperAdmin()) {
                throw HttpException::forbidden('This action is restricted to Super Admins.');
            }
        };
    }
}
