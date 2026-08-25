<?php
declare(strict_types=1);

namespace Mariah\Core;

final class Slug
{
    public static function make(string $text): string
    {
        $text = trim($text);

        // Normalise common typographic characters the spa copy uses.
        $text = str_replace(['’', '‘', '“', '”', '&', '–', '—'], ['', '', '', '', ' and ', '-', '-'], $text);

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');

        return $text === '' ? 'item-' . substr(bin2hex(random_bytes(4)), 0, 6) : substr($text, 0, 190);
    }

    /**
     * Returns a slug unique within $table, ignoring $ignoreId (for updates).
     * Soft-deleted rows still hold their slug, so uniqueness spans them too.
     */
    public static function unique(string $table, string $text, ?int $ignoreId = null): string
    {
        static $allowed = [
            'services', 'service_categories', 'promotions', 'specials',
            'products', 'product_categories', 'product_brands', 'gift_cards', 'roles',
        ];

        if (!in_array($table, $allowed, true)) {
            throw new \InvalidArgumentException("Slug uniqueness is not configured for table {$table}.");
        }

        $base = self::make($text);
        $slug = $base;
        $n    = 2;

        while (self::exists($table, $slug, $ignoreId)) {
            $slug = $base . '-' . $n;
            $n++;
            if ($n > 500) {
                $slug = $base . '-' . substr(bin2hex(random_bytes(3)), 0, 5);
                break;
            }
        }

        return $slug;
    }

    private static function exists(string $table, string $slug, ?int $ignoreId): bool
    {
        // $table is validated against the allowlist above; never raw input.
        $sql    = "SELECT 1 FROM `{$table}` WHERE slug = ?";
        $params = [$slug];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $ignoreId;
        }

        return Database::fetchValue($sql . ' LIMIT 1', $params) !== null;
    }
}
