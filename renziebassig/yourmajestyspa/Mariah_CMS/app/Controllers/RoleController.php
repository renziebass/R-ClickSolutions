<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Slug;
use Mariah\Core\Validator;
use Mariah\Repositories\RoleRepository;
use Mariah\Services\AuditLogger;

final class RoleController
{
    private RoleRepository $roles;

    public function __construct()
    {
        $this->roles = new RoleRepository();
    }

    public function index(Request $request): never
    {
        $result = $this->roles->paginate($request);
        Response::json($result['rows'], 200, $result['meta']);
    }

    public function show(Request $request, array $args): never
    {
        $role = $this->roles->findOrFail($this->idFrom($args));

        Response::json(array_merge($role, [
            'permissions' => $this->roles->permissionSlugs((int) $role['id']),
        ]));
    }

    public function store(Request $request): never
    {
        $data = Validator::make($request->body())->validate([
            'name'        => 'required|string|min:2|max:80',
            'description' => 'nullable|string|max:255',
        ], ['name' => 'Role name']);

        $slug = Slug::unique('roles', (string) $data['name']);

        $id = $this->roles->create([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
        ]);

        $permissions = $request->input('permissions');
        if (is_array($permissions)) {
            $this->roles->syncPermissions($id, $this->sanitisePermissions($permissions));
        }

        AuditLogger::record('created', 'role', $id, "Created role \"{$data['name']}\"");

        Response::created(array_merge(
            $this->roles->findOrFail($id),
            ['permissions' => $this->roles->permissionSlugs($id)]
        ));
    }

    public function update(Request $request, array $args): never
    {
        $id     = $this->idFrom($args);
        $before = $this->roles->findOrFail($id);

        $data = Validator::make($request->body())->validate([
            'name'        => 'nullable|string|min:2|max:80',
            'description' => 'nullable|string|max:255',
        ], ['name' => 'Role name']);

        $update = [];

        // System role names are referenced by slug throughout the code; only
        // the description is editable.
        if (!empty($data['name']) && !$before['is_system']) {
            $update['name'] = $data['name'];
            $update['slug'] = Slug::unique('roles', (string) $data['name'], $id);
        }

        if (array_key_exists('description', $data)) {
            $update['description'] = $data['description'];
        }

        if ($update !== []) {
            $this->roles->update($id, $update);
        }

        $permissionsChanged = false;
        $permissions        = $request->input('permissions');

        if (is_array($permissions)) {
            // Stripping permissions from Super Admin would strand the system
            // with no one able to restore them.
            if ($before['slug'] === 'super-admin') {
                throw HttpException::forbidden(
                    'The Super Admin role always holds every permission and cannot be narrowed.'
                );
            }

            $this->roles->syncPermissions($id, $this->sanitisePermissions($permissions));
            $permissionsChanged = true;
        }

        $after = $this->roles->findOrFail($id);

        AuditLogger::record(
            'updated',
            'role',
            $id,
            "Updated role \"{$after['name']}\"" . ($permissionsChanged ? ' and its permissions' : ''),
            AuditLogger::diff($before, $after)
        );

        Response::json(array_merge($after, ['permissions' => $this->roles->permissionSlugs($id)]));
    }

    public function destroy(Request $request, array $args): never
    {
        $id   = $this->idFrom($args);
        $role = $this->roles->findOrFail($id);

        if ($role['is_system']) {
            throw HttpException::conflict(
                "\"{$role['name']}\" is a built-in role and cannot be deleted."
            );
        }

        $userCount = $this->roles->userCount($id);
        if ($userCount > 0) {
            throw HttpException::conflict(
                "\"{$role['name']}\" is assigned to {$userCount} user(s). Reassign them first."
            );
        }

        // Roles are hard-deleted: role_permissions cascades, and no content
        // row references a role, so nothing is orphaned.
        Database::run('DELETE FROM roles WHERE id = ?', [$id]);

        AuditLogger::record('deleted', 'role', $id, "Deleted role \"{$role['name']}\"");

        Response::json(['id' => $id, 'deleted' => true, 'message' => "Role \"{$role['name']}\" was deleted."]);
    }

    /** The full permission catalogue, grouped, for the role editor. */
    public function permissions(Request $request): never
    {
        Response::json($this->roles->permissionCatalogue());
    }

    // -----------------------------------------------------------------

    /** @return string[] only slugs that actually exist */
    private function sanitisePermissions(array $slugs): array
    {
        $slugs = array_values(array_unique(array_filter(
            array_map(static fn ($s): string => is_string($s) ? trim($s) : '', $slugs),
            static fn (string $s): bool => $s !== ''
        )));

        if ($slugs === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($slugs), '?'));

        return array_column(
            Database::fetchAll(
                "SELECT slug FROM permissions WHERE slug IN ({$placeholders})",
                $slugs
            ),
            'slug'
        );
    }

    private function idFrom(array $args): int
    {
        $id = $args['id'] ?? '';

        if (!is_string($id) || !preg_match('/^\d+$/', $id) || (int) $id < 1) {
            throw HttpException::badRequest('Invalid role id.');
        }

        return (int) $id;
    }
}
