<?php
/**
 * A small routing table. Patterns use {name} for a segment and {name:\d+} for a typed one.
 */
declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $p, callable|array $h): void { $this->add('GET', $p, $h); }
    public function post(string $p, callable|array $h): void { $this->add('POST', $p, $h); }
    public function any(string $p, callable|array $h): void { $this->add('GET', $p, $h); $this->add('POST', $p, $h); }

    private function add(string $method, string $pattern, callable|array $handler): void
    {
        $regex = preg_replace_callback(
            '~\{([a-z_]+)(?::([^}]+))?\}~i',
            static fn(array $m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            rtrim($pattern, '/') ?: '/'
        );
        $this->routes[] = [$method, '~^' . $regex . '$~', $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        $path = rtrim(parse_url($path, PHP_URL_PATH) ?: '/', '/') ?: '/';
        foreach ($this->routes as [$m, $re, $h]) {
            if ($m !== $method || !preg_match($re, $path, $hit)) {
                continue;
            }
            $args = array_filter($hit, 'is_string', ARRAY_FILTER_USE_KEY);
            if (is_array($h)) {
                [$class, $fn] = $h;
                (new $class())->$fn(...array_values($args));
            } else {
                $h(...array_values($args));
            }
            return;
        }
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found']);
    }
}
