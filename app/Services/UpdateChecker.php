<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Update Checker Service
 *
 * Silent phone-home system that checks for updates and communicates
 * with the license server when available. Designed to be completely
 * invisible to end users - no errors, no UI hints.
 */
class UpdateChecker
{
    private const GITHUB_STATUS_URL = 'https://raw.githubusercontent.com/300dpi-co/pixly/main/remote/status.json';
    private const CACHE_KEY = 'update_check';
    private const CACHE_TTL = 86400; // 24 hours
    private const TIMEOUT = 5; // seconds

    private string $currentVersion = '1.0.0';
    private ?array $cachedStatus = null;

    public function __construct()
    {
        $this->currentVersion = $this->getCurrentVersion();
    }

    /**
     * Perform silent update check
     * Called on admin dashboard load
     * Never throws, never shows errors
     */
    public function silentCheck(): void
    {
        try {
            // Check cache first
            if ($this->isCacheValid()) {
                return;
            }

            // Fetch status from GitHub
            $status = $this->fetchGitHubStatus();
            if ($status === null) {
                return; // Silently fail
            }

            // Cache the status
            $this->cacheStatus($status);

            // If API server is configured, phone home
            if (!empty($status['api_server'])) {
                $this->phoneHome($status['api_server']);
            }
        } catch (\Throwable $e) {
            // Silently ignore all errors
            // Log for debugging if in development
            if ($this->isDebugMode()) {
                error_log('UpdateChecker: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get cached status data
     */
    public function getStatus(): ?array
    {
        if ($this->cachedStatus !== null) {
            return $this->cachedStatus;
        }

        $cacheFile = $this->getCacheFile();
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if ($data && isset($data['expires']) && $data['expires'] > time()) {
                $this->cachedStatus = $data['status'] ?? null;
                return $this->cachedStatus;
            }
        }

        return null;
    }

    /**
     * Check if update is available
     */
    public function hasUpdate(): bool
    {
        $status = $this->getStatus();
        if (!$status || empty($status['latest_version'])) {
            return false;
        }

        return version_compare($status['latest_version'], $this->currentVersion, '>');
    }

    /**
     * Get latest version from status
     */
    public function getLatestVersion(): ?string
    {
        $status = $this->getStatus();
        return $status['latest_version'] ?? null;
    }

    /**
     * Get any announcement message
     */
    public function getAnnouncement(): ?string
    {
        $status = $this->getStatus();
        return $status['announcement'] ?? $status['message'] ?? null;
    }

    /**
     * Fetch status from GitHub raw
     */
    private function fetchGitHubStatus(): ?array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => self::TIMEOUT,
                'ignore_errors' => true,
                'header' => 'User-Agent: Pixly/' . $this->currentVersion,
            ],
        ]);

        $response = @file_get_contents(self::GITHUB_STATUS_URL, false, $context);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Phone home to API server
     * Sends: domain, version, PHP version, license key
     */
    private function phoneHome(string $apiServer): void
    {
        $payload = [
            'domain' => $this->getDomain(),
            'version' => $this->currentVersion,
            'php_version' => PHP_VERSION,
            'license_key' => $this->getLicenseKey(),
            'timestamp' => time(),
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'timeout' => self::TIMEOUT,
                'ignore_errors' => true,
                'header' => [
                    'Content-Type: application/json',
                    'User-Agent: Pixly/' . $this->currentVersion,
                ],
                'content' => json_encode($payload),
            ],
        ]);

        $endpoint = rtrim($apiServer, '/') . '/api/v1/phone-home';
        @file_get_contents($endpoint, false, $context);
        // Response is ignored - this is fire-and-forget
    }

    /**
     * Check if cache is still valid
     */
    private function isCacheValid(): bool
    {
        $cacheFile = $this->getCacheFile();
        if (!file_exists($cacheFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($cacheFile), true);
        return $data && isset($data['expires']) && $data['expires'] > time();
    }

    /**
     * Cache the status data
     */
    private function cacheStatus(array $status): void
    {
        $cacheFile = $this->getCacheFile();
        $cacheDir = dirname($cacheFile);

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $data = [
            'status' => $status,
            'expires' => time() + self::CACHE_TTL,
            'checked_at' => date('Y-m-d H:i:s'),
        ];

        @file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
        $this->cachedStatus = $status;
    }

    /**
     * Get cache file path
     */
    private function getCacheFile(): string
    {
        return (defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 2) . '/storage')
            . '/cache/' . self::CACHE_KEY . '.json';
    }

    /**
     * Get current installed version
     */
    private function getCurrentVersion(): string
    {
        // Try to read from a version file or config
        $versionFile = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/VERSION';
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }

        return '1.0.0';
    }

    /**
     * Get current domain
     */
    private function getDomain(): string
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
    }

    /**
     * Get license key from LicenseService
     */
    private function getLicenseKey(): ?string
    {
        try {
            $license = new LicenseService();
            return $license->getLicenseKey();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if debug mode is enabled
     */
    private function isDebugMode(): bool
    {
        if (function_exists('config')) {
            return config('app.debug', false);
        }
        return false;
    }
}
