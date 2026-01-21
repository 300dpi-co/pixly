<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Cache Clear Command
 *
 * Clears all cache files.
 */
class CacheClearCommand extends Command
{
    protected string $name = 'cache:clear';
    protected string $description = 'Clear all caches';

    public function run(array $args): int
    {
        $this->info('Clearing caches...');

        $cachePath = config('cache.path') ?? \ROOT_PATH . '/public_html/cache';

        if (!is_dir($cachePath)) {
            $this->warn('Cache directory does not exist.');
            return 0;
        }

        $count = $this->clearDirectory($cachePath);

        $this->info("Cleared {$count} cache files.");

        return 0;
    }

    /**
     * Clear all files in directory
     */
    private function clearDirectory(string $path): int
    {
        $count = 0;
        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            } elseif (is_dir($file) && basename($file) !== '.' && basename($file) !== '..') {
                $count += $this->clearDirectory($file);
                @rmdir($file);
            }
        }

        return $count;
    }
}
