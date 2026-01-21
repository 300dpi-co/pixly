<?php
/**
 * Migration Runner Script
 *
 * Run specific migration files against the database.
 * Usage: php run-migration.php [migration_name]
 * Or visit: http://fwp.local/run-migration.php?migration=add_appreciations
 */

// Bootstrap the application
require_once __DIR__ . '/app/bootstrap.php';

// Security: Only allow in development or CLI
$isCli = php_sapi_name() === 'cli';
if (!$isCli && ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1' && ($_SERVER['REMOTE_ADDR'] ?? '') !== '::1') {
    die('Access denied. Run from localhost only.');
}

// Get migration name
if ($isCli) {
    $migrationName = $argv[1] ?? null;
} else {
    header('Content-Type: text/plain; charset=utf-8');
    $migrationName = $_GET['migration'] ?? null;
}

// List available migrations if none specified
$migrationsDir = __DIR__ . '/database/migrations';
$availableMigrations = [];

if (is_dir($migrationsDir)) {
    $files = glob($migrationsDir . '/*.sql');
    foreach ($files as $file) {
        $availableMigrations[] = basename($file, '.sql');
    }
}

if (!$migrationName) {
    echo "=== Migration Runner ===\n\n";
    echo "Usage:\n";
    echo "  CLI: php run-migration.php <migration_name>\n";
    echo "  Web: run-migration.php?migration=<migration_name>\n";
    echo "  Run all: php run-migration.php --all\n\n";
    echo "Available migrations:\n";
    foreach ($availableMigrations as $m) {
        echo "  - {$m}\n";
    }
    exit(0);
}

// Run all migrations
if ($migrationName === '--all' || $migrationName === 'all') {
    echo "Running all migrations...\n\n";

    try {
        $db = app()->getDatabase();
        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;

        foreach ($availableMigrations as $migration) {
            $filePath = $migrationsDir . '/' . $migration . '.sql';
            echo "Processing: {$migration}\n";

            try {
                $sql = file_get_contents($filePath);
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    fn($s) => !empty($s) && !str_starts_with($s, '--')
                );

                foreach ($statements as $statement) {
                    if (empty(trim($statement))) continue;
                    $db->execute($statement);
                }

                echo "  [OK] {$migration}\n";
                $successCount++;
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
                // Check if it's just "already exists" type error
                if (str_contains($errorMsg, 'Duplicate') || str_contains($errorMsg, 'already exists')) {
                    echo "  [SKIP] {$migration} - Already applied\n";
                    $skipCount++;
                } else {
                    echo "  [ERROR] {$migration}: {$errorMsg}\n";
                    $errorCount++;
                }
            }
        }

        echo "\n=== Summary ===\n";
        echo "Success: {$successCount}\n";
        echo "Skipped: {$skipCount}\n";
        echo "Errors: {$errorCount}\n";

    } catch (\Throwable $e) {
        echo "Database connection failed: " . $e->getMessage() . "\n";
        exit(1);
    }

    exit(0);
}

// Run specific migration
$migrationFile = $migrationsDir . '/' . $migrationName . '.sql';

if (!file_exists($migrationFile)) {
    echo "Error: Migration file not found: {$migrationFile}\n\n";
    echo "Available migrations:\n";
    foreach ($availableMigrations as $m) {
        echo "  - {$m}\n";
    }
    exit(1);
}

echo "Running migration: {$migrationName}\n";
echo str_repeat('-', 50) . "\n\n";

try {
    $db = app()->getDatabase();
    $sql = file_get_contents($migrationFile);

    // Remove SQL comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s)
    );

    $executedCount = 0;

    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) {
            continue;
        }

        echo "Executing statement " . ($index + 1) . "...\n";

        try {
            $db->execute($statement);
            echo "  [OK]\n";
            $executedCount++;
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();

            // Handle "already exists" gracefully
            if (str_contains($errorMsg, 'Duplicate column') ||
                str_contains($errorMsg, 'already exists') ||
                str_contains($errorMsg, 'Duplicate key name')) {
                echo "  [SKIP] Already exists\n";
            } else {
                echo "  [ERROR] " . $errorMsg . "\n";
            }
        }
    }

    echo "\n" . str_repeat('-', 50) . "\n";
    echo "Migration completed! Executed {$executedCount} statement(s).\n";

} catch (\Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
