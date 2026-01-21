<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\UpdateChecker;

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
}
