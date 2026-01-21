<?php

declare(strict_types=1);

namespace App\Console;

/**
 * Base Command Class
 */
abstract class Command
{
    protected string $name = '';
    protected string $description = '';

    /**
     * Run the command
     */
    abstract public function run(array $args): int;

    /**
     * Output a line
     */
    protected function line(string $message): void
    {
        echo $message . "\n";
    }

    /**
     * Output info message
     */
    protected function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    /**
     * Output warning message
     */
    protected function warn(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }

    /**
     * Output error message
     */
    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }

    /**
     * Get database instance
     */
    protected function db(): \App\Core\Database
    {
        return app()->getDatabase();
    }

    /**
     * Parse options from args
     */
    protected function parseOptions(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $parts = explode('=', substr($arg, 2), 2);
                $options[$parts[0]] = $parts[1] ?? true;
            } elseif (str_starts_with($arg, '-')) {
                $options[substr($arg, 1)] = true;
            }
        }
        return $options;
    }
}
