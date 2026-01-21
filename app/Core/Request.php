<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Request Wrapper
 *
 * Provides a clean interface for accessing request data,
 * headers, and other HTTP request information.
 */
class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $server;
    private array $headers;
    private array $cookies;
    private array $files;
    private ?array $json = null;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = $this->parsePath();
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;
        $this->headers = $this->parseHeaders();
    }

    /**
     * Parse the request path
     */
    private function parsePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH);

        // Remove trailing slash (except for root)
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path ?: '/';
    }

    /**
     * Parse request headers
     */
    private function parseHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $header = ucwords(strtolower($header), '-');
                $headers[$header] = $value;
            }
        }

        // Add Content-Type and Content-Length if present
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        return $headers;
    }

    /**
     * Get HTTP method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get full URL
     */
    public function getUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri = $this->server['REQUEST_URI'] ?? '/';

        return "{$scheme}://{$host}{$uri}";
    }

    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off')
            || ($this->server['SERVER_PORT'] ?? 80) == 443;
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return ($this->headers['X-Requested-With'] ?? '') === 'XMLHttpRequest';
    }

    /**
     * Check if request expects JSON response
     */
    public function wantsJson(): bool
    {
        $accept = $this->headers['Accept'] ?? '';
        return str_contains($accept, 'application/json');
    }

    /**
     * Check if request method matches
     */
    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * Get query parameter
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public function queryAll(): array
    {
        return $this->query;
    }

    /**
     * Get POST parameter
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all POST parameters
     */
    public function postAll(): array
    {
        return $this->post;
    }

    /**
     * Get input from either GET or POST
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input (merged GET and POST)
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    /**
     * Get only specified keys from input
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Get all input except specified keys
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    /**
     * Check if input key exists
     */
    public function has(string $key): bool
    {
        return isset($this->post[$key]) || isset($this->query[$key]);
    }

    /**
     * Get JSON body
     */
    public function json(string $key = null, mixed $default = null): mixed
    {
        if ($this->json === null) {
            $body = file_get_contents('php://input');
            $this->json = json_decode($body, true) ?? [];
        }

        if ($key === null) {
            return $this->json;
        }

        return $this->json[$key] ?? $default;
    }

    /**
     * Get header
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $key = ucwords(strtolower($key), '-');
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get cookie
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Check if file was uploaded
     */
    public function hasFile(string $key): bool
    {
        $file = $this->files[$key] ?? null;
        return $file !== null && $file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Get server variable
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (!empty($this->server[$key])) {
                // X-Forwarded-For can contain multiple IPs
                $ip = explode(',', $this->server[$key])[0];
                return trim($ip);
            }
        }

        return '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Get referer
     */
    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }
}
