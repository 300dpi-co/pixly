<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\UpdateChecker;
use App\Services\AutoUpdater;

/**
 * System Controller
 *
 * Handles system-level operations like updates.
 */
class SystemController extends Controller
{
    /**
     * Perform manual system update
     */
    public function update(): Response
    {
        try {
            $checker = new UpdateChecker();

            // Force fresh check
            $checker->clearCache();
            $checker->silentCheck();

            $status = $checker->getStatus();

            if (!$status) {
                return $this->json([
                    'success' => false,
                    'error' => 'Could not fetch update information',
                ]);
            }

            if (!$checker->hasUpdate()) {
                return $this->json([
                    'success' => false,
                    'error' => 'No update available',
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
                // Clear cache after successful update
                $checker->clearCache();

                return $this->json([
                    'success' => true,
                    'message' => 'Update installed successfully',
                    'version' => $status['latest_version'],
                    'log' => $updater->getLog(),
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Update failed',
                    'errors' => $updater->getErrors(),
                    'log' => $updater->getLog(),
                ]);
            }

        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
