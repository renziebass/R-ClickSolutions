<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Validator;
use Mariah\Repositories\RoleRepository;
use Mariah\Repositories\UserRepository;
use Mariah\Services\AuditLogger;

/**
 * User administration.
 *
 * Two guards sit above the permission system, because "users.edit" alone would
 * otherwise let an Admin escalate themselves:
 *   1. Only a Super Admin may create, edit or delete a Super Admin account.
 *   2. Only a Super Admin may assign the Super Admin role to anyone.
 * Both are enforced here, on the server, for every request path.
 */
final class UserController
{
    private UserRepository $users;
    private RoleRepository $roles;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->roles = new RoleRepository();
    }

    public function index(Request $request): never
    {
        $result = $this->users->paginate($request);
        Response::json($result['rows'], 200, $result['meta']);
    }

    public function show(Request $request, array $args): never
    {
        Response::json($this->users->findOrFail($this->idFrom($args), true));
    }

    public function store(Request $request): never
    {
        $data = Validator::make($request->body())->validate([
            'first_name' => 'required|string|min:1|max:80',
            'last_name'  => 'required|string|min:1|max:80',
            'email'      => 'required|email|max:190',
            'password'   => 'required|string|min:10|max:200',
            'role_id'    => 'required|int|min:1',
            'status'     => 'nullable|in:active,inactive,suspended',
        ], [
            'first_name' => 'First name',
            'last_name'  => 'Last name',
            'email'      => 'Email address',
            'password'   => 'Password',
            'role_id'    => 'Role',
        ]);

        $email = strtolower(trim((string) $data['email']));

        if ($this->users->emailExists($email)) {
            throw HttpException::conflict('An account with that email address already exists.');
        }

        $role = $this->assertAssignableRole((int) $data['role_id']);

        $id = $this->users->create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $email,
            'password_hash' => password_hash((string) $data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role_id'       => (int) $data['role_id'],
            'status'        => $data['status'] ?? 'active',
        ]);

        AuditLogger::record(
            'created',
            'user',
            $id,
            "Created user \"{$data['first_name']} {$data['last_name']}\" ({$email}) with role {$role['name']}"
        );

        Response::created($this->users->findOrFail($id));
    }

    public function update(Request $request, array $args): never
    {
        $id     = $this->idFrom($args);
        $before = $this->users->findOrFail($id, true);

        $this->assertMayManage($before);

        $data = Validator::make($request->body())->validate([
            'first_name' => 'nullable|string|min:1|max:80',
            'last_name'  => 'nullable|string|min:1|max:80',
            'email'      => 'nullable|email|max:190',
            'password'   => 'nullable|string|min:10|max:200',
            'role_id'    => 'nullable|int|min:1',
            'status'     => 'nullable|in:active,inactive,suspended',
        ], [
            'first_name' => 'First name',
            'last_name'  => 'Last name',
            'email'      => 'Email address',
            'password'   => 'Password',
            'role_id'    => 'Role',
        ]);

        $update = [];

        foreach (['first_name', 'last_name'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $update[$field] = $data[$field];
            }
        }

        if (!empty($data['email'])) {
            $email = strtolower(trim((string) $data['email']));
            if ($this->users->emailExists($email, $id)) {
                throw HttpException::conflict('Another account already uses that email address.');
            }
            $update['email'] = $email;
        }

        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash((string) $data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (!empty($data['role_id']) && (int) $data['role_id'] !== (int) $before['role_id']) {
            $this->assertAssignableRole((int) $data['role_id']);

            // Demoting the last Super Admin would lock everyone out of role management.
            if ($before['role']['slug'] === 'super-admin'
                && $this->users->activeSuperAdminCount($id) === 0) {
                throw HttpException::conflict(
                    'This is the only active Super Admin. Promote another account before changing this role.'
                );
            }

            $update['role_id'] = (int) $data['role_id'];
        }

        if (!empty($data['status']) && $data['status'] !== $before['status']) {
            if ($id === Auth::id() && $data['status'] !== 'active') {
                throw HttpException::conflict('You cannot deactivate your own account.');
            }

            if ($data['status'] !== 'active'
                && $before['role']['slug'] === 'super-admin'
                && $this->users->activeSuperAdminCount($id) === 0) {
                throw HttpException::conflict(
                    'This is the only active Super Admin. Activate another before deactivating this one.'
                );
            }

            $update['status'] = $data['status'];
        }

        if ($update !== []) {
            $this->users->update($id, $update);
        }

        $after = $this->users->findOrFail($id, true);

        $metadata = AuditLogger::diff($before, $after, ['role', 'full_name']);
        if (isset($update['password_hash'])) {
            $metadata['password'] = ['from' => '********', 'to' => '********'];
        }

        AuditLogger::record(
            isset($update['role_id']) ? 'role_changed' : 'updated',
            'user',
            $id,
            isset($update['role_id'])
                ? "Changed role for \"{$after['full_name']}\" to {$after['role']['name']}"
                : "Updated user \"{$after['full_name']}\"",
            $metadata
        );

        Response::json($after);
    }

    public function destroy(Request $request, array $args): never
    {
        $id  = $this->idFrom($args);
        $row = $this->users->findOrFail($id);

        $this->assertMayManage($row);

        if ($id === Auth::id()) {
            throw HttpException::conflict('You cannot delete your own account.');
        }

        if ($row['role']['slug'] === 'super-admin' && $this->users->activeSuperAdminCount($id) === 0) {
            throw HttpException::conflict(
                'This is the only active Super Admin and cannot be deleted.'
            );
        }

        $this->users->softDelete($id);

        AuditLogger::record('deleted', 'user', $id, "Deleted user \"{$row['full_name']}\" ({$row['email']})");

        Response::json([
            'id'      => $id,
            'deleted' => true,
            'message' => "User \"{$row['full_name']}\" was deleted and can be restored.",
        ]);
    }

    public function restore(Request $request, array $args): never
    {
        $id  = $this->idFrom($args);
        $row = $this->users->findOrFail($id, true);

        $this->assertMayManage($row);

        if ($row['deleted_at'] === null) {
            throw HttpException::conflict('That user is not deleted.');
        }

        // The address may have been reused while this account sat in the bin.
        if ($this->users->emailExists($row['email'], $id)) {
            throw HttpException::conflict(
                'Another active account now uses that email address. Change it before restoring.'
            );
        }

        $this->users->restore($id);

        AuditLogger::record('restored', 'user', $id, "Restored user \"{$row['full_name']}\"");

        Response::json($this->users->findOrFail($id));
    }

    /** Roles the current user is permitted to assign. */
    public function assignableRoles(Request $request): never
    {
        $roles = $this->roles->options();

        if (!Auth::isSuperAdmin()) {
            $roles = array_values(array_filter(
                $roles,
                static fn (array $r): bool => $r['slug'] !== 'super-admin'
            ));
        }

        Response::json($roles);
    }

    // -----------------------------------------------------------------

    /** Rejects any attempt by a non-Super-Admin to touch a Super Admin account. */
    private function assertMayManage(array $target): void
    {
        if (($target['role']['slug'] ?? null) === 'super-admin' && !Auth::isSuperAdmin()) {
            throw HttpException::forbidden(
                'Only a Super Admin can manage another Super Admin account.'
            );
        }
    }

    /** Rejects any attempt by a non-Super-Admin to hand out the Super Admin role. */
    private function assertAssignableRole(int $roleId): array
    {
        $role = Database::fetchOne('SELECT id, name, slug FROM roles WHERE id = ?', [$roleId]);

        if ($role === null) {
            throw HttpException::validation(['role_id' => 'Please choose a valid role.']);
        }

        if ($role['slug'] === 'super-admin' && !Auth::isSuperAdmin()) {
            throw HttpException::forbidden('Only a Super Admin can assign the Super Admin role.');
        }

        return $role;
    }

    private function idFrom(array $args): int
    {
        $id = $args['id'] ?? '';

        if (!is_string($id) || !preg_match('/^\d+$/', $id) || (int) $id < 1) {
            throw HttpException::badRequest('Invalid user id.');
        }

        return (int) $id;
    }
}
