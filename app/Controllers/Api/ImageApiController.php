<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

/**
 * Image API Controller
 *
 * Handles image API requests.
 */
class ImageApiController extends Controller
{
    /**
     * List images (paginated)
     */
    public function index(): Response
    {
        $db = $this->db();

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = min(100, max(1, (int) $this->request->input('per_page', 24)));
        $offset = ($page - 1) * $perPage;

        $category = $this->request->input('category');
        $tag = $this->request->input('tag');

        $where = ["i.status = 'published'", "i.moderation_status = 'approved'"];
        $params = [];
        $joins = '';

        if ($category) {
            $joins .= " JOIN image_categories ic ON i.id = ic.image_id JOIN categories c ON ic.category_id = c.id";
            $where[] = "c.slug = :category";
            $params['category'] = $category;
        }

        if ($tag) {
            $joins .= " JOIN image_tags it ON i.id = it.image_id JOIN tags t ON it.tag_id = t.id";
            $where[] = "t.slug = :tag";
            $params['tag'] = $tag;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Get total
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(DISTINCT i.id) FROM images i {$joins} {$whereClause}",
            $params
        );

        // Get images
        $images = $db->fetchAll(
            "SELECT DISTINCT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.description, i.view_count, i.favorite_count, i.created_at
             FROM images i
             {$joins}
             {$whereClause}
             ORDER BY i.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Transform for API response
        $data = array_map(function ($img) {
            return [
                'id' => $img['uuid'],
                'title' => $img['title'],
                'slug' => $img['slug'],
                'description' => $img['description'],
                'alt_text' => $img['alt_text'],
                'thumbnail_url' => url('/uploads/' . ($img['thumbnail_webp_path'] ?: $img['thumbnail_path'])),
                'image_url' => url('/uploads/' . ($img['webp_path'] ?: $img['storage_path'])),
                'views' => (int) $img['view_count'],
                'favorites' => (int) $img['favorite_count'],
                'created_at' => $img['created_at'],
            ];
        }, $images);

        return $this->json([
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get single image
     */
    public function show(string $slug): Response
    {
        $db = $this->db();

        $image = $db->fetch(
            "SELECT * FROM images WHERE slug = :slug AND status = 'published'",
            ['slug' => $slug]
        );

        if (!$image) {
            return $this->json(['error' => 'Image not found'], 404);
        }

        // Get categories
        $categories = $db->fetchAll(
            "SELECT c.name, c.slug FROM categories c
             JOIN image_categories ic ON c.id = ic.category_id
             WHERE ic.image_id = :id",
            ['id' => $image['id']]
        );

        // Get tags
        $tags = $db->fetchAll(
            "SELECT t.name, t.slug FROM tags t
             JOIN image_tags it ON t.id = it.tag_id
             WHERE it.image_id = :id",
            ['id' => $image['id']]
        );

        return $this->json([
            'data' => [
                'id' => $image['uuid'],
                'title' => $image['title'],
                'slug' => $image['slug'],
                'description' => $image['description'] ?: $image['ai_description'],
                'alt_text' => $image['alt_text'],
                'thumbnail_url' => url('/uploads/' . ($image['thumbnail_webp_path'] ?: $image['thumbnail_path'])),
                'image_url' => url('/uploads/' . ($image['webp_path'] ?: $image['storage_path'])),
                'width' => $image['width'],
                'height' => $image['height'],
                'views' => (int) $image['view_count'],
                'favorites' => (int) $image['favorite_count'],
                'categories' => $categories,
                'tags' => $tags,
                'created_at' => $image['created_at'],
            ],
        ]);
    }
}
