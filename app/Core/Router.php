<?php

declare(strict_types=1);

namespace App\Core;

/**
 * URL Router
 *
 * Handles URL routing with support for:
 * - Static routes
 * - Dynamic parameters (e.g., /image/{slug})
 * - Route groups with prefixes and middleware
 * - Named routes for URL generation
 */
class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private array $groupStack = [];

    /**
     * Add a GET route
     */
    public function get(string $path, string|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, string|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add a PUT route
     */
    public function put(string $path, string|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Add a DELETE route
     */
    public function delete(string $path, string|array $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Add a PATCH route
     */
    public function patch(string $path, string|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    /**
     * Add a route for any HTTP method
     */
    public function any(string $path, string|array $handler, array $middleware = []): self
    {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $this->addRoute($method, $path, $handler, $middleware);
        }
        return $this;
    }

    /**
     * Add a route for multiple HTTP methods
     */
    public function methods(array $methods, string $path, string|array $handler, array $middleware = []): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler, $middleware);
        }
        return $this;
    }

    /**
     * Create a route group
     */
    public function group(array $attributes, callable $callback): self
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    /**
     * Name the last added route
     */
    public function name(string $name): self
    {
        // Get the last route added
        foreach ($this->routes as $method => $routes) {
            if (!empty($routes)) {
                $lastPath = array_key_last($routes);
                $this->namedRoutes[$name] = [
                    'method' => $method,
                    'path' => $lastPath,
                ];
                break;
            }
        }
        return $this;
    }

    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, string|array $handler, array $middleware = []): self
    {
        // Apply group attributes
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddleware = array_merge(
                    $groupMiddleware,
                    (array) $group['middleware']
                );
            }
        }

        // Build full path
        $fullPath = $prefix . '/' . ltrim($path, '/');
        $fullPath = $fullPath === '' ? '/' : rtrim($fullPath, '/');
        if ($fullPath !== '/') {
            $fullPath = '/' . ltrim($fullPath, '/');
        }

        // Merge middleware
        $allMiddleware = array_merge($groupMiddleware, $middleware);

        // Parse handler
        if (is_string($handler)) {
            [$controller, $action] = explode('@', $handler);
        } else {
            [$controller, $action] = $handler;
        }

        // Apply namespace from group
        foreach ($this->groupStack as $group) {
            if (isset($group['namespace']) && !str_starts_with($controller, '\\')) {
                $controller = $group['namespace'] . '\\' . $controller;
            }
        }

        // Store route
        $this->routes[$method][$fullPath] = [
            'controller' => $controller,
            'action' => $action,
            'middleware' => $allMiddleware,
        ];

        return $this;
    }

    /**
     * Match a request to a route
     */
    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method])) {
            return null;
        }

        // Normalize path
        $path = $path === '' ? '/' : rtrim($path, '/');
        if ($path !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        // Try exact match first
        if (isset($this->routes[$method][$path])) {
            return array_merge($this->routes[$method][$path], ['params' => []]);
        }

        // Try pattern matching
        foreach ($this->routes[$method] as $routePath => $route) {
            $params = $this->matchPattern($routePath, $path);
            if ($params !== null) {
                return array_merge($route, ['params' => $params]);
            }
        }

        return null;
    }

    /**
     * Match path against a route pattern
     */
    private function matchPattern(string $pattern, string $path): ?array
    {
        // Convert route pattern to regex
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $pattern);

        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            // Extract only named parameters
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = $value;
                }
            }
            return $params;
        }

        return null;
    }

    /**
     * Generate URL for a named route
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route not found: {$name}");
        }

        $path = $this->namedRoutes[$name]['path'];

        // Replace parameters
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string) $value, $path);
        }

        return url($path);
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Load routes from file
     */
    public function loadRoutes(string $file): void
    {
        if (file_exists($file)) {
            $router = $this;
            require $file;
        }
    }
}
