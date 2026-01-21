<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Gallery Controller
 *
 * Handles gallery listing with pagination.
 */
class GalleryController extends Controller
{
    /**
     * Display gallery with pagination
     */
    public function index(string|int $page = 1): Response
    {
        $db = $this->db();

        $page = max(1, (int) $page);
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        // Get total count of published images
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM images WHERE status = 'published' AND moderation_status = 'approved'"
        );

        // Get images
        $images = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, thumbnail_webp_path, storage_path, webp_path, alt_text,
                    view_count, favorite_count, is_animated, created_at
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
             ORDER BY created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );

        $totalPages = (int) ceil($total / $perPage);

        // Get categories for sidebar
        $categories = $db->fetchAll(
            "SELECT c.id, c.name, c.slug, COUNT(ic.image_id) as image_count
             FROM categories c
             LEFT JOIN image_categories ic ON c.id = ic.category_id
             LEFT JOIN images i ON ic.image_id = i.id AND i.status = 'published'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );

        // Get popular tags
        $popularTags = $db->fetchAll(
            "SELECT id, name, slug, usage_count
             FROM tags
             WHERE usage_count > 0
             ORDER BY usage_count DESC
             LIMIT 20"
        );

        // Determine which template to use based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/gallery-pexels',
            default => 'frontend/gallery',
        };

        return $this->view($template, [
            'title' => 'Gallery',
            'meta_description' => 'Browse our collection of images across various categories.',
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'categories' => $categories,
            'popularTags' => $popularTags,
        ]);
    }

    /**
     * Redirect to a random image
     */
    public function random(): Response
    {
        $db = $this->db();

        $image = $db->fetch(
            "SELECT slug FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
             ORDER BY RAND()
             LIMIT 1"
        );

        if ($image) {
            return $this->redirect('/image/' . $image['slug']);
        }

        return $this->redirect('/gallery');
    }
}
