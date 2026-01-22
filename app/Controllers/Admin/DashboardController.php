<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\UpdateChecker;
use App\Services\MigrationRunner;

/**
 * Admin Dashboard Controller
 */
class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(): Response
    {
        // Run migrations on every dashboard load (backup in case middleware fails)
        $this->runMigrations();

        $db = $this->db();

        // Get update info (silent, never fails)
        $updateInfo = $this->getUpdateInfo();

        // Get stats
        $stats = [
            'total_images' => (int) $db->fetchColumn('SELECT COUNT(*) FROM images'),
            'pending_moderation' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE moderation_status = 'pending'"),
            'published_images' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE status = 'published'"),
            'total_users' => (int) $db->fetchColumn('SELECT COUNT(*) FROM users'),
            'total_categories' => (int) $db->fetchColumn('SELECT COUNT(*) FROM categories'),
            'total_tags' => (int) $db->fetchColumn('SELECT COUNT(*) FROM tags'),
        ];

        // Recent images
        $recentImages = $db->fetchAll(
            "SELECT id, title, slug, thumbnail_path, status, moderation_status, created_at
             FROM images
             ORDER BY created_at DESC
             LIMIT 5"
        );

        // Recent users
        $recentUsers = $db->fetchAll(
            "SELECT id, username, email, role, status, created_at
             FROM users
             ORDER BY created_at DESC
             LIMIT 5"
        );

        return $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'recentImages' => $recentImages,
            'recentUsers' => $recentUsers,
            'updateInfo' => $updateInfo,
        ], 'admin');
    }

    /**
     * Perform silent update check and return update info
     * Never throws, never affects page load
     */
    private function getUpdateInfo(): array
    {
        try {
            $checker = new UpdateChecker();
            $checker->silentCheck();

            return [
                'available' => $checker->hasUpdate(),
                'latest_version' => $checker->getLatestVersion(),
                'current_version' => file_exists(ROOT_PATH . '/VERSION')
                    ? trim(file_get_contents(ROOT_PATH . '/VERSION'))
                    : '1.0.0',
                'announcement' => $checker->getAnnouncement(),
            ];
        } catch (\Throwable $e) {
            return ['available' => false];
        }
    }

    /**
     * Run database migrations silently
     */
    private function runMigrations(): void
    {
        try {
            $runner = new MigrationRunner();
            $runner->runPending();
        } catch (\Throwable $e) {
            // Silent fail - log for debugging
            error_log('Dashboard migration error: ' . $e->getMessage());
        }
    }

    /**
     * Diagnostic endpoint to check migration status
     */
    public function diagnostics(): Response
    {
        $db = $this->db();
        $results = [];

        // Check migrations table
        try {
            $migrations = $db->fetchAll("SELECT * FROM migrations ORDER BY id");
            $results['migrations_table'] = $migrations ?: 'empty';
        } catch (\Throwable $e) {
            $results['migrations_table'] = 'ERROR: ' . $e->getMessage();
        }

        // Check is_trusted column
        try {
            $col = $db->fetch(
                "SELECT 1 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_trusted'"
            );
            $results['users.is_trusted'] = $col ? 'EXISTS' : 'MISSING';
        } catch (\Throwable $e) {
            $results['users.is_trusted'] = 'ERROR: ' . $e->getMessage();
        }

        // Check AI settings
        try {
            $settings = $db->fetchAll(
                "SELECT setting_key FROM settings WHERE setting_key IN ('ai_provider', 'replicate_api_key', 'claude_api_key')"
            );
            $results['ai_settings'] = $settings ?: 'MISSING';
        } catch (\Throwable $e) {
            $results['ai_settings'] = 'ERROR: ' . $e->getMessage();
        }

        // Run migrations now and show log
        try {
            $runner = new MigrationRunner();
            $runner->runPending();
            $results['migration_log'] = $runner->getLog() ?: 'No actions taken';
        } catch (\Throwable $e) {
            $results['migration_log'] = 'ERROR: ' . $e->getMessage();
        }

        return $this->json($results);
    }
}
