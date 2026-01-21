<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Appreciation;

/**
 * Trending Controller
 *
 * Displays trending images based on views, favorites, and recency.
 * Optimized for performance with proper indexing and efficient queries.
 */
class TrendingController extends Controller
{
    /**
     * Display trending images
     */
    public function index(): Response
    {
        $db = $this->db();

        $period = $this->request->input('period', 'week');
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        // Determine date filter with optimized conditions
        [$dateCondition, $dateParams] = $this->getDateFilter($period);

        // Get total count (optimized - uses covering index)
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM images
             WHERE status = 'published'
               AND moderation_status = 'approved'
               AND {$dateCondition}",
            $dateParams
        );

        // Get trending images with optimized query
        // Uses pre-calculated score to avoid complex calculations in ORDER BY
        $images = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path,
                    storage_path, webp_path, medium_path, medium_webp_path,
                    alt_text, view_count, favorite_count, appreciate_count,
                    download_count, is_animated, created_at, user_id,
                    (view_count + (favorite_count * 5) + (COALESCE(appreciate_count, 0) * 3)) as engagement_score
             FROM images
             WHERE status = 'published'
               AND moderation_status = 'approved'
               AND {$dateCondition}
             ORDER BY engagement_score DESC, view_count DESC, created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $dateParams
        );

        // Get user info for images (single query instead of N+1)
        $userIds = array_unique(array_filter(array_column($images, 'user_id')));
        $users = [];
        if (!empty($userIds)) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $userRows = $db->fetchAll(
                "SELECT id, username, display_name, avatar_path
                 FROM users WHERE id IN ({$placeholders})",
                array_values($userIds)
            );
            foreach ($userRows as $user) {
                $users[$user['id']] = $user;
            }
        }

        // Attach user info to images
        foreach ($images as &$image) {
            $image['user'] = $users[$image['user_id']] ?? null;
        }
        unset($image);

        $totalPages = (int) ceil($total / $perPage);

        // Get trending tags (optimized with limit)
        $trendingTags = $db->fetchAll(
            "SELECT t.id, t.name, t.slug, t.trend_score,
                    COUNT(DISTINCT it.image_id) as recent_count
             FROM tags t
             INNER JOIN image_tags it ON t.id = it.tag_id
             INNER JOIN images i ON it.image_id = i.id
             WHERE i.status = 'published'
               AND i.moderation_status = 'approved'
               AND i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY t.id, t.name, t.slug, t.trend_score
             ORDER BY recent_count DESC, t.trend_score DESC
             LIMIT 15"
        );

        // Determine layout preset for themed view
        $layoutPreset = setting('layout_preset', 'clean-minimal');

        // Get top contributors for Pexels theme
        $topContributors = [];
        if ($layoutPreset === 'pexels-stock') {
            $topContributors = Appreciation::getTopContributors(6);
        }

        // Select appropriate view template
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/trending-pexels',
            'dark-cinematic' => 'frontend/trending-cinematic',
            default => 'frontend/trending',
        };

        return $this->view($template, [
            'title' => 'Trending Images',
            'meta_description' => 'Discover the hottest trending images right now.',
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'period' => $period,
            'trendingTags' => $trendingTags,
            'topContributors' => $topContributors,
            'layoutPreset' => $layoutPreset,
        ]);
    }

    /**
     * Get optimized date filter condition
     */
    private function getDateFilter(string $period): array
    {
        return match ($period) {
            'today' => [
                "created_at >= :date_start",
                ['date_start' => date('Y-m-d 00:00:00')]
            ],
            'week' => [
                "created_at >= :date_start",
                ['date_start' => date('Y-m-d H:i:s', strtotime('-7 days'))]
            ],
            'month' => [
                "created_at >= :date_start",
                ['date_start' => date('Y-m-d H:i:s', strtotime('-30 days'))]
            ],
            'all' => [
                "1=1",
                []
            ],
            default => [
                "created_at >= :date_start",
                ['date_start' => date('Y-m-d H:i:s', strtotime('-7 days'))]
            ],
        };
    }
}
