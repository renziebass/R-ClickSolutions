<?php
declare(strict_types=1);

/**
 * The canonical permission catalogue and the role → permission map.
 *
 * This file is the single source of truth. `php database/seed.php --sync`
 * re-syncs the database from it, so adding a module later means adding its
 * permissions here — no authorization logic needs rewriting.
 */

$catalogue = [
    'Dashboard' => [
        'dashboard.view'      => 'View the dashboard',
    ],
    'Services' => [
        'services.view'       => 'View services',
        'services.create'     => 'Create services',
        'services.edit'       => 'Edit services',
        'services.delete'     => 'Delete services',
        'services.activate'   => 'Activate / deactivate services',
    ],
    'Service categories' => [
        'categories.view'     => 'View service categories',
        'categories.create'   => 'Create service categories',
        'categories.edit'     => 'Edit service categories',
        'categories.delete'   => 'Delete service categories',
    ],
    'Promotions' => [
        'promotions.view'     => 'View promotions',
        'promotions.create'   => 'Create promotions',
        'promotions.edit'     => 'Edit promotions',
        'promotions.delete'   => 'Delete promotions',
        'promotions.activate' => 'Publish / unpublish promotions',
    ],
    'Specials' => [
        'specials.view'       => 'View specials',
        'specials.create'     => 'Create specials',
        'specials.edit'       => 'Edit specials',
        'specials.delete'     => 'Delete specials',
        'specials.activate'   => 'Publish / unpublish specials',
    ],
    'Blog posts' => [
        'blog_posts.view'     => 'View blog posts',
        'blog_posts.create'   => 'Create blog posts',
        'blog_posts.edit'     => 'Edit blog posts',
        'blog_posts.delete'   => 'Delete blog posts',
        'blog_posts.activate' => 'Publish / unpublish blog posts',
    ],
    'Blog topics' => [
        'blog_categories.view'   => 'View blog topics',
        'blog_categories.create' => 'Create blog topics',
        'blog_categories.edit'   => 'Edit blog topics',
        'blog_categories.delete' => 'Delete blog topics',
    ],
    'Shop products' => [
        'products.view'       => 'View shop products',
        'products.create'     => 'Create shop products',
        'products.edit'       => 'Edit shop products',
        'products.delete'     => 'Delete shop products',
        'products.activate'   => 'Activate / deactivate shop products',
    ],
    'Product categories & brands' => [
        'product_categories.view'   => 'View product categories',
        'product_categories.create' => 'Create product categories',
        'product_categories.edit'   => 'Edit product categories',
        'product_categories.delete' => 'Delete product categories',
        'brands.view'         => 'View brands',
        'brands.create'       => 'Create brands',
        'brands.edit'         => 'Edit brands',
        'brands.delete'       => 'Delete brands',
    ],
    'Gift cards & memberships' => [
        'gift_cards.view'     => 'View gift cards and memberships',
        'gift_cards.create'   => 'Create gift cards and memberships',
        'gift_cards.edit'     => 'Edit gift cards and memberships',
        'gift_cards.delete'   => 'Delete gift cards and memberships',
        'gift_cards.activate' => 'Activate / deactivate gift cards',
    ],
    'Media' => [
        'media.view'          => 'View the media library',
        'media.upload'        => 'Upload images',
        'media.edit'          => 'Edit image metadata',
        'media.delete'        => 'Delete images',
    ],
    'Users' => [
        'users.view'          => 'View users',
        'users.create'        => 'Create users',
        'users.edit'          => 'Edit users',
        'users.delete'        => 'Delete users',
    ],
    'Roles & permissions' => [
        'roles.view'          => 'View roles',
        'roles.create'        => 'Create roles',
        'roles.edit'          => 'Edit roles and their permissions',
        'roles.delete'        => 'Delete roles',
    ],
    'Audit logs' => [
        'audit_logs.view'     => 'View the audit log',
    ],
];

/** Flat list of every permission slug. */
$all = [];
foreach ($catalogue as $group => $perms) {
    foreach ($perms as $slug => $name) {
        $all[] = $slug;
    }
}

/** Every ".view" permission — the read-only baseline. */
$viewOnly = array_values(array_filter($all, static fn (string $s): bool => str_ends_with($s, '.view')));

/** Content create/edit without any delete, user or role rights. */
$editorPerms = array_merge($viewOnly, [
    'services.create',   'services.edit',   'services.activate',
    'categories.create', 'categories.edit',
    'promotions.create', 'promotions.edit', 'promotions.activate',
    'specials.create',   'specials.edit',   'specials.activate',
    'blog_posts.create', 'blog_posts.edit', 'blog_posts.activate',
    'blog_categories.create', 'blog_categories.edit',
    'products.create',   'products.edit',   'products.activate',
    'product_categories.create', 'product_categories.edit',
    'brands.create',     'brands.edit',
    'gift_cards.create', 'gift_cards.edit', 'gift_cards.activate',
    'media.upload',      'media.edit',
]);
// An Editor has no business reading the audit trail or the user list.
$editorPerms = array_values(array_diff($editorPerms, ['users.view', 'roles.view', 'audit_logs.view']));

/** Full content control, plus read-only visibility of users and the audit log. */
$adminPerms = array_values(array_diff($all, [
    'roles.create', 'roles.edit', 'roles.delete',   // role editing is Super Admin only
    'users.delete',                                  // deleting staff is Super Admin only
]));

/** Staff can look, not touch, and cannot see users, roles or the audit log. */
$staffPerms = array_values(array_diff($viewOnly, ['users.view', 'roles.view', 'audit_logs.view']));

return [
    'catalogue' => $catalogue,
    'roles'     => [
        'super-admin' => [
            'name'        => 'Super Admin',
            'description' => 'Unrestricted access, including users, roles and system settings.',
            'permissions' => $all,
        ],
        'admin' => [
            'name'        => 'Admin',
            'description' => 'Full content management. Cannot manage roles or Super Admin accounts.',
            'permissions' => $adminPerms,
        ],
        'editor' => [
            'name'        => 'Editor',
            'description' => 'Creates and edits content. Cannot delete records or manage users and roles.',
            'permissions' => $editorPerms,
        ],
        'staff' => [
            'name'        => 'Staff',
            'description' => 'Read-only access to services, promotions, specials, blog posts and shop content.',
            'permissions' => $staffPerms,
        ],
    ],
];
