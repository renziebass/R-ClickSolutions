<?php
declare(strict_types=1);

namespace Mariah\Services;

/**
 * Derives the real-world state of a dated record from its stored status plus
 * its date window. Staff never type "expired" — the dates decide.
 *
 *   stored     window                effective
 *   ---------- --------------------- -----------
 *   draft      any                   draft
 *   archived   any                   inactive
 *   published  start_date > today    scheduled
 *   published  inside window         active
 *   published  end_date < today      expired
 */
final class ScheduleResolver
{
    public const DRAFT     = 'draft';
    public const SCHEDULED = 'scheduled';
    public const ACTIVE    = 'active';
    public const EXPIRED   = 'expired';
    public const INACTIVE  = 'inactive';

    public static function resolve(?string $status, ?string $startDate, ?string $endDate): string
    {
        if ($status === 'draft') {
            return self::DRAFT;
        }
        if ($status === 'archived' || $status === 'inactive') {
            return self::INACTIVE;
        }

        $today = date('Y-m-d');

        if ($startDate !== null && $startDate !== '' && substr($startDate, 0, 10) > $today) {
            return self::SCHEDULED;
        }
        if ($endDate !== null && $endDate !== '' && substr($endDate, 0, 10) < $today) {
            return self::EXPIRED;
        }

        return self::ACTIVE;
    }

    /** Adds `effective_status` to a row. */
    public static function decorate(array $row): array
    {
        $row['effective_status'] = self::resolve(
            $row['status'] ?? null,
            $row['start_date'] ?? null,
            $row['end_date'] ?? null
        );
        return $row;
    }

    /** @param array<int, array> $rows */
    public static function decorateMany(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * SQL fragment selecting only rows that are live right now. Used by the
     * public endpoints so unpublished, scheduled and expired records never
     * reach the website.
     *
     * @param string $alias table alias, e.g. "p"
     */
    public static function publicWhere(string $alias): string
    {
        // Alias comes from call sites in this codebase, never from user input.
        return "{$alias}.status = 'published'
                AND {$alias}.deleted_at IS NULL
                AND ({$alias}.start_date IS NULL OR {$alias}.start_date <= CURDATE())
                AND ({$alias}.end_date   IS NULL OR {$alias}.end_date   >= CURDATE())";
    }

    /** Human-readable label for the admin UI. */
    public static function label(string $effective): string
    {
        return match ($effective) {
            self::DRAFT     => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::ACTIVE    => 'Active',
            self::EXPIRED   => 'Expired',
            self::INACTIVE  => 'Inactive',
            default         => ucfirst($effective),
        };
    }
}
