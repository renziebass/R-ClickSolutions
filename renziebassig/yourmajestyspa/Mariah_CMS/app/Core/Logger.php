<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Technical errors go to a daily file for developers. They are never returned
 * to the client — the client only ever sees the generic message on a 500.
 */
final class Logger
{
    public static function error(\Throwable $e, array $context = []): string
    {
        $ref = bin2hex(random_bytes(6));

        $line = sprintf(
            "[%s] ref=%s %s: %s in %s:%d\n%s\ncontext=%s\n%s\n",
            date('Y-m-d H:i:s'),
            $ref,
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            json_encode($context, JSON_UNESCAPED_SLASHES),
            str_repeat('-', 70)
        );

        self::write($line);
        return $ref;
    }

    public static function warn(string $message, array $context = []): void
    {
        self::write(sprintf(
            "[%s] WARN %s context=%s\n",
            date('Y-m-d H:i:s'),
            $message,
            json_encode($context, JSON_UNESCAPED_SLASHES)
        ));
    }

    private static function write(string $line): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
