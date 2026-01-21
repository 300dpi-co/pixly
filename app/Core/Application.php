<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Main Application Class
 *
 * Central orchestrator for the application. Handles routing,
 * request/response cycle, and provides access to core services.
 */
class Application
{
    private static ?Application $instance = null;

    private array $config;
    private Router $router;
    private Request $request;
    private ?Database $database = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->request = new Request();
        $this->router = new Router();

        self::$instance = $this;
    }

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Application not initialized');
        }
        return self::$instance;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            // Get the matched route
            $route = $this->router->match(
                $this->request->getMethod(),
                $this->request->getPath()
            );

            if ($route === null) {
                $this->handleNotFound();
                return;
            }

            // Execute middleware stack
            $response = $this->executeMiddleware($route['middleware'], function () use ($route) {
                return $this->executeController($route);
            });

            // Send response
            if ($response instanceof Response) {
                $response->send();
            }
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * Execute the controller action
     */
    private function executeController(array $route): Response
    {
        $controller = $route['controller'];
        $action = $route['action'];
        $params = $route['params'];

        // Instantiate controller
        if (!class_exists($controller)) {
            throw new \RuntimeException("Controller not found: {$controller}");
        }

        $controllerInstance = new $controller($this->request);

        if (!method_exists($controllerInstance, $action)) {
            throw new \RuntimeException("Action not found: {$controller}::{$action}");
        }

        // Call the action with route parameters
        $result = $controllerInstance->$action(...$params);

        // Convert string response to Response object
        if (is_string($result)) {
            return new Response($result);
        }

        if ($result instanceof Response) {
            return $result;
        }

        // Default empty response
        return new Response();
    }

    /**
     * Execute middleware stack
     */
    private function executeMiddleware(array $middleware, callable $next): Response
    {
        if (empty($middleware)) {
            return $next();
        }

        $middlewareClass = array_shift($middleware);

        // Map middleware aliases to classes
        $middlewareMap = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'guest' => \App\Middleware\GuestMiddleware::class,
            'admin' => \App\Middleware\AdminMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
            'cache' => \App\Middleware\CacheMiddleware::class,
        ];

        $class = $middlewareMap[$middlewareClass] ?? $middlewareClass;

        if (!class_exists($class)) {
            // Skip non-existent middleware in development
            if ($this->config['app']['debug'] ?? false) {
                return $this->executeMiddleware($middleware, $next);
            }
            throw new \RuntimeException("Middleware not found: {$class}");
        }

        $instance = new $class();

        return $instance->handle($this->request, function () use ($middleware, $next) {
            return $this->executeMiddleware($middleware, $next);
        });
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);

        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }

    /**
     * Handle exceptions
     */
    private function handleException(\Throwable $e): void
    {
        // Let the global exception handler deal with it
        throw $e;
    }

    /**
     * Get the router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the request instance
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the database instance
     */
    public function getDatabase(): Database
    {
        if ($this->database === null) {
            $this->database = new Database($this->config['database']);
        }
        return $this->database;
    }

    /**
     * Get configuration array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if running in debug mode
     */
    public function isDebug(): bool
    {
        return $this->config['app']['debug'] ?? false;
    }
}
