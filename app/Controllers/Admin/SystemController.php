<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AutoUpdater;
use App\Services\UpdateChecker;

/**
 * System Controller
 *
 * Handles system-level operations like updates.
 */
class SystemController extends Controller
{
    /**
     * Check for updates (clears cache and fetches fresh)
     */
    public function checkUpdate(): Response
    {
        try {
            $checker = new UpdateChecker();

            // Clear cache and fetch fresh
            $checker->clearCache();
            $checker->silentCheck();

            $currentVersion = file_exists(ROOT_PATH . '/VERSION')
                ? trim(file_get_contents(ROOT_PATH . '/VERSION'))
                : '1.0.0';

            $hasUpdate = $checker->hasUpdate();
            $latestVersion = $checker->getLatestVersion();
            $announcement = $checker->getAnnouncement();

            if ($hasUpdate) {
                return $this->json([
                    'success' => true,
                    'has_update' => true,
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                    'announcement' => $announcement,
                    'message' => "Update available: v{$latestVersion}",
                ]);
            }

            return $this->json([
                'success' => true,
                'has_update' => false,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'message' => 'You are running the latest version',
            ]);

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perform manual system update
     */
    public function update(): Response
    {
        try {
            // Try to fetch status directly from GitHub
            $statusUrl = 'https://raw.githubusercontent.com/300dpi-co/pixly/main/remote/status.json';

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'header' => 'User-Agent: Pixly-Updater/1.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $response = @file_get_contents($statusUrl, false, $context);

            if ($response === false) {
                $error = error_get_last();
                return $this->json([
                    'success' => false,
                    'error' => 'Could not connect to update server',
                    'details' => $error['message'] ?? 'Unknown error',
                    'tip' => 'Check if allow_url_fopen is enabled in PHP',
                ]);
            }

            $status = json_decode($response, true);

            if (!$status || !is_array($status)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid response from update server',
                    'response' => substr($response, 0, 200),
                ]);
            }

            // Check current version
            $currentVersion = file_exists(ROOT_PATH . '/VERSION')
                ? trim(file_get_contents(ROOT_PATH . '/VERSION'))
                : '1.0.0';

            if (!version_compare($status['latest_version'] ?? '0', $currentVersion, '>')) {
                return $this->json([
                    'success' => false,
                    'error' => 'No update available - you have the latest version',
                    'current' => $currentVersion,
                    'latest' => $status['latest_version'] ?? 'unknown',
                ]);
            }

            if (empty($status['download_url'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'No download URL available',
                ]);
            }

            // Force auto_update flag for manual update
            $status['auto_update'] = true;

            // Perform update
            $updater = new AutoUpdater();
            $result = $updater->update($status);

            if ($result) {
                // Clear update cache
                $cacheFile = (defined('STORAGE_PATH') ? STORAGE_PATH : ROOT_PATH . '/storage')
                    . '/cache/update_check.json';
                @unlink($cacheFile);

                return $this->json([
                    'success' => true,
                    'message' => 'Update installed successfully! Please refresh the page.',
                    'version' => $status['latest_version'],
                    'log' => $updater->getLog(),
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Update failed: ' . implode(', ', $updater->getErrors()),
                    'errors' => $updater->getErrors(),
                    'log' => $updater->getLog(),
                ]);
            }

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug', false) ? $e->getTraceAsString() : null,
            ], 500);
        }
    }
}
