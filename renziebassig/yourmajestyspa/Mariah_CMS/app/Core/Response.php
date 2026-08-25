<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Every API response uses one envelope so the SPA has a single parse path.
 *   success: { "success": true,  "data": …, "meta": {…}? }
 *   failure: { "success": false, "error": { "code", "message", "fields" } }
 */
final class Response
{
    public static function json(mixed $data, int $status = 200, ?array $meta = null): never
    {
        $payload = ['success' => true, 'data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        self::emit($payload, $status);
    }

    public static function created(mixed $data): never
    {
        self::json($data, 201);
    }

    public static function noContent(): never
    {
        self::json(null, 200);
    }

    public static function error(int $status, string $code, string $message, array $fields = []): never
    {
        self::emit([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
                'fields'  => (object) $fields,
            ],
        ], $status);
    }

    private static function emit(array $payload, int $status): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            // Admin data must never sit in a shared or browser cache.
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        echo json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }
}
