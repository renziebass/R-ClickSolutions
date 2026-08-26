<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Auth;
use Mariah\Core\Csrf;
use Mariah\Core\HttpException;
use Mariah\Core\RateLimiter;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Core\Logger;
use Mariah\Core\Validator;
use Mariah\Repositories\SettingsRepository;
use Mariah\Repositories\UserRepository;
use Mariah\Services\AuditLogger;

final class AuthController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function login(Request $request): never
    {
        $data = Validator::make($request->body())->validate([
            'email'    => 'required|email|max:190',
            'password' => 'required|string|max:200',
        ], [
            'email'    => 'Email address',
            'password' => 'Password',
        ]);

        $email = strtolower(trim((string) $data['email']));
        $ip    = $request->ip();

        RateLimiter::assertLoginAllowed($email, $ip);

        $user = $this->users->findForAuthentication($email);

        // Always run a hash comparison so a missing account and a wrong password
        // take the same time — the response must not reveal which it was.
        $hash  = $user['password_hash'] ?? '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidin';
        $valid = password_verify((string) $data['password'], $hash);

        if ($user === null || !$valid) {
            RateLimiter::record($email, $ip, false);
            AuditLogger::recordAuth('login_failed', $user['id'] ?? null, $email, "Failed sign-in attempt for {$email}");

            throw HttpException::unauthorized('The email address or password is incorrect.');
        }

        if ($user['status'] !== 'active') {
            RateLimiter::record($email, $ip, false);
            AuditLogger::recordAuth(
                'login_blocked',
                (int) $user['id'],
                $email,
                "Sign-in blocked: account is {$user['status']}"
            );

            throw HttpException::forbidden(
                'This account is not active. Please contact a Super Admin.'
            );
        }

        // Re-hash if the cost factor has since been raised.
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->users->updatePassword(
                (int) $user['id'],
                password_hash((string) $data['password'], PASSWORD_BCRYPT, ['cost' => 12])
            );
        }

        RateLimiter::record($email, $ip, true);
        Auth::login($user);
        $this->users->recordLogin((int) $user['id'], $ip);

        AuditLogger::recordAuth(
            'login',
            (int) $user['id'],
            trim($user['first_name'] . ' ' . $user['last_name']),
            'Signed in to the admin dashboard'
        );

        Response::json([
            'user'       => Auth::publicProfile(),
            'csrf_token' => Csrf::token(),
        ]);
    }

    public function logout(Request $request): never
    {
        $user = Auth::user();

        if ($user !== null) {
            AuditLogger::recordAuth(
                'logout',
                $user['id'],
                trim($user['first_name'] . ' ' . $user['last_name']),
                'Signed out of the admin dashboard'
            );
        }

        Auth::logout();

        Response::json(['message' => 'You have been signed out.']);
    }

    /** Current session identity — the SPA calls this on every page load. */
    public function me(Request $request): never
    {
        $profile = Auth::publicProfile();

        if ($profile === null) {
            throw HttpException::unauthorized();
        }

        // Client-visible settings ride along here rather than costing the SPA a
        // second request. Wrapped because session.load() is the first thing the
        // dashboard awaits: if this threw — say because migration 009 has not
        // been run yet — the entire admin would fail to boot behind a 500.
        $config = [];

        try {
            $config = SettingsRepository::publicValues();
        } catch (\Throwable $e) {
            Logger::error($e, ['hint' => 'settings read for /auth/me']);
        }

        Response::json([
            'user'       => $profile,
            'csrf_token' => Csrf::token(),
            'config'     => $config,
        ]);
    }

    /** Issues a CSRF token for the login form, before any session exists. */
    public function csrf(Request $request): never
    {
        Response::json(['csrf_token' => Csrf::token()]);
    }

    /** Self-service password change for the signed-in user. */
    public function changePassword(Request $request): never
    {
        $user = Auth::user();
        if ($user === null) {
            throw HttpException::unauthorized();
        }

        $data = Validator::make($request->body())->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:10|max:200',
        ], [
            'current_password' => 'Current password',
            'new_password'     => 'New password',
        ]);

        $record = $this->users->findForAuthentication($user['email']);

        if ($record === null || !password_verify((string) $data['current_password'], $record['password_hash'])) {
            throw HttpException::validation(['current_password' => 'That is not your current password.']);
        }

        $this->users->updatePassword(
            $user['id'],
            password_hash((string) $data['new_password'], PASSWORD_BCRYPT, ['cost' => 12])
        );

        AuditLogger::record('password_changed', 'user', $user['id'], 'Changed their own password');

        Response::json(['message' => 'Your password has been updated.']);
    }
}
