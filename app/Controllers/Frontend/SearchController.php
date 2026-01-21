<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Search Controller
 *
 * Handles search functionality.
 */
class SearchController extends Controller
{
    /**
     * Display search results
     */
    public function index(): Response
    {
        $db = $this->db();

        $query = trim($this->request->input('q', ''));
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        $images = [];
        $total = 0;
        $totalPages = 0;

        if (!empty($query)) {
            $searchTerm = "%{$query}%";

            // Get total count
            $total = (int) $db->fetchColumn(
                "SELECT COUNT(DISTINCT i.id)
                 FROM images i
                 LEFT JOIN image_tags it ON i.id = it.image_id
                 LEFT JOIN tags t ON it.tag_id = t.id
                 WHERE i.status = 'published'
                   AND i.moderation_status = 'approved'
                   AND (
                       i.title LIKE :q1
                       OR i.description LIKE :q2
                       OR i.ai_description LIKE :q3
                       OR t.name LIKE :q4
                   )",
                ['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm, 'q4' => $searchTerm]
            );

            // Get images
            $images = $db->fetchAll(
                "SELECT DISTINCT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                        i.alt_text, i.view_count, i.favorite_count, i.is_animated, i.created_at
                 FROM images i
                 LEFT JOIN image_tags it ON i.id = it.image_id
                 LEFT JOIN tags t ON it.tag_id = t.id
                 WHERE i.status = 'published'
                   AND i.moderation_status = 'approved'
                   AND (
                       i.title LIKE :q1
                       OR i.description LIKE :q2
                       OR i.ai_description LIKE :q3
                       OR t.name LIKE :q4
                   )
                 ORDER BY i.view_count DESC, i.created_at DESC
                 LIMIT {$perPage} OFFSET {$offset}",
                ['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm, 'q4' => $searchTerm]
            );

            $totalPages = (int) ceil($total / $perPage);

            // Log search query for analytics
            $this->logSearch($query, $total);
        }

        // Get popular searches (gracefully handle if table doesn't exist)
        $popularSearches = [];
        try {
            $popularSearches = $db->fetchAll(
                "SELECT query, search_count
                 FROM search_logs
                 GROUP BY query
                 ORDER BY search_count DESC
                 LIMIT 10"
            );
        } catch (\PDOException $e) {
            // Table doesn't exist yet - that's okay
        }

        // Select appropriate view template based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/search-pexels',
            default => 'frontend/search',
        };

        return $this->view($template, [
            'title' => $query ? "Search: {$query}" : 'Search',
            'meta_description' => $query ? "Search results for '{$query}'" : 'Search our image gallery',
            'query' => $query,
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'popularSearches' => $popularSearches,
        ]);
    }

    /**
     * Log search query for analytics
     */
    private function logSearch(string $query, int $resultCount): void
    {
        try {
            $db = $this->db();

            // Check if query exists
            $existing = $db->fetch(
                "SELECT id, search_count FROM search_logs WHERE query = :query",
                ['query' => $query]
            );

            if ($existing) {
                $db->execute(
                    "UPDATE search_logs SET search_count = search_count + 1, last_searched_at = NOW() WHERE id = :id",
                    ['id' => $existing['id']]
                );
            } else {
                $db->insert('search_logs', [
                    'query' => $query,
                    'result_count' => $resultCount,
                    'search_count' => 1,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail logging
        }
    }
}
