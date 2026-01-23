<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Appreciation;

/**
 * Home Controller
 *
 * Handles the homepage display.
 */
class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index(): Response
    {
        $db = $this->db();

        // Get featured images
        $featuredImages = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path, storage_path, webp_path, alt_text
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved' AND featured = 1
             ORDER BY featured_order ASC, created_at DESC
             LIMIT 6"
        );

        // Get trending images (most views in last 7 days)
        $trendingImages = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path, storage_path, webp_path, alt_text, view_count
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY view_count DESC, favorite_count DESC
             LIMIT 8"
        );

        // Get recent images
        $recentImages = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path, storage_path, webp_path, alt_text
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
             ORDER BY published_at DESC
             LIMIT 12"
        );

        // Get categories with image counts
        $categories = $db->fetchAll(
            "SELECT c.id, c.name, c.slug, c.description, COUNT(ic.image_id) as image_count
             FROM categories c
             LEFT JOIN image_categories ic ON c.id = ic.category_id
             LEFT JOIN images i ON ic.image_id = i.id AND i.status = 'published'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order, c.name
             LIMIT 8"
        );

        // Get trending tags
        $trendingTags = $db->fetchAll(
            "SELECT id, name, slug, usage_count, trend_score
             FROM tags
             WHERE usage_count > 0
             ORDER BY trend_score DESC, usage_count DESC
             LIMIT 12"
        );

        // Get stats for Pexels theme
        $stats = [
            'total_images' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE status = 'published'"),
            'total_users' => (int) $db->fetchColumn("SELECT COUNT(*) FROM users"),
            'total_downloads' => (int) $db->fetchColumn("SELECT COALESCE(SUM(download_count), 0) FROM images"),
        ];

        // Determine which template to use based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');

        // Get top contributors for Pexels theme (based on likes + appreciations)
        $topContributors = [];
        if ($layoutPreset === 'pexels-stock') {
            $topContributors = Appreciation::getTopContributors(8);
        }
        $template = match ($layoutPreset) {
            'magazine-grid' => 'frontend/home-magazine',
            'bold-modern' => 'frontend/home-bold',
            'dark-cinematic' => 'frontend/home-cinematic',
            'neon-nights' => 'frontend/home-neon',
            'premium-luxury' => 'frontend/home-luxury',
            'minimal-dark' => 'frontend/home-minimal-dark',
            'pexels-stock' => 'frontend/home-pexels',
            default => 'frontend/home',
        };

        return $this->view($template, [
            'title' => 'Welcome',
            'meta_description' => config('seo.site_description'),
            'featuredImages' => $featuredImages,
            'trendingImages' => $trendingImages,
            'recentImages' => $recentImages,
            'categories' => $categories,
            'trendingTags' => $trendingTags,
            'layoutPreset' => $layoutPreset,
            'stats' => $stats,
            'topContributors' => $topContributors,
        ]);
    }
}
