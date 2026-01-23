<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Category Controller
 *
 * Handles category pages with image listing.
 */
class CategoryController extends Controller
{
    /**
     * Display category with images
     */
    public function index(string $slug, string|int $page = 1): Response
    {
        $db = $this->db();

        // Get category
        $category = $db->fetch(
            "SELECT * FROM categories WHERE slug = :slug AND is_active = 1",
            ['slug' => $slug]
        );

        if (!$category) {
            return $this->notFound('Category not found');
        }

        $page = max(1, (int) $page);
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM images i
             JOIN image_categories ic ON i.id = ic.image_id
             WHERE ic.category_id = :cat_id
               AND i.status = 'published'
               AND i.moderation_status = 'approved'",
            ['cat_id' => $category['id']]
        );

        // Get images
        $images = $db->fetchAll(
            "SELECT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.view_count, i.favorite_count, i.is_animated, i.created_at
             FROM images i
             JOIN image_categories ic ON i.id = ic.image_id
             WHERE ic.category_id = :cat_id
               AND i.status = 'published'
               AND i.moderation_status = 'approved'
             ORDER BY i.published_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['cat_id' => $category['id']]
        );

        $totalPages = (int) ceil($total / $perPage);

        // Get all categories for sidebar
        $allCategories = $db->fetchAll(
            "SELECT c.id, c.name, c.slug, COUNT(ic.image_id) as image_count
             FROM categories c
             LEFT JOIN image_categories ic ON c.id = ic.category_id
             LEFT JOIN images i ON ic.image_id = i.id AND i.status = 'published'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order, c.name"
        );

        // Select appropriate view template based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/category-pexels',
            default => 'frontend/category',
        };

        return $this->view($template, [
            'title' => $category['name'],
            'meta_description' => $category['meta_description'] ?: "Browse {$category['name']} images in our gallery.",
            'category' => $category,
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'categories' => $allCategories,
        ]);
    }
}
