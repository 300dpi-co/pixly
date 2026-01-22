<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Automatic Migration Runner
 *
 * Silently runs pending database migrations on app startup.
 * Users never need to manually run SQL - updates just work.
 */
class MigrationRunner
{
    private $db;
    private string $migrationsPath;
    private array $log = [];

    public function __construct()
    {
        $this->db = db();
        $this->migrationsPath = ROOT_PATH . '/database/migrations';
    }

    /**
     * Run all pending migrations silently
     * Called on admin page load
     */
    public function runPending(): void
    {
        try {
            // Ensure migrations table exists
            $this->ensureMigrationsTable();

            // Get list of already-run migrations
            $completed = $this->getCompletedMigrations();

            // Get all migration files
            $files = $this->getMigrationFiles();

            // Run pending migrations
            foreach ($files as $file) {
                $migrationName = basename($file, '.sql');

                if (!in_array($migrationName, $completed)) {
                    $this->runMigration($file, $migrationName);
                }
            }
        } catch (\Throwable $e) {
            // Log error but don't break the app
            if (function_exists('config') && config('app.debug', false)) {
                error_log('MigrationRunner: ' . $e->getMessage());
            }
        }
    }

    /**
     * Ensure the migrations tracking table exists
     */
    private function ensureMigrationsTable(): void
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL DEFAULT 1,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Get list of completed migration names
     */
    private function getCompletedMigrations(): array
    {
        try {
            $results = $this->db->fetchAll("SELECT migration FROM migrations");
            return array_column($results, 'migration');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get all migration SQL files sorted by name
     */
    private function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.sql');
        sort($files); // Alphabetical order
        return $files;
    }

    /**
     * Run a single migration file
     */
    private function runMigration(string $file, string $migrationName): void
    {
        $sql = file_get_contents($file);

        if (empty(trim($sql))) {
            return;
        }

        // Split by semicolons to handle multiple statements
        // But be careful with semicolons inside strings
        $statements = $this->splitSqlStatements($sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            // Skip empty statements and comments
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }

            try {
                $this->db->execute($statement);
            } catch (\Throwable $e) {
                // Log but continue - some statements may fail if already applied
                // e.g., "column already exists" errors are OK
                $errorMsg = $e->getMessage();

                // These errors are OK - column/index already exists
                $ignorableErrors = [
                    'Duplicate column name',
                    'Duplicate key name',
                    'already exists',
                    'SQLSTATE[42S21]', // Column already exists
                    'SQLSTATE[42000]', // Duplicate key name
                ];

                $isIgnorable = false;
                foreach ($ignorableErrors as $ignorable) {
                    if (stripos($errorMsg, $ignorable) !== false) {
                        $isIgnorable = true;
                        break;
                    }
                }

                if (!$isIgnorable) {
                    $this->log[] = "Migration {$migrationName} error: {$errorMsg}";
                    if (function_exists('config') && config('app.debug', false)) {
                        error_log("Migration {$migrationName} error: {$errorMsg}");
                    }
                }
            }
        }

        // Record migration as completed
        try {
            $batch = (int) $this->db->fetchColumn("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations");
            $this->db->insert('migrations', [
                'migration' => $migrationName,
                'batch' => $batch,
            ]);
            $this->log[] = "Migration completed: {$migrationName}";
        } catch (\Throwable $e) {
            // Already recorded, that's fine
        }
    }

    /**
     * Split SQL into individual statements
     */
    private function splitSqlStatements(string $sql): array
    {
        // Remove SQL comments
        $sql = preg_replace('/--.*$/m', '', $sql);

        // Split by semicolons
        $statements = preg_split('/;\s*$/m', $sql);

        return array_filter(array_map('trim', $statements));
    }

    /**
     * Get migration log
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
