<?php
declare(strict_types=1);

namespace Mariah\Core;

use DateTimeImmutable;
use DateTimeZone;
use Mariah\Repositories\SettingsRepository;

/**
 * The one place that knows what timezone the CMS runs in.
 *
 * Every timestamp column in this schema is DATETIME, never TIMESTAMP, so a
 * stored value is bare wall-clock text with no zone attached — whichever clock
 * wrote it. Two clocks write into those columns: PHP (date(), the repositories'
 * explicit values) and MySQL (NOW(), DEFAULT CURRENT_TIMESTAMP — which is where
 * audit_logs.created_at comes from). If the two disagree, values written by one
 * are compared against cutoffs computed by the other and the results are wrong
 * by the offset between them. This class exists to make them agree.
 *
 * RESOLUTION ORDER
 *   the site_timezone setting → APP_TIMEZONE in .env → FALLBACK
 *
 * The setting is authoritative but lives in the database, which is not reachable
 * while config/bootstrap.php runs. So resolution happens in two steps:
 * envTimezone() answers without touching the database and is what Database::pdo()
 * uses at connect time, then boot() refines both clocks from the setting once a
 * connection exists. Nothing is ever left on an unknown zone in between.
 */
final class Clock
{
    /** Fort Lauderdale, FL — the spa's own zone, and the last resort. */
    public const FALLBACK = 'America/New_York';

    private static ?string $zone = null;

    public static function isValid(string $zone): bool
    {
        return $zone !== '' && in_array($zone, timezone_identifiers_list(), true);
    }

    /**
     * The configured zone, without touching the database. Used at connect time
     * and whenever the settings table is unreachable.
     */
    public static function envTimezone(): string
    {
        $zone = trim(Env::string('APP_TIMEZONE', ''));

        return self::isValid($zone) ? $zone : self::FALLBACK;
    }

    /**
     * The authoritative zone. Reads the setting once per request and falls back
     * to envTimezone() if the settings table cannot be read — a pre-install
     * visit, an unmigrated database, or a CLI script with no database at all.
     */
    public static function timezone(): string
    {
        if (self::$zone !== null) {
            return self::$zone;
        }

        try {
            $stored = trim((string) (SettingsRepository::get('site_timezone') ?? ''));
        } catch (\Throwable $e) {
            $stored = '';
        }

        return self::$zone = self::isValid($stored) ? $stored : self::envTimezone();
    }

    /**
     * The zone as a numeric UTC offset, "-04:00" style.
     *
     * MySQL is set by offset rather than by name deliberately: shared hosting
     * rarely loads the named-timezone tables, so `SET time_zone =
     * 'America/New_York'` fails there with "Unknown or incorrect time zone"
     * while an offset always works. Resolved per connection, so the offset is
     * the correct one for today's DST state.
     */
    public static function utcOffset(?string $zone = null): string
    {
        $zone ??= self::timezone();

        try {
            return (new DateTimeImmutable('now', new DateTimeZone($zone)))->format('P');
        } catch (\Throwable $e) {
            return (new DateTimeImmutable('now', new DateTimeZone(self::FALLBACK)))->format('P');
        }
    }

    /**
     * Points PHP and the current MySQL session at the configured zone.
     *
     * Called once per request from api/index.php, and by the CLI entrypoints.
     * Never throws: a failure here must not take down a request, and the worst
     * case is that both clocks stay on the connect-time zone, which is still a
     * zone they agree on.
     */
    public static function boot(): void
    {
        $zone = self::timezone();

        date_default_timezone_set($zone);

        try {
            self::applyTo(Database::pdo(), $zone);
        } catch (\Throwable $e) {
            Logger::error($e, ['hint' => 'setting the MySQL session time_zone']);
        }
    }

    /**
     * Sets the session time_zone on an open connection.
     *
     * Takes the PDO rather than calling Database::pdo() itself, because
     * Database::pdo() calls this from inside its own connect path — passing the
     * instance keeps that from being a reentrant call.
     */
    public static function applyTo(\PDO $pdo, string $zone): void
    {
        // The offset comes from DateTimeZone, never from user input, so it can
        // only ever be "+HH:MM" or "-HH:MM". Inlined rather than bound because
        // SET does not accept a placeholder for its value on every MySQL build.
        $pdo->exec("SET time_zone = '" . self::utcOffset($zone) . "'");
    }

    /** Drops the resolved zone so the next call re-reads the setting. For tests. */
    public static function forget(): void
    {
        self::$zone = null;
    }
}
