<?php

declare(strict_types=1);

namespace Zcc\Core\Http;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$request->method][$request->path] ?? null;

        if (!$handler) {
            return new Response('Not Found', 404);
        }

        return $handler($request);
    }
}
