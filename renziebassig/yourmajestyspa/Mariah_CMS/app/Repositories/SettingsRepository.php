<?php
declare(strict_types=1);

namespace Mariah\Repositories;

use Mariah\Core\Auth;
use Mariah\Core\Database;
use Mariah\Core\Logger;
use Mariah\Services\SettingsSchema;

/**
 * Reads and writes the `settings` table.
 *
 * Deliberately not a BaseRepository: there is no soft delete, no id-addressed
 * CRUD and no pagination here, and BaseRepository's authorship stamping would
 * cost a SHOW COLUMNS on a table with six columns.
 *
 * Stored rows are merged over the registry defaults, so a setting nobody has
 * touched still has a value and the table stays empty until someone saves.
 */
final class SettingsRepository
{
    /** Settings are read on most requests; resolve them once per request. */
    private static ?array $cache = null;

    /** @return array<string, mixed> every setting, coerced to its declared type */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $stored = [];

        try {
            $rows = Database::fetchAll('SELECT setting_key, setting_value FROM settings');

            foreach ($rows as $row) {
                $key = (string) $row['setting_key'];

                // A key left over from a removed setting is ignored rather
                // than surfacing as a phantom entry.
                if (SettingsSchema::has($key)) {
                    $stored[$key] = SettingsSchema::coerce($key, $row['setting_value']);
                }
            }
        } catch (\PDOException $e) {
            // 1146 = table missing, i.e. migration 009 has not been run yet.
            // /auth/me reads settings and the whole admin SPA boots through it,
            // so this must degrade to defaults rather than take the dashboard
            // down behind a generic 500. Anything else is a real fault.
            if (($e->errorInfo[1] ?? null) !== 1146) {
                throw $e;
            }

            Logger::error($e, ['hint' => 'settings table missing — run php database/migrate.php']);
        }

        return self::$cache = array_merge(SettingsSchema::defaults(), $stored);
    }

    public static function get(string $key): mixed
    {
        return self::all()[$key] ?? null;
    }

    public static function string(string $key): string
    {
        return (string) (self::get($key) ?? '');
    }

    public static function bool(string $key): bool
    {
        return (bool) self::get($key);
    }

    /**
     * The values the browser is allowed to see, plus flags derived from the
     * server's own capabilities.
     */
    public static function publicValues(): array
    {
        $all    = self::all();
        $public = [];

        foreach (SettingsSchema::publicKeys() as $key) {
            $public[$key] = $all[$key] ?? null;
        }

        // The server knows whether importing from a link can actually work;
        // the client should never render a control that cannot.
        $public['services_import_url_available'] =
            (bool) ($all['services_import_url_enabled'] ?? false) && extension_loaded('curl');

        return $public;
    }

    /**
     * Upserts the given settings in one transaction.
     *
     * @param  array<string, mixed> $values already validated and coerced
     * @return array<string, array{from: mixed, to: mixed}> what actually changed
     */
    public static function put(array $values): array
    {
        $before  = self::all();
        $changes = [];

        Database::transaction(static function () use ($values, $before, &$changes): void {
            foreach ($values as $key => $value) {
                if (!SettingsSchema::has($key)) {
                    continue;
                }

                $coerced = SettingsSchema::coerce($key, SettingsSchema::serialise($key, $value));

                if (($before[$key] ?? null) === $coerced) {
                    continue;   // unchanged; do not touch updated_at
                }

                Database::run(
                    'INSERT INTO settings (setting_key, setting_value, updated_by)
                     VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value),
                        updated_by    = VALUES(updated_by)',
                    [$key, SettingsSchema::serialise($key, $value), Auth::id()]
                );

                $changes[$key] = ['from' => $before[$key] ?? null, 'to' => $coerced];
            }
        });

        self::$cache = null;

        return $changes;
    }

    /** Only for tests, which change settings and then read them back. */
    public static function forget(): void
    {
        self::$cache = null;
    }
}
