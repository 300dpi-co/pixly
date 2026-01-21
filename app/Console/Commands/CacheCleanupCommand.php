<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Cache Cleanup Command
 *
 * Removes expired cache files.
 */
class CacheCleanupCommand extends Command
{
    protected string $name = 'cache:cleanup';
    protected string $description = 'Clean up expired cache files';

    public function run(array $args): int
    {
        $this->info('Cleaning up expired cache files...');

        $cachePath = config('cache.path') ?? \ROOT_PATH . '/public_html/cache';
        $defaultTtl = config('cache.default_ttl') ?? 3600;

        if (!is_dir($cachePath)) {
            $this->warn('Cache directory does not exist.');
            return 0;
        }

        $count = $this->cleanupDirectory($cachePath, $defaultTtl);

        $this->info("Removed {$count} expired cache files.");

        return 0;
    }

    /**
     * Clean up expired files in directory
     */
    private function cleanupDirectory(string $path, int $ttl): int
    {
        $count = 0;
        $now = time();
        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $age = $now - filemtime($file);
                if ($age > $ttl) {
                    unlink($file);
                    $count++;
                }
            } elseif (is_dir($file) && basename($file) !== '.' && basename($file) !== '..') {
                $count += $this->cleanupDirectory($file, $ttl);
                // Remove empty directories
                if (count(glob($file . '/*')) === 0) {
                    @rmdir($file);
                }
            }
        }

        return $count;
    }
}
