<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, string $action, array $middleware = []): void
    {
        $this->routes[] = ['GET', $uri, $action, $middleware];
    }

    public function post(string $uri, string $action, array $middleware = []): void
    {
        $this->routes[] = ['POST', $uri, $action, $middleware];
    }

    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $routeUri, $action, $middleware]) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routeUri);
            $pattern = '#^' . $pattern . '$#';

            if ($requestMethod !== $routeMethod || !preg_match($pattern, $uri, $matches)) {
                continue;
            }

            foreach ($middleware as $m) {
                Middleware::handle($m);
            }

            [$controller, $actionMethod] = explode('@', $action);
            $controller = 'App\\Controllers\\' . $controller;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            $instance = new $controller();
            $instance->$actionMethod(...$params);
            return;
        }

        http_response_code(404);
        View::render('errors/404', [], 'main');
        exit;
    }
}
