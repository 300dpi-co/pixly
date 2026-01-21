<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

/**
 * Search API Controller
 *
 * Handles search API requests.
 */
class SearchApiController extends Controller
{
    /**
     * Search images
     */
    public function index(): Response
    {
        $db = $this->db();

        $query = trim($this->request->input('q', ''));
        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = min(100, max(1, (int) $this->request->input('per_page', 24)));
        $offset = ($page - 1) * $perPage;

        if (empty($query)) {
            return $this->json([
                'data' => [],
                'meta' => ['total' => 0, 'page' => $page, 'per_page' => $perPage, 'total_pages' => 0],
            ]);
        }

        $searchTerm = "%{$query}%";

        // Get total
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(DISTINCT i.id)
             FROM images i
             LEFT JOIN image_tags it ON i.id = it.image_id
             LEFT JOIN tags t ON it.tag_id = t.id
             WHERE i.status = 'published'
               AND i.moderation_status = 'approved'
               AND (i.title LIKE :q1 OR i.description LIKE :q2 OR t.name LIKE :q3)",
            ['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm]
        );

        // Get images
        $images = $db->fetchAll(
            "SELECT DISTINCT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.description, i.view_count, i.favorite_count, i.created_at
             FROM images i
             LEFT JOIN image_tags it ON i.id = it.image_id
             LEFT JOIN tags t ON it.tag_id = t.id
             WHERE i.status = 'published'
               AND i.moderation_status = 'approved'
               AND (i.title LIKE :q1 OR i.description LIKE :q2 OR t.name LIKE :q3)
             ORDER BY i.view_count DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['q1' => $searchTerm, 'q2' => $searchTerm, 'q3' => $searchTerm]
        );

        $data = array_map(function ($img) {
            return [
                'id' => $img['uuid'],
                'title' => $img['title'],
                'slug' => $img['slug'],
                'thumbnail_url' => url('/uploads/' . ($img['thumbnail_webp_path'] ?: $img['thumbnail_path'])),
                'image_url' => url('/uploads/' . ($img['webp_path'] ?: $img['storage_path'])),
                'views' => (int) $img['view_count'],
            ];
        }, $images);

        return $this->json([
            'data' => $data,
            'meta' => [
                'query' => $query,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }
}
