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
            $this->addSetting('ai_provider', 'huggingface', 'string', 'AI provider (huggingface, replicate, or claude)');
            $this->addSetting('huggingface_api_key', '', 'encrypted', 'Hugging Face API key for Florence-2 + WD14');
            $this->addSetting('replicate_api_key', '', 'encrypted', 'Replicate API key for LLaVA image analysis');
            $this->addSetting('claude_api_key', '', 'encrypted', 'Claude API key for image analysis');
            $this->markComplete('add_ai_settings');
        }

        // Migration: Clean up old unused API keys and add HuggingFace
        if (!$this->hasRun('cleanup_ai_settings_v2')) {
            // Remove old unused keys
            $this->db->execute("DELETE FROM settings WHERE setting_key IN ('deepseek_api_key', 'deepinfra_api_key')");
            // Add HuggingFace if not exists
            $this->addSetting('huggingface_api_key', '', 'encrypted', 'Hugging Face API key for Florence-2 + WD14');
            $this->markComplete('cleanup_ai_settings_v2');
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

        // Migration: Add AI Horde settings (replaces HuggingFace)
        if (!$this->hasRun('add_aihorde_settings')) {
            $this->addSetting('aihorde_api_key', '', 'encrypted', 'AI Horde API key for free image analysis');
            // Update default provider to aihorde
            $this->db->execute(
                "UPDATE settings SET setting_value = 'aihorde' WHERE setting_key = 'ai_provider'"
            );
            $this->markComplete('add_aihorde_settings');
        }

        // Migration: Add OpenRouter settings (recommended - fast & cheap)
        if (!$this->hasRun('add_openrouter_settings')) {
            $this->addSetting('openrouter_api_key', '', 'encrypted', 'OpenRouter API key for Qwen 2.5 VL image analysis');
            $this->markComplete('add_openrouter_settings');
        }

        // Migration: Add scheduling columns to images table
        if (!$this->hasRun('add_image_scheduling_columns')) {
            $this->addColumn('images', 'scheduled_at', 'DATETIME NULL AFTER published_at');
            $this->addColumn('images', 'queue_type', "ENUM('fast','scheduled','moderation') DEFAULT NULL");
            $this->addColumn('images', 'batch_id', 'INT UNSIGNED DEFAULT NULL');
            $this->markComplete('add_image_scheduling_columns');
        }

        // Migration: Update images status ENUM to include new statuses
        if (!$this->hasRun('update_images_status_enum')) {
            try {
                // MySQL allows modifying ENUM to add new values
                $this->db->execute("
                    ALTER TABLE images MODIFY COLUMN status
                    ENUM('draft','processing','queued','scheduled','published','archived','deleted','rejected')
                    DEFAULT 'draft'
                ");
                $this->log[] = "Updated images.status ENUM with new values";
            } catch (\Throwable $e) {
                $this->logError("Failed to update images.status ENUM: " . $e->getMessage());
            }
            $this->markComplete('update_images_status_enum');
        }

        // Migration: Add queue_type column to ai_processing_queue
        if (!$this->hasRun('add_queue_type_to_ai_queue')) {
            $this->addColumn('ai_processing_queue', 'queue_type', "ENUM('fast','scheduled') DEFAULT 'fast'");
            $this->markComplete('add_queue_type_to_ai_queue');
        }

        // Migration: Create upload_batches table
        if (!$this->hasRun('create_upload_batches_table')) {
            if (!$this->tableExists('upload_batches')) {
                try {
                    $this->db->execute("
                        CREATE TABLE upload_batches (
                            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            uuid CHAR(36) NOT NULL UNIQUE,
                            user_id INT UNSIGNED NOT NULL,
                            total_images INT UNSIGNED DEFAULT 0,
                            processed_images INT UNSIGNED DEFAULT 0,
                            schedule_type ENUM('scheduled','auto_publish') NOT NULL,
                            scheduled_start_at DATETIME NULL,
                            publish_interval_minutes INT UNSIGNED DEFAULT 4,
                            status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
                            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                            completed_at DATETIME NULL,
                            INDEX idx_status (status),
                            INDEX idx_user (user_id)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ");
                    $this->log[] = "Created upload_batches table";
                } catch (\Throwable $e) {
                    $this->logError("Failed to create upload_batches table: " . $e->getMessage());
                }
            }
            $this->markComplete('create_upload_batches_table');
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
