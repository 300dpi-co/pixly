<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cache Service
 *
 * Simple file-based caching system.
 */
class CacheService
{
    private string $path;
    private bool $enabled;
    private int $defaultTtl;

    public function __construct()
    {
        $config = config('cache');
        $this->path = $config['path'] ?? \ROOT_PATH . '/public_html/cache';
        $this->enabled = $config['enabled'] ?? true;
        $this->defaultTtl = $config['default_ttl'] ?? 3600;

        // Ensure cache directory exists
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Get cached value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->enabled) {
            return $default;
        }

        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        // Check expiration
        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set cached value
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? $this->defaultTtl;
        $file = $this->getFilePath($key);

        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'created' => time(),
        ];

        // Ensure subdirectory exists
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($file, serialize($data)) !== false;
    }

    /**
     * Check if key exists and is not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete cached value
     */
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Get or set cache value
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        return $this->clearDirectory($this->path);
    }

    /**
     * Clear cache by prefix/pattern
     */
    public function clearPattern(string $pattern): int
    {
        $count = 0;
        $files = glob($this->path . '/' . $pattern . '*.cache');

        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get file path for cache key
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        // Use first 2 chars as subdirectory for better file distribution
        $subdir = substr($hash, 0, 2);
        return $this->path . '/' . $subdir . '/' . $hash . '.cache';
    }

    /**
     * Clear directory recursively
     */
    private function clearDirectory(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }

        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                @rmdir($file);
            }
        }

        return true;
    }
}
