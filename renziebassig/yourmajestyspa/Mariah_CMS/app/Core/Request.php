<?php
declare(strict_types=1);

namespace Mariah\Core;

final class Request
{
    private array $query;
    private array $body;
    private string $method;
    private string $path;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->query  = $_GET;
        $this->path   = $this->resolvePath();
        $this->body   = $this->resolveBody();
    }

    /**
     * Extracts the route path after ".../Mariah_CMS/api". Works whether the
     * request was rewritten by .htaccess or hit api/index.php directly.
     */
    private function resolvePath(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);

        // Strip everything up to and including the "/api" segment. The lookahead
        // means only a whole segment matches, so a slug such as "/apiary" or a
        // deploy path containing "apis" cannot be mistaken for the API root.
        $path = preg_match('#^(.*?)/api(?=/|$)(.*)$#s', $uri, $m) === 1 ? $m[2] : $uri;

        $path = preg_replace('#^/index\.php#', '', $path) ?? $path;
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function resolveBody(): array
    {
        if (in_array($this->method, ['GET', 'HEAD'], true)) {
            return [];
        }

        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');

        // multipart/form-data (uploads) and urlencoded forms land in $_POST.
        if (str_contains($contentType, 'multipart/form-data')
            || str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw HttpException::badRequest('Request body must be valid JSON.');
        }

        return $decoded;
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    public function body(): array { return $this->body; }
    public function query(): array { return $this->query; }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->body);
    }

    public function q(string $key, mixed $default = null): mixed
    {
        $v = $this->query[$key] ?? null;
        return ($v === null || $v === '') ? $default : $v;
    }

    public function qInt(string $key, int $default): int
    {
        $v = $this->q($key);
        return is_numeric($v) ? (int) $v : $default;
    }

    public function qBool(string $key): ?bool
    {
        $v = $this->q($key);
        if ($v === null) {
            return null;
        }
        return in_array(strtolower((string) $v), ['1', 'true', 'yes'], true);
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    public function ip(): string
    {
        // REMOTE_ADDR only. Forwarded-For headers are client-controlled and
        // would let an attacker sidestep login rate limiting.
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}
