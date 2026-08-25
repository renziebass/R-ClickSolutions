<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Tiny pattern router. Routes are registered as literal segments plus
 * {name} placeholders, e.g. "/services/{id}/status".
 */
final class Router
{
    /** @var array<string, array<int, array{regex:string, params:string[], handler:callable, guards:array}>> */
    private array $routes = [];

    public function get(string $p, callable $h, array $guards = []): void    { $this->add('GET', $p, $h, $guards); }
    public function post(string $p, callable $h, array $guards = []): void   { $this->add('POST', $p, $h, $guards); }
    public function put(string $p, callable $h, array $guards = []): void    { $this->add('PUT', $p, $h, $guards); }
    public function patch(string $p, callable $h, array $guards = []): void  { $this->add('PATCH', $p, $h, $guards); }
    public function delete(string $p, callable $h, array $guards = []): void { $this->add('DELETE', $p, $h, $guards); }

    private function add(string $method, string $pattern, callable $handler, array $guards): void
    {
        $params = [];
        $regex  = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            $pattern
        );

        $this->routes[$method][] = [
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
            'guards'  => $guards,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['regex'], $path, $m)) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $i => $name) {
                $args[$name] = $m[$i + 1];
            }

            foreach ($route['guards'] as $guard) {
                $guard($request);
            }

            ($route['handler'])($request, $args);
            return; // handlers always exit via Response, but be explicit.
        }

        // Path exists under another verb → 405 is more useful than 404.
        foreach ($this->routes as $verb => $routes) {
            if ($verb === $method) {
                continue;
            }
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path)) {
                    Response::error(405, 'METHOD_NOT_ALLOWED', "{$method} is not supported for this endpoint.");
                }
            }
        }

        throw HttpException::notFound("No API endpoint matches {$method} {$path}.");
    }
}
