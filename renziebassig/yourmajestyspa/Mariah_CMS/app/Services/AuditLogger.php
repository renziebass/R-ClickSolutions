<?php
declare(strict_types=1);

namespace Mariah\Services;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\Logger;

/**
 * Append-only administrative activity trail.
 *
 * Audit writes must never break the operation they describe: a failure here is
 * logged for developers and swallowed, because losing the audit row is far less
 * damaging than rolling back a successful content change.
 */
final class AuditLogger
{
    public static function record(
        string $action,
        string $entityType,
        ?int $entityId,
        string $description,
        array $metadata = []
    ): void {
        try {
            $user = Auth::user();

            Database::run(
                'INSERT INTO audit_logs
                    (user_id, user_label, action, entity_type, entity_id,
                     description, metadata, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $user['id'] ?? null,
                    $user === null ? null : trim($user['first_name'] . ' ' . $user['last_name']),
                    $action,
                    $entityType,
                    $entityId,
                    mb_substr($description, 0, 500),
                    $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_SLASHES),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                ]
            );
        } catch (\Throwable $e) {
            Logger::error($e, ['audit_action' => $action, 'entity' => $entityType]);
        }
    }

    /** Login/logout events, recorded before or without a resolved session user. */
    public static function recordAuth(
        string $action,
        ?int $userId,
        ?string $userLabel,
        string $description
    ): void {
        try {
            Database::run(
                'INSERT INTO audit_logs
                    (user_id, user_label, action, entity_type, entity_id,
                     description, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $userId,
                    $userLabel,
                    $action,
                    'auth',
                    $userId,
                    mb_substr($description, 0, 500),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                ]
            );
        } catch (\Throwable $e) {
            Logger::error($e, ['audit_action' => $action]);
        }
    }

    /**
     * Diff of user-visible fields, so the log says what actually changed rather
     * than dumping the whole row.
     */
    public static function diff(array $before, array $after, array $ignore = []): array
    {
        $ignore  = array_merge($ignore, ['updated_at', 'created_at', 'updated_by', 'created_by', 'password_hash']);
        $changes = [];

        foreach ($after as $key => $newValue) {
            if (in_array($key, $ignore, true) || !array_key_exists($key, $before)) {
                continue;
            }
            $oldValue = $before[$key];

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            $changes[$key] = [
                'from' => is_scalar($oldValue) || $oldValue === null ? $oldValue : '…',
                'to'   => is_scalar($newValue) || $newValue === null ? $newValue : '…',
            ];
        }

        return $changes;
    }
}
