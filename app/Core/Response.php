<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response Wrapper
 *
 * Provides a clean interface for building and sending HTTP responses.
 */
class Response
{
    private string $content;
    private int $statusCode;
    private array $headers = [];

    public function __construct(string $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    /**
     * Set response content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get response content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    /**
     * Get header
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set content type
     */
    public function setContentType(string $contentType): self
    {
        return $this->setHeader('Content-Type', $contentType);
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        // Send status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Send content
        echo $this->content;
    }

    /**
     * Create HTML response
     */
    public static function html(string $content, int $statusCode = 200): self
    {
        return (new self($content, $statusCode))
            ->setContentType('text/html; charset=utf-8');
    }

    /**
     * Create JSON response
     */
    public static function json(mixed $data, int $statusCode = 200): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return (new self($content, $statusCode))
            ->setContentType('application/json');
    }

    /**
     * Create XML response
     */
    public static function xml(string $content, int $statusCode = 200): self
    {
        return (new self($content, $statusCode))
            ->setContentType('application/xml; charset=utf-8');
    }

    /**
     * Create redirect response
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        return (new self('', $statusCode))
            ->setHeader('Location', $url);
    }

    /**
     * Create file download response
     */
    public static function download(string $content, string $filename, string $contentType = 'application/octet-stream'): self
    {
        return (new self($content, 200))
            ->setContentType($contentType)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($content));
    }

    /**
     * Create no content response
     */
    public static function noContent(): self
    {
        return new self('', 204);
    }

    /**
     * Create not found response
     */
    public static function notFound(string $message = 'Not Found'): self
    {
        return new self($message, 404);
    }

    /**
     * Create forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403);
    }

    /**
     * Create server error response
     */
    public static function serverError(string $message = 'Internal Server Error'): self
    {
        return new self($message, 500);
    }

    /**
     * Set cache headers
     */
    public function cache(int $seconds, bool $public = true): self
    {
        $directive = $public ? 'public' : 'private';

        return $this
            ->setHeader('Cache-Control', "{$directive}, max-age={$seconds}")
            ->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
    }

    /**
     * Set no-cache headers
     */
    public function noCache(): self
    {
        return $this
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }

    /**
     * Set CORS headers
     */
    public function cors(string $origin = '*', array $methods = ['GET', 'POST'], array $headers = []): self
    {
        $this->setHeader('Access-Control-Allow-Origin', $origin);
        $this->setHeader('Access-Control-Allow-Methods', implode(', ', $methods));

        if (!empty($headers)) {
            $this->setHeader('Access-Control-Allow-Headers', implode(', ', $headers));
        }

        return $this;
    }
}
