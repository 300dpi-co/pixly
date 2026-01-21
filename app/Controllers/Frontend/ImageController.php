<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Like;
use App\Models\Appreciation;

/**
 * Image Controller (Frontend)
 *
 * Handles single image display with prev/next navigation.
 */
class ImageController extends Controller
{
    /**
     * Display single image
     */
    public function show(string $slug): Response
    {
        $db = $this->db();

        // Get image by slug
        $image = $db->fetch(
            "SELECT * FROM images WHERE slug = :slug AND status = 'published'",
            ['slug' => $slug]
        );

        if (!$image) {
            return $this->notFound('Image not found');
        }

        // Increment view count
        $db->execute(
            "UPDATE images SET view_count = view_count + 1 WHERE id = :id",
            ['id' => $image['id']]
        );

        // Get image categories
        $categories = $db->fetchAll(
            "SELECT c.id, c.name, c.slug
             FROM categories c
             JOIN image_categories ic ON c.id = ic.category_id
             WHERE ic.image_id = :id",
            ['id' => $image['id']]
        );

        // Get image tags
        $tags = $db->fetchAll(
            "SELECT t.id, t.name, t.slug
             FROM tags t
             JOIN image_tags it ON t.id = it.tag_id
             WHERE it.image_id = :id",
            ['id' => $image['id']]
        );

        // Get previous image (older)
        $prevImage = $db->fetch(
            "SELECT slug, title, thumbnail_path, thumbnail_webp_path
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
               AND created_at < :created
             ORDER BY created_at DESC
             LIMIT 1",
            ['created' => $image['created_at']]
        );

        // Get next image (newer)
        $nextImage = $db->fetch(
            "SELECT slug, title, thumbnail_path, thumbnail_webp_path
             FROM images
             WHERE status = 'published' AND moderation_status = 'approved'
               AND created_at > :created
             ORDER BY created_at ASC
             LIMIT 1",
            ['created' => $image['created_at']]
        );

        // Get related images (same category or tags)
        $relatedImages = [];
        if (!empty($categories)) {
            $categoryIds = array_column($categories, 'id');
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $relatedImages = $db->fetchAll(
                "SELECT DISTINCT i.id, i.title, i.slug, i.thumbnail_path, i.thumbnail_webp_path
                 FROM images i
                 JOIN image_categories ic ON i.id = ic.image_id
                 WHERE ic.category_id IN ({$placeholders})
                   AND i.id != ?
                   AND i.status = 'published'
                 ORDER BY RAND()
                 LIMIT 6",
                [...$categoryIds, $image['id']]
            );
        }

        // Check if liked (works for guests too)
        $userId = $_SESSION['user_id'] ?? null;
        $isLiked = Like::hasLiked($userId, 'image', $image['id']);

        // Check if favorited by current user (only for logged-in users)
        $isFavorited = false;
        $isAppreciated = false;
        if (isset($_SESSION['user_id'])) {
            $favorite = $db->fetch(
                "SELECT 1 FROM favorites WHERE user_id = :user_id AND image_id = :image_id",
                ['user_id' => $_SESSION['user_id'], 'image_id' => $image['id']]
            );
            $isFavorited = (bool) $favorite;

            // Check if appreciated (for Pexels theme)
            $isAppreciated = Appreciation::hasAppreciated($_SESSION['user_id'], $image['id']);
        }

        // Get layout preset to check if Pexels theme is active
        $layoutPreset = setting('layout_preset', 'clean-minimal');
        $isPexelsTheme = ($layoutPreset === 'pexels-stock');

        // Check if current user owns this image (can't appreciate own images)
        $isOwnImage = isset($_SESSION['user_id']) && $image['user_id'] == $_SESSION['user_id'];

        // Get approved comments
        $comments = $db->fetchAll(
            "SELECT c.id, c.content, c.created_at, c.parent_id, u.username, u.avatar_path
             FROM comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.image_id = :image_id AND c.status = 'approved'
             ORDER BY c.created_at ASC",
            ['image_id' => $image['id']]
        );

        // Build Schema.org JSON-LD
        $schemaData = $this->buildSchemaData($image, $categories, $tags);

        // Select appropriate view template based on layout preset
        $template = match ($layoutPreset) {
            'pexels-stock' => 'frontend/image-pexels',
            'dark-cinematic' => 'frontend/image',
            default => 'frontend/image',
        };

        return $this->view($template, [
            'title' => $image['title'],
            'meta_description' => $image['description'] ?: $image['ai_description'] ?: "View {$image['title']} in our gallery.",
            'og_image' => url('/uploads/' . $image['storage_path']),
            'image' => $image,
            'categories' => $categories,
            'tags' => $tags,
            'prevImage' => $prevImage,
            'nextImage' => $nextImage,
            'relatedImages' => $relatedImages,
            'isFavorited' => $isFavorited,
            'isLiked' => $isLiked,
            'isAppreciated' => $isAppreciated,
            'isPexelsTheme' => $isPexelsTheme,
            'isOwnImage' => $isOwnImage,
            'isLoggedIn' => $this->isAuthenticated(),
            'schemaData' => $schemaData,
            'comments' => $comments,
        ]);
    }

    /**
     * Build Schema.org JSON-LD data
     */
    private function buildSchemaData(array $image, array $categories, array $tags): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ImageObject',
            'name' => $image['title'],
            'description' => $image['description'] ?: $image['ai_description'] ?: '',
            'contentUrl' => url('/uploads/' . $image['storage_path']),
            'thumbnailUrl' => url('/uploads/' . $image['thumbnail_path']),
            'uploadDate' => $image['created_at'],
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
        ];

        if ($image['width'] && $image['height']) {
            $schema['width'] = $image['width'];
            $schema['height'] = $image['height'];
        }

        if (!empty($tags)) {
            $schema['keywords'] = implode(', ', array_column($tags, 'name'));
        }

        return $schema;
    }
}
