<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Auto Updater Service
 *
 * WordPress-style silent auto-update system.
 * Downloads and installs updates from GitHub automatically.
 */
class AutoUpdater
{
    private const UPDATE_LOCK_FILE = 'update.lock';
    private const BACKUP_DIR = 'backups';
    private const TIMEOUT = 30;

    // Directories to preserve (never overwrite)
    private const PRESERVE_DIRS = [
        'storage',
        'uploads',
        'cache',
    ];

    // Files to preserve (never overwrite)
    private const PRESERVE_FILES = [
        'app/Config/database.php',
        'robots.txt',
        '.htaccess',
    ];

    private string $rootPath;
    private string $storagePath;
    private array $errors = [];
    private array $log = [];

    public function __construct()
    {
        $this->rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $this->storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : $this->rootPath . '/storage';
    }

    /**
     * Perform auto-update if new version available
     * Returns true if update was performed
     */
    public function update(array $status): bool
    {
        try {
            // Check if auto-update is enabled
            if (empty($status['auto_update'])) {
                return false;
            }

            // Check if download URL exists
            if (empty($status['download_url'])) {
                $this->log('No download URL provided');
                return false;
            }

            // Check if already updating (lock file)
            if ($this->isLocked()) {
                $this->log('Update already in progress');
                return false;
            }

            // Create lock
            $this->lock();

            // Download the update
            $this->log('Downloading update...');
            $zipPath = $this->download($status['download_url']);
            if (!$zipPath) {
                $this->unlock();
                return false;
            }

            // Extract the update
            $this->log('Extracting update...');
            $extractPath = $this->extract($zipPath);
            if (!$extractPath) {
                @unlink($zipPath);
                $this->unlock();
                return false;
            }

            // Find the actual source directory (GitHub zips have a subdirectory)
            $sourcePath = $this->findSourceDir($extractPath);
            if (!$sourcePath) {
                $this->cleanup($extractPath, $zipPath);
                $this->unlock();
                return false;
            }

            // Install the update
            $this->log('Installing update...');
            $installed = $this->install($sourcePath);

            // Run migrations
            if ($installed) {
                $this->log('Running migrations...');
                $this->runMigrations();
            }

            // Cleanup
            $this->log('Cleaning up...');
            $this->cleanup($extractPath, $zipPath);

            // Update version file
            if ($installed && !empty($status['latest_version'])) {
                $this->updateVersionFile($status['latest_version']);
            }

            // Release lock
            $this->unlock();

            // Clear opcache if available
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            $this->log('Update completed successfully!');
            return $installed;

        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage();
            $this->log('Update failed: ' . $e->getMessage());
            $this->unlock();
            return false;
        }
    }

