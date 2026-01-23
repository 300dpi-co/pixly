<?php

declare(strict_types=1);

/**
 * CLI Entry Point
 *
 * Usage: php app/Console/cli.php <command> [options]
 *
 * Commands:
 *   ai:process              Process AI queue (generic)
 *   images:process-fast     Process fast queue (trusted users, no delays)
 *   images:process-scheduled Process ONE scheduled image (cron every 4 min)
 *   sitemap:generate        Generate sitemaps
 *   cache:clear             Clear all caches
 *   cache:cleanup           Clean up expired cache
 *   trends:fetch            Fetch trending keywords
 *   webp:convert            Convert images to WebP format
 */

// Ensure CLI only
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

// Bootstrap
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

// Parse command
$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

// Available commands
$commands = [
    'help' => \App\Console\Commands\HelpCommand::class,
    'ai:process' => \App\Console\Commands\AiProcessCommand::class,
    'images:process-fast' => \App\Console\Commands\ProcessFastQueueCommand::class,
    'images:process-scheduled' => \App\Console\Commands\ProcessScheduledQueueCommand::class,
    'sitemap:generate' => \App\Console\Commands\SitemapGenerateCommand::class,
    'cache:clear' => \App\Console\Commands\CacheClearCommand::class,
    'cache:cleanup' => \App\Console\Commands\CacheCleanupCommand::class,
    'trends:fetch' => \App\Console\Commands\TrendsFetchCommand::class,
    'webp:convert' => \App\Console\Commands\WebpConvertCommand::class,
];

if (!isset($commands[$command])) {
    echo "Unknown command: {$command}\n";
    echo "Run 'php cli.php help' for available commands.\n";
    exit(1);
}

// Run command
try {
    $commandClass = $commands[$command];
    $instance = new $commandClass();
    $exitCode = $instance->run($args);
    exit($exitCode);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    if (config('app.debug')) {
        echo $e->getTraceAsString() . "\n";
    }
    exit(1);
}
