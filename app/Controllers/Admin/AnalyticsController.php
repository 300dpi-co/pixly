<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();

        // Overview stats
        $stats = [
            'total_images' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images"),
            'published_images' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE status = 'published'"),
            'total_views' => (int) $db->fetchColumn("SELECT COALESCE(SUM(view_count), 0) FROM images"),
            'total_favorites' => (int) $db->fetchColumn("SELECT COUNT(*) FROM favorites"),
            'total_comments' => (int) $db->fetchColumn("SELECT COUNT(*) FROM comments"),
            'total_users' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users"),
            'active_users' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE status = 'active'"),
        ];

        // Views this week vs last week
        $viewsThisWeek = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM page_views WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
        $viewsLastWeek = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM page_views WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND created_at < DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
        $stats['views_this_week'] = $viewsThisWeek;
        $stats['views_growth'] = $viewsLastWeek > 0 ? round((($viewsThisWeek - $viewsLastWeek) / $viewsLastWeek) * 100, 1) : 0;

        // Top images by views
        $topImages = $db->fetchAll(
            "SELECT id, title, slug, thumbnail_path, view_count, favorite_count
             FROM images
             WHERE status = 'published'
             ORDER BY view_count DESC
             LIMIT 10"
        );

        // Top categories
        $topCategories = $db->fetchAll(
            "SELECT c.name, c.slug, COUNT(ic.image_id) as image_count,
                    SUM(i.view_count) as total_views
             FROM categories c
             LEFT JOIN image_categories ic ON c.id = ic.category_id
             LEFT JOIN images i ON ic.image_id = i.id
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY total_views DESC
             LIMIT 5"
        );

        // Top tags
        $topTags = $db->fetchAll(
            "SELECT name, slug, usage_count, trend_score
             FROM tags
             WHERE usage_count > 0
             ORDER BY usage_count DESC
             LIMIT 10"
        );

        // Recent activity (views per day for last 14 days)
        $dailyViews = $db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as views
             FROM page_views
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );

        // Top search queries
        $topSearches = $db->fetchAll(
            "SELECT query, search_count, result_count
             FROM search_logs
             ORDER BY search_count DESC
             LIMIT 10"
        );

        // Recent uploads
        $recentUploads = $db->fetchAll(
            "SELECT id, title, slug, thumbnail_path, created_at, status
             FROM images
             ORDER BY created_at DESC
             LIMIT 5"
        );

        // API usage
        $apiStats = $db->fetch(
            "SELECT COUNT(*) as total_calls,
                    SUM(tokens_used) as total_tokens,
                    COUNT(CASE WHEN error_message IS NOT NULL THEN 1 END) as errors
             FROM api_logs
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );

        // Google Analytics settings
        $gaSettings = [
            'measurement_id' => setting('google_analytics_id', ''),
            'enabled' => !empty(setting('google_analytics_id')),
        ];

        return $this->view('admin/analytics/index', [
            'title' => 'Analytics',
            'currentPage' => 'analytics',
            'stats' => $stats,
            'topImages' => $topImages,
            'topCategories' => $topCategories,
            'topTags' => $topTags,
            'dailyViews' => $dailyViews,
            'topSearches' => $topSearches,
            'recentUploads' => $recentUploads,
            'apiStats' => $apiStats,
            'gaSettings' => $gaSettings,
        ], 'admin');
    }

    /**
     * Update Google Analytics settings
     */
    public function updateGoogleAnalytics(): Response
    {
        $db = $this->db();
        $measurementId = trim($_POST['google_analytics_id'] ?? '');

        // Validate format (G-XXXXXXXXXX or UA-XXXXXXXX-X)
        if (!empty($measurementId) && !preg_match('/^(G-[A-Z0-9]+|UA-\d+-\d+)$/i', $measurementId)) {
            session_flash('error', 'Invalid Google Analytics ID format. Use G-XXXXXXXXXX or UA-XXXXXXXX-X');
            return Response::redirect('/admin/analytics');
        }

        // Update or insert setting
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = 'google_analytics_id'");

        if ($existing) {
            $db->update('settings', ['setting_value' => $measurementId], 'setting_key = :key', ['key' => 'google_analytics_id']);
        } else {
            $db->insert('settings', [
                'setting_key' => 'google_analytics_id',
                'setting_value' => $measurementId,
                'setting_type' => 'string',
                'description' => 'Google Analytics Measurement ID (G-XXXXXXXXXX)',
                'is_public' => 0,
            ]);
        }

        // Clear all settings cache
        clear_settings_cache();

        if ($measurementId) {
            session_flash('success', 'Google Analytics connected successfully.');
        } else {
            session_flash('success', 'Google Analytics disconnected.');
        }

        return Response::redirect('/admin/analytics');
    }
}
