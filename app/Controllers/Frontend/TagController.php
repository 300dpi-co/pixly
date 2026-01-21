<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;

/**
 * Tag Controller
 *
 * Handles tag pages with image listing.
 */
class TagController extends Controller
{
    /**
     * Display tag with images
     */
    public function index(string $slug, string|int $page = 1): Response
    {
        $db = $this->db();

        // Get tag
        $tag = $db->fetch(
            "SELECT * FROM tags WHERE slug = :slug",
            ['slug' => $slug]
        );

        if (!$tag) {
            return $this->notFound('Tag not found');
        }

        $page = max(1, (int) $page);
        $perPage = config('pagination.per_page') ?? 24;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM images i
             JOIN image_tags it ON i.id = it.image_id
             WHERE it.tag_id = :tag_id
               AND i.status = 'published'
               AND i.moderation_status = 'approved'",
            ['tag_id' => $tag['id']]
        );

        // Get images
        $images = $db->fetchAll(
            "SELECT i.id, i.uuid, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path, i.storage_path, i.webp_path,
                    i.alt_text, i.view_count, i.favorite_count, i.is_animated, i.created_at
             FROM images i
             JOIN image_tags it ON i.id = it.image_id
             WHERE it.tag_id = :tag_id
               AND i.status = 'published'
               AND i.moderation_status = 'approved'
             ORDER BY i.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['tag_id' => $tag['id']]
        );

        $totalPages = (int) ceil($total / $perPage);

        // Get related tags
        $relatedTags = $db->fetchAll(
            "SELECT DISTINCT t.id, t.name, t.slug, t.usage_count
             FROM tags t
             JOIN image_tags it ON t.id = it.tag_id
             WHERE it.image_id IN (
                 SELECT image_id FROM image_tags WHERE tag_id = :tag_id
             )
             AND t.id != :tag_id2
             ORDER BY t.usage_count DESC
             LIMIT 15",
            ['tag_id' => $tag['id'], 'tag_id2' => $tag['id']]
        );

        // Select appropriate view template based on layout preset
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/tag-pexels',
            default => 'frontend/tag',
        };

        return $this->view($template, [
            'title' => "#{$tag['name']}",
            'meta_description' => "Browse images tagged with {$tag['name']}.",
            'tag' => $tag,
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'relatedTags' => $relatedTags,
        ]);
    }
}