    /**
     * Download update zip from URL
     */
    private function download(string $url): ?string
    {
        $tempFile = $this->storagePath . '/cache/update_' . time() . '.zip';

        // Ensure cache directory exists
        $cacheDir = dirname($tempFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        // Download using file_get_contents with context
        $context = stream_context_create([
            'http' => [
                'timeout' => self::TIMEOUT,
                'follow_location' => true,
                'max_redirects' => 5,
                'header' => [
                    'User-Agent: Pixly-AutoUpdater/1.0',
                ],
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            $this->errors[] = 'Failed to download update from: ' . $url;
            $this->log('Download failed');
            return null;
        }

        if (file_put_contents($tempFile, $content) === false) {
            $this->errors[] = 'Failed to save downloaded file';
            $this->log('Failed to save zip');
            return null;
        }

        $this->log('Downloaded ' . round(strlen($content) / 1024) . ' KB');
        return $tempFile;
    }

    /**
     * Extract zip file
     */
    private function extract(string $zipPath): ?string
    {
        if (!class_exists('ZipArchive')) {
            $this->errors[] = 'ZipArchive extension not available';
            return null;
        }

        $extractPath = $this->storagePath . '/cache/update_extract_' . time();

        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            $this->errors[] = 'Failed to open zip file';
            return null;
        }

        if (!$zip->extractTo($extractPath)) {
            $this->errors[] = 'Failed to extract zip file';
            $zip->close();
            return null;
        }

        $zip->close();
        $this->log('Extracted to temp directory');
        return $extractPath;
    }

    /**
     * Find the actual source directory in extracted folder
     * GitHub zips contain a subdirectory like "pixly-main"
     */
    private function findSourceDir(string $extractPath): ?string
    {
        $items = @scandir($extractPath);
        if (!$items) {
            return null;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $extractPath . '/' . $item;
            if (is_dir($path)) {
                // Check if this looks like the Pixly root (has app/ directory)
                if (is_dir($path . '/app')) {
                    return $path;
                }
            }
        }

        // Maybe files are directly in extract path
        if (is_dir($extractPath . '/app')) {
            return $extractPath;
        }

        $this->errors[] = 'Could not find source directory in extracted files';
        return null;
    }

    /**
     * Install update by copying files
     */
    private function install(string $sourcePath): bool
    {
        // Get list of files to update
        $files = $this->getFilesToUpdate($sourcePath);

        if (empty($files)) {
            $this->errors[] = 'No files to update';
            return false;
        }

        $this->log('Updating ' . count($files) . ' files...');

        $updated = 0;
        foreach ($files as $relativePath) {
            $sourceFile = $sourcePath . '/' . $relativePath;
            $destFile = $this->rootPath . '/' . $relativePath;

            // Skip preserved files
            if ($this->shouldPreserve($relativePath)) {
                continue;
            }

            // Create destination directory if needed
            $destDir = dirname($destFile);
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }

            // Copy file
            if (is_file($sourceFile)) {
                if (@copy($sourceFile, $destFile)) {
                    $updated++;
                } else {
                    $this->log('Failed to update: ' . $relativePath);
                }
            }
        }

        $this->log("Updated {$updated} files");
        return $updated > 0;
    }

    /**
     * Get list of files to update
     */
    private function getFilesToUpdate(string $sourcePath): array
    {
        $files = [];
        $this->scanDirectory($sourcePath, $sourcePath, $files);
        return $files;
    }

    /**
     * Recursively scan directory for files
     */
    private function scanDirectory(string $basePath, string $currentPath, array &$files): void
    {
        $items = @scandir($currentPath);
        if (!$items) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.git') {
                continue;
            }

            $fullPath = $currentPath . '/' . $item;
            $relativePath = substr($fullPath, strlen($basePath) + 1);

            // Skip preserved directories entirely
            foreach (self::PRESERVE_DIRS as $preserveDir) {
                if (str_starts_with($relativePath, $preserveDir . '/') || $relativePath === $preserveDir) {
                    continue 2;
                }
            }

            if (is_dir($fullPath)) {
                $this->scanDirectory($basePath, $fullPath, $files);
            } else {
                $files[] = $relativePath;
            }
        }
    }

    /**
     * Check if file/directory should be preserved
     */
    private function shouldPreserve(string $relativePath): bool
    {
        // Check preserved files
        if (in_array($relativePath, self::PRESERVE_FILES, true)) {
            return true;
        }

        // Check preserved directories
        foreach (self::PRESERVE_DIRS as $dir) {
            if (str_starts_with($relativePath, $dir . '/') || $relativePath === $dir) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): void
    {
        try {
            $migrationsPath = $this->rootPath . '/database/migrations';
            if (!is_dir($migrationsPath)) {
                return;
            }

            // Get database connection
            if (!function_exists('db')) {
                return;
            }

            $db = db();
            $files = glob($migrationsPath . '/*.sql');
            sort($files);

            foreach ($files as $file) {
                $sql = file_get_contents($file);
                $sql = preg_replace('/--.*$/m', '', $sql);
                $statements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        try {
                            $db->execute($statement);
                        } catch (\Throwable $e) {
                            // Ignore duplicate/exists errors
                            $msg = $e->getMessage();
                            if (strpos($msg, 'Duplicate') === false
                                && strpos($msg, 'already exists') === false
                                && strpos($msg, '1062') === false
                                && strpos($msg, '1050') === false
                                && strpos($msg, '1060') === false) {
                                $this->log('Migration warning: ' . $msg);
                            }
                        }
                    }
                }
            }

            $this->log('Migrations completed');
        } catch (\Throwable $e) {
            $this->log('Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Update VERSION file
     */
    private function updateVersionFile(string $version): void
    {
        $versionFile = $this->rootPath . '/VERSION';
        @file_put_contents($versionFile, $version . "\n");
    }

    /**
     * Cleanup temporary files
     */
    private function cleanup(string $extractPath, string $zipPath): void
    {
        // Remove zip file
        @unlink($zipPath);

        // Remove extracted directory
        $this->removeDirectory($extractPath);
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = @scandir($dir);
        if (!$items) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    /**
     * Check if update is locked
     */
    private function isLocked(): bool
    {
        $lockFile = $this->storagePath . '/' . self::UPDATE_LOCK_FILE;

        if (!file_exists($lockFile)) {
            return false;
        }

        // Check if lock is stale (older than 10 minutes)
        $lockTime = @filemtime($lockFile);
        if ($lockTime && (time() - $lockTime) > 600) {
            @unlink($lockFile);
            return false;
        }

        return true;
    }

    /**
     * Create update lock
     */
    private function lock(): void
    {
        $lockFile = $this->storagePath . '/' . self::UPDATE_LOCK_FILE;
        @file_put_contents($lockFile, date('Y-m-d H:i:s'));
    }

    /**
     * Remove update lock
     */
    private function unlock(): void
    {
        $lockFile = $this->storagePath . '/' . self::UPDATE_LOCK_FILE;
        @unlink($lockFile);
    }

    /**
     * Add to log
     */
    private function log(string $message): void
    {
        $this->log[] = '[' . date('H:i:s') . '] ' . $message;

        // Also write to file for debugging
        $logFile = $this->storagePath . '/logs/update.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        @file_put_contents(
            $logFile,
            '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get log
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
