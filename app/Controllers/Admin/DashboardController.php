<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

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
        ], 'admin');
    }
}
