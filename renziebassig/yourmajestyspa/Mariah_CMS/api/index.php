<?php
declare(strict_types=1);

/**
 * Mariah_CMS API front controller.
 *
 * Every request enters here. Routes carry their own guards, so authorization
 * is enforced per endpoint on the server regardless of what the client sends.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use Mariah\Controllers\AddonController;
use Mariah\Controllers\AuditLogController;
use Mariah\Controllers\AuthController;
use Mariah\Controllers\BlogCategoryController;
use Mariah\Controllers\BlogPostController;
use Mariah\Controllers\BrandController;
use Mariah\Controllers\CategoryController;
use Mariah\Controllers\DashboardController;
use Mariah\Controllers\GiftCardController;
use Mariah\Controllers\MediaController;
use Mariah\Controllers\ProductCategoryController;
use Mariah\Controllers\ProductController;
use Mariah\Controllers\PromotionController;
use Mariah\Controllers\PublicContentController;
use Mariah\Controllers\RoleController;
use Mariah\Controllers\ServiceController;
use Mariah\Controllers\SettingsController;
use Mariah\Controllers\SpecialController;
use Mariah\Controllers\UserController;
use Mariah\Core\Auth;
use Mariah\Core\Clock;
use Mariah\Core\Env;
use Mariah\Core\HttpException;
use Mariah\Core\Logger;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Router;
use Mariah\Middleware\Guard;

// ---------------------------------------------------------------------
// CORS — the public endpoints may be read cross-origin if configured.
// Admin endpoints never send credentials cross-origin.
// ---------------------------------------------------------------------
$allowedOrigins = array_filter(array_map('trim', explode(',', Env::string('PUBLIC_CORS_ORIGINS', ''))));
$origin         = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---------------------------------------------------------------------
// Clocks — before anything reads or writes a timestamp. Points PHP and the
// MySQL session at the configured zone so the two agree, since both write
// into the same tz-naive DATETIME columns. Opens the database connection, so
// it sits after the preflight exit above rather than before it. Never throws.
// ---------------------------------------------------------------------
Clock::boot();

try {
    $request = new Request();

    // Public content endpoints are anonymous by definition. Starting a session
    // for them would set a cookie on every website visitor and create a session
    // file per page view, for a request that never reads identity.
    if (!str_starts_with($request->path(), '/public/')) {
        Auth::startSession();
    }

    $router = new Router();

    // =================================================================
    // PUBLIC — no authentication. Active/published content only.
    // =================================================================
    $public = new PublicContentController();

    $router->get('/public/bootstrap',          [$public, 'bootstrap']);
    $router->get('/public/services',           [$public, 'services']);
    $router->get('/public/services/{slug}',    [$public, 'service']);
    $router->get('/public/categories',         [$public, 'categories']);
    $router->get('/public/specials',           [$public, 'specials']);
    $router->get('/public/promotions',         [$public, 'promotions']);
    $router->get('/public/products',           [$public, 'products']);
    $router->get('/public/product-categories', [$public, 'productCategories']);
    $router->get('/public/brands',             [$public, 'brands']);
    $router->get('/public/gift-cards',         [$public, 'giftCards']);
    $router->get('/public/blog-posts',          [$public, 'blogPosts']);
    $router->get('/public/blog-posts/{slug}',   [$public, 'blogPost']);
    $router->get('/public/blog-categories',     [$public, 'blogCategories']);

    // =================================================================
    // AUTHENTICATION
    // =================================================================
    $auth = new AuthController();

    $router->get('/auth/csrf',    [$auth, 'csrf']);
    $router->post('/auth/login',  [$auth, 'login']);            // rate-limited inside
    $router->post('/auth/logout', [$auth, 'logout'], [Guard::auth()]);
    $router->get('/auth/me',      [$auth, 'me']);
    $router->post('/auth/password', [$auth, 'changePassword'], [Guard::auth()]);

    // =================================================================
    // DASHBOARD
    // =================================================================
    $router->get('/dashboard/stats', [new DashboardController(), 'stats'],
        [Guard::permission('dashboard.view')]);

    // =================================================================
    // CONTENT RESOURCES
    // Each verb carries its own permission — an Editor calling
    // DELETE /api/services/123 by hand is rejected with 403.
    // =================================================================

    /**
     * Registers the standard CRUD surface for one resource.
     *
     * @param string $prefix       route prefix, e.g. "services"
     * @param object $controller   a ResourceController
     * @param string $permission   permission group, e.g. "services"
     * @param bool   $hasStatus    register PATCH /:id/status
     * @param bool   $hasDuplicate register POST /:id/duplicate
     */
    $registerResource = static function (
        Router $router,
        string $prefix,
        object $controller,
        string $permission,
        bool $hasStatus = true,
        bool $hasDuplicate = true
    ): void {
        $router->get("/{$prefix}", [$controller, 'index'],
            [Guard::permission("{$permission}.view")]);

        $router->get("/{$prefix}/{id}", [$controller, 'show'],
            [Guard::permission("{$permission}.view")]);

        $router->post("/{$prefix}", [$controller, 'store'],
            [Guard::permission("{$permission}.create")]);

        $router->put("/{$prefix}/{id}", [$controller, 'update'],
            [Guard::permission("{$permission}.edit")]);

        $router->delete("/{$prefix}/{id}", [$controller, 'destroy'],
            [Guard::permission("{$permission}.delete")]);

        $router->post("/{$prefix}/{id}/restore", [$controller, 'restore'],
            [Guard::permission("{$permission}.delete")]);

        if ($hasStatus) {
            // Falls back to .edit for resources with no dedicated activate right.
            $activate = in_array($permission, [
                'services', 'promotions', 'specials', 'products', 'gift_cards', 'blog_posts',
            ], true) ? "{$permission}.activate" : "{$permission}.edit";

            $router->patch("/{$prefix}/{id}/status", [$controller, 'setStatus'],
                [Guard::permission($activate)]);
        }

        if ($hasDuplicate) {
            $router->post("/{$prefix}/{id}/duplicate", [$controller, 'duplicate'],
                [Guard::permission("{$permission}.create")]);
        }
    };

    // --- Services -----------------------------------------------------
    $services = new ServiceController();
    $router->get('/services/form-options', [$services, 'formOptions'],
        [Guard::permission('services.view')]);

    // Bulk CSV import. Its own permission, not services.create: one file can
    // rewrite the whole public menu.
    $router->post('/services/import', [$services, 'import'],
        [Guard::permission('services.import')]);

    $registerResource($router, 'services', $services, 'services');

    // --- Service categories -------------------------------------------
    $categories = new CategoryController();
    $router->get('/categories/options', [$categories, 'options'],
        [Guard::anyPermission('categories.view', 'services.view')]);
    $registerResource($router, 'categories', $categories, 'categories');

    // --- Service add-ons ----------------------------------------------
    // Gated by the services permissions rather than slugs of its own: an
    // add-on is a line on the treatment menu, managed by whoever manages the
    // menu. No duplicate route — an add-on is two fields, so copying one is
    // slower than typing it.
    $registerResource($router, 'addons', new AddonController(), 'services', true, false);

    // --- Promotions ---------------------------------------------------
    $registerResource($router, 'promotions', new PromotionController(), 'promotions');

    // --- Specials -----------------------------------------------------
    $registerResource($router, 'specials', new SpecialController(), 'specials');

    // --- Blog posts ---------------------------------------------------
    // The post form fills its topic dropdown from /blog-categories/options
    // below, so this resource needs no form-options endpoint of its own.
    $registerResource($router, 'blog-posts', new BlogPostController(), 'blog_posts');

    // --- Blog topics --------------------------------------------------
    $blogCategories = new BlogCategoryController();
    $router->get('/blog-categories/options', [$blogCategories, 'options'],
        [Guard::anyPermission('blog_categories.view', 'blog_posts.view')]);
    $registerResource($router, 'blog-categories', $blogCategories, 'blog_categories');

    // --- Shop products ------------------------------------------------
    $products = new ProductController();
    $router->get('/products/form-options', [$products, 'formOptions'],
        [Guard::permission('products.view')]);
    $registerResource($router, 'products', $products, 'products');

    // --- Product categories -------------------------------------------
    $productCategories = new ProductCategoryController();
    $router->get('/product-categories/options', [$productCategories, 'options'],
        [Guard::anyPermission('product_categories.view', 'products.view')]);
    $registerResource($router, 'product-categories', $productCategories, 'product_categories');

    // --- Brands -------------------------------------------------------
    $brands = new BrandController();
    $router->get('/brands/options', [$brands, 'options'],
        [Guard::anyPermission('brands.view', 'products.view')]);
    $registerResource($router, 'brands', $brands, 'brands');

    // --- Gift cards & memberships -------------------------------------
    $registerResource($router, 'gift-cards', new GiftCardController(), 'gift_cards');

    // =================================================================
    // MEDIA
    // =================================================================
    $media = new MediaController();

    $router->get('/media',             [$media, 'index'],   [Guard::permission('media.view')]);
    $router->get('/media/{id}',        [$media, 'show'],    [Guard::permission('media.view')]);
    $router->get('/media/{id}/usage',  [$media, 'usage'],   [Guard::permission('media.view')]);
    $router->post('/media',            [$media, 'store'],   [Guard::permission('media.upload')]);
    $router->put('/media/{id}',        [$media, 'update'],  [Guard::permission('media.edit')]);
    $router->delete('/media/{id}',     [$media, 'destroy'], [Guard::permission('media.delete')]);

    // =================================================================
    // USERS
    // =================================================================
    $users = new UserController();

    $router->get('/users/assignable-roles', [$users, 'assignableRoles'],
        [Guard::permission('users.view')]);
    $router->get('/users',           [$users, 'index'],   [Guard::permission('users.view')]);
    $router->get('/users/{id}',      [$users, 'show'],    [Guard::permission('users.view')]);
    $router->post('/users',          [$users, 'store'],   [Guard::permission('users.create')]);
    $router->put('/users/{id}',      [$users, 'update'],  [Guard::permission('users.edit')]);
    $router->delete('/users/{id}',   [$users, 'destroy'], [Guard::permission('users.delete')]);
    $router->post('/users/{id}/restore', [$users, 'restore'], [Guard::permission('users.delete')]);

    // =================================================================
    // ROLES & PERMISSIONS — Super Admin only for writes.
    // =================================================================
    $roles = new RoleController();

    $router->get('/permissions',    [$roles, 'permissions'], [Guard::permission('roles.view')]);
    $router->get('/roles',          [$roles, 'index'],       [Guard::permission('roles.view')]);
    $router->get('/roles/{id}',     [$roles, 'show'],        [Guard::permission('roles.view')]);
    $router->post('/roles',         [$roles, 'store'],       [Guard::superAdmin()]);
    $router->put('/roles/{id}',     [$roles, 'update'],      [Guard::superAdmin()]);
    $router->delete('/roles/{id}',  [$roles, 'destroy'],     [Guard::superAdmin()]);

    // =================================================================
    // AUDIT LOGS — read-only, by design.
    // =================================================================
    $auditLogs = new AuditLogController();

    $router->get('/audit-logs',         [$auditLogs, 'index'],   [Guard::permission('audit_logs.view')]);
    $router->get('/audit-logs/filters', [$auditLogs, 'filters'], [Guard::permission('audit_logs.view')]);

    // =================================================================
    // SITE SETTINGS — the CMS's own configuration, editable in the browser.
    // One record that is really a bag of keys, so there is no /settings/{id}.
    // =================================================================
    $settings = new SettingsController();

    $router->get('/settings', [$settings, 'index'],  [Guard::permission('settings.view')]);
    $router->put('/settings', [$settings, 'update'], [Guard::permission('settings.edit')]);

    // =================================================================
    $router->dispatch($request);

} catch (HttpException $e) {
    // Expected, user-facing failures.
    Response::error($e->status(), $e->errorCode(), $e->getMessage(), $e->fields());

} catch (\PDOException $e) {
    // Duplicate keys are a real conflict the user can act on; everything else
    // is an internal fault, and the raw driver message never leaves the server.
    $reference = Logger::error($e, ['path' => $_SERVER['REQUEST_URI'] ?? '']);

    if (($e->errorInfo[1] ?? null) === 1062) {
        Response::error(409, 'CONFLICT', 'That record already exists. Please use a different name.');
    }

    Response::error(
        500,
        'SERVER_ERROR',
        "Something went wrong while saving. Please try again. (Reference: {$reference})"
    );

} catch (\Throwable $e) {
    $reference = Logger::error($e, ['path' => $_SERVER['REQUEST_URI'] ?? '']);

    Response::error(
        500,
        'SERVER_ERROR',
        "Something went wrong. Please try again. (Reference: {$reference})"
    );
}
