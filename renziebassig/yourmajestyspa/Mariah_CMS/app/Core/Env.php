<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Minimal .env loader. Values live in process memory only and are never
 * echoed to the browser.
 */
final class Env
{
    private static array $vars = [];
    private static bool $loaded = false;

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        if (!is_readable($path)) {
            throw new \RuntimeException(
                'Missing .env file. Copy .env.example to .env and fill it in.'
            );
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip matching surrounding quotes.
            $len = strlen($value);
            if ($len >= 2
                && (($value[0] === '"' && $value[$len - 1] === '"')
                 || ($value[0] === "'" && $value[$len - 1] === "'"))) {
                $value = substr($value, 1, -1);
            }

            self::$vars[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$vars[$key] ?? $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $v = self::$vars[$key] ?? '';
        return $v === '' ? $default : (string) $v;
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = self::$vars[$key] ?? '';
        return $v === '' ? $default : (int) $v;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::$vars[$key] ?? null;
        if ($v === null || $v === '') {
            return $default;
        }
        return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'on'], true);
    }

    /** Throws if a required value is absent — fail loudly at boot, not mid-request. */
    public static function require(string $key): string
    {
        $v = self::string($key);
        if ($v === '') {
            throw new \RuntimeException("Required environment variable {$key} is not set in .env");
        }
        return $v;
    }
}
