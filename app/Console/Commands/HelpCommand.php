<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Help Command
 */
class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Display available commands';

    public function run(array $args): int
    {
        $this->line('');
        $this->info('FWP Image Gallery CLI');
        $this->line('');
        $this->line('Usage: php app/Console/cli.php <command> [options]');
        $this->line('');
        $this->line('Available commands:');
        $this->line('');
        $this->line('  help              Display this help message');
        $this->line('  ai:process        Process images in AI queue');
        $this->line('  sitemap:generate  Generate XML sitemaps');
        $this->line('  cache:clear       Clear all caches');
        $this->line('  cache:cleanup     Clean up expired cache files');
        $this->line('  trends:fetch      Fetch trending keywords');
        $this->line('');
        $this->line('Options:');
        $this->line('  --limit=N         Limit number of items to process');
        $this->line('  --force           Force operation');
        $this->line('');

        return 0;
    }
}
