<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Automatic Migration Runner
 *
 * Runs database schema updates automatically on admin page load.
 * Uses PHP-based migrations for reliable execution.
 * Users never need to manually run SQL - updates just work.
 */
class MigrationRunner
{
    private $db;
    private array $log = [];

    public function __construct()
    {
        $this->db = \app()->getDatabase();
    }

    /**
     * Run all pending migrations
     */
    public function runPending(): void
    {
        try {
            $this->ensureMigrationsTable();
            $this->runAllMigrations();
        } catch (\Throwable $e) {
            $this->logError('MigrationRunner failed: ' . $e->getMessage());
        }
    }

    /**
     * Ensure migrations tracking table exists
     */
    private function ensureMigrationsTable(): void
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Check if migration has been run
     */
    private function hasRun(string $name): bool
    {
        try {
            $result = $this->db->fetch(
                "SELECT 1 FROM migrations WHERE migration = :name",
                ['name' => $name]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mark migration as complete
     */
    private function markComplete(string $name): void
    {
        try {
            $this->db->execute(
                "INSERT IGNORE INTO migrations (migration) VALUES (:name)",
                ['name' => $name]
            );
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * Check if column exists in table
     */
    private function columnExists(string $table, string $column): bool
    {
        try {
            $result = $this->db->fetch(
                "SELECT 1 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = :table
                 AND COLUMN_NAME = :column",
                ['table' => $table, 'column' => $column]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if table exists
     */
    private function tableExists(string $table): bool
    {
        try {
            $result = $this->db->fetch(
                "SELECT 1 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = :table",
                ['table' => $table]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check if setting exists
     */
    private function settingExists(string $key): bool
    {
        try {
            $result = $this->db->fetch(
                "SELECT 1 FROM settings WHERE setting_key = :key",
                ['key' => $key]
            );
            return !empty($result);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Add column if it doesn't exist
     */
    private function addColumn(string $table, string $column, string $definition): void
    {
        if (!$this->columnExists($table, $column)) {
            try {
                $this->db->execute("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
                $this->log[] = "Added column {$table}.{$column}";
            } catch (\Throwable $e) {
                $this->logError("Failed to add column {$table}.{$column}: " . $e->getMessage());
            }
        }
    }

    /**
     * Add setting if it doesn't exist
     */
    private function addSetting(string $key, string $value, string $type, string $description): void
    {
        if (!$this->settingExists($key)) {
            try {
                $this->db->insert('settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'description' => $description,
                    'is_public' => 0,
                ]);
                $this->log[] = "Added setting: {$key}";
            } catch (\Throwable $e) {
                $this->logError("Failed to add setting {$key}: " . $e->getMessage());
            }
        }
    }

    /**
     * Run all migrations
     */
    private function runAllMigrations(): void
    {
        // Migration: Add is_trusted column to users
        if (!$this->hasRun('add_is_trusted_column')) {
            $this->addColumn('users', 'is_trusted', 'TINYINT(1) DEFAULT 0');
            $this->markComplete('add_is_trusted_column');
        }

        // Migration: Add uploaded_by column to images
        if (!$this->hasRun('add_uploaded_by_column')) {
            $this->addColumn('images', 'uploaded_by', 'INT UNSIGNED NULL');
            $this->markComplete('add_uploaded_by_column');
        }

        // Migration: Add AI provider settings
        if (!$this->hasRun('add_ai_settings')) {
            $this->addSetting('ai_provider', 'replicate', 'string', 'AI provider for image analysis (claude or replicate)');
            $this->addSetting('replicate_api_key', '', 'encrypted', 'Replicate API key for LLaVA image analysis');
            $this->addSetting('claude_api_key', '', 'encrypted', 'Claude API key for image analysis');
            $this->addSetting('deepseek_api_key', '', 'encrypted', 'DeepSeek API key');
            $this->addSetting('deepinfra_api_key', '', 'encrypted', 'DeepInfra API key');
            $this->markComplete('add_ai_settings');
        }

        // Migration: Add contributor system settings
        if (!$this->hasRun('add_contributor_settings')) {
            $this->addSetting('contributor_system_enabled', '0', 'bool', 'Enable contributor role system');
            $this->addColumn('users', 'contributor_status', "ENUM('none','pending','approved','rejected') DEFAULT 'none'");
            $this->addColumn('users', 'contributor_requested_at', 'DATETIME NULL');
            $this->addColumn('users', 'contributor_approved_at', 'DATETIME NULL');
            $this->markComplete('add_contributor_settings');
        }

        // Migration: Ensure AI processing queue table exists
        if (!$this->hasRun('ensure_ai_queue_table')) {
            if (!$this->tableExists('ai_processing_queue')) {
                $this->db->execute("
                    CREATE TABLE ai_processing_queue (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        image_id INT UNSIGNED NOT NULL,
                        task_type VARCHAR(50) DEFAULT 'all',
                        priority INT DEFAULT 5,
                        status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
                        attempts INT DEFAULT 0,
                        error_message TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_status (status),
                        INDEX idx_image (image_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
            $this->markComplete('ensure_ai_queue_table');
        }

        // Migration: Ensure API logs table exists
        if (!$this->hasRun('ensure_api_logs_table')) {
            if (!$this->tableExists('api_logs')) {
                $this->db->execute("
                    CREATE TABLE api_logs (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        api_name VARCHAR(50) NOT NULL,
                        endpoint VARCHAR(255) NULL,
                        tokens_used INT NULL,
                        cost DECIMAL(10,6) NULL,
                        error_message TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_api_name (api_name),
                        INDEX idx_created (created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
            $this->markComplete('ensure_api_logs_table');
        }

        // Migration: Add Hugging Face API key setting
        if (!$this->hasRun('add_huggingface_settings')) {
            $this->addSetting('huggingface_api_key', '', 'encrypted', 'Hugging Face API key for Florence-2 and WD14 tagger');
            $this->markComplete('add_huggingface_settings');
        }
    }

    /**
     * Log error
     */
    private function logError(string $message): void
    {
        $this->log[] = "ERROR: {$message}";
        error_log("MigrationRunner: {$message}");
    }

    /**
     * Get log
     */
    public function getLog(): array
    {
        return $this->log;
    }
}
