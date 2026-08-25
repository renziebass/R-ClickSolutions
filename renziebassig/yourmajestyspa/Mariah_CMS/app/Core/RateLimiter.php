<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Login throttling backed by the login_attempts table. Counts failures for the
 * email/IP pair inside the lockout window; a successful login clears them.
 */
final class RateLimiter
{
    public static function assertLoginAllowed(string $email, string $ip): void
    {
        $max    = Env::int('LOGIN_MAX_ATTEMPTS', 5);
        $window = Env::int('LOGIN_LOCKOUT_SECONDS', 900);
        $since  = date('Y-m-d H:i:s', time() - $window);

        $failures = (int) Database::fetchValue(
            'SELECT COUNT(*) FROM login_attempts
              WHERE successful = 0 AND attempted_at > ?
                AND (email = ? OR ip_address = ?)',
            [$since, $email, $ip]
        );

        if ($failures >= $max) {
            $minutes = max(1, (int) ceil($window / 60));
            throw HttpException::tooManyRequests(
                "Too many failed sign-in attempts. Please try again in {$minutes} minutes."
            );
        }
    }

    public static function record(string $email, string $ip, bool $successful): void
    {
        Database::run(
            'INSERT INTO login_attempts (email, ip_address, successful, attempted_at)
             VALUES (?, ?, ?, NOW())',
            [substr($email, 0, 190), $ip, $successful ? 1 : 0]
        );

        if ($successful) {
            Database::run(
                'DELETE FROM login_attempts WHERE successful = 0 AND (email = ? OR ip_address = ?)',
                [$email, $ip]
            );
        }

        // Opportunistic pruning keeps the table small without a cron job.
        if (random_int(1, 50) === 1) {
            Database::run('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 7 DAY)');
        }
    }
}
