<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Metadata Generator
 *
 * Uses AI to generate SEO-optimized metadata for images.
 */
class MetadataGenerator
{
    private DeepSeekService $ai;
    private array $errors = [];

    public function __construct()
    {
        $this->ai = new DeepSeekService();
    }

    /**
     * Process an image and update its metadata
     */
    public function processImage(int $imageId): bool
    {
        $this->errors = [];
        $db = app()->getDatabase();

        // Get image record
        $image = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $imageId]);

        if (!$image) {
            $this->errors[] = 'Image not found';
            return false;
        }

        // Get full path to image
        $imagePath = \ROOT_PATH . '/public_html/uploads/' . $image['storage_path'];

        if (!file_exists($imagePath)) {
            $this->errors[] = 'Image file not found: ' . $image['storage_path'];
            return false;
        }

        // Analyze image with AI (pass existing title for context)
        $analysis = $this->ai->analyzeImage($imagePath, $image['title']);

        if (!$analysis) {
            $this->errors[] = 'AI analysis failed: ' . ($this->ai->getLastError()['message'] ?? 'Unknown error');
            return false;
        }

        // Update image record with AI-generated data
        $updates = [
            'ai_description' => $analysis['description'] ?? null,
            'ai_tags' => json_encode($analysis['tags'] ?? []),
            'ai_category_suggestions' => json_encode([$analysis['category'] ?? null]),
            'ai_processed_at' => date('Y-m-d H:i:s'),
            'moderation_score' => $analysis['safety_score'] ?? null,
        ];

        // Optionally update main fields if they're empty or generic
        if (empty($image['description']) && !empty($analysis['description'])) {
            $updates['description'] = $analysis['description'];
        }

        // Update title if it's just the filename
        $titleUpdated = false;
        if ($this->isGenericTitle($image['title']) && !empty($analysis['title'])) {
            $updates['title'] = $analysis['title'];
            $updates['alt_text'] = $analysis['alt_text'] ?? $analysis['title'];
            $titleUpdated = true;
        }

        // Generate clean SEO slug from the final title
        $finalTitle = $updates['title'] ?? $image['title'];
        $newSlug = $this->generateUniqueSlug($finalTitle, $imageId, $db);
        if ($newSlug !== $image['slug']) {
            $updates['slug'] = $newSlug;
        }

        // Auto-publish if moderation is approved (AI processing complete)
        if ($image['moderation_status'] === 'approved') {
            $updates['status'] = 'published';
            $updates['published_at'] = date('Y-m-d H:i:s');
        }

        $db->update('images', $updates, 'id = :where_id', ['where_id' => $imageId]);

        // Process tags
        if (!empty($analysis['tags'])) {
            $this->syncAiTags($imageId, $analysis['tags']);
        }

        // Suggest category
        if (!empty($analysis['category'])) {
            $this->suggestCategory($imageId, $analysis['category']);
        }

        return true;
    }

    /**
     * Process multiple images from queue
     */
    public function processQueue(int $limit = 10): array
    {
        $db = app()->getDatabase();

        // Get pending items from queue
        $limit = (int) $limit;
        $queue = $db->fetchAll(
            "SELECT q.*, i.storage_path
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.status = 'pending'
             ORDER BY q.priority DESC, q.scheduled_at ASC
             LIMIT {$limit}"
        );

        $results = [
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($queue as $item) {
            // Mark as processing
            $db->update('ai_processing_queue', [
                'status' => 'processing',
                'started_at' => date('Y-m-d H:i:s'),
                'attempts' => $item['attempts'] + 1,
            ], 'id = :where_id', ['where_id' => $item['id']]);

            // Process the image
            $success = $this->processImage($item['image_id']);

            if ($success) {
                $db->update('ai_processing_queue', [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                ], 'id = :where_id', ['where_id' => $item['id']]);
                $results['processed']++;
            } else {
                $errorMessage = implode('; ', $this->errors);
                $newStatus = $item['attempts'] + 1 >= $item['max_attempts'] ? 'failed' : 'pending';

                $db->update('ai_processing_queue', [
                    'status' => $newStatus,
                    'error_message' => $errorMessage,
                ], 'id = :where_id', ['where_id' => $item['id']]);

                $results['failed']++;
                $results['errors'][] = "Image {$item['image_id']}: {$errorMessage}";
            }
        }

        return $results;
    }

    /**
     * Check if title is generic (just filename)
     */
    private function isGenericTitle(string $title): bool
    {
        // Check if title looks like a filename or is very short
        $generic = preg_match('/^(image|img|photo|pic|dsc|screenshot|untitled)/i', $title);
        $hasExtension = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $title);

        return $generic || $hasExtension || strlen($title) < 5;
    }

    /**
     * Sync AI-generated tags
     */
    private function syncAiTags(int $imageId, array $tagNames): void
    {
        $db = app()->getDatabase();

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            // Find or create tag
            $tag = $db->fetch("SELECT id FROM tags WHERE name = :name", ['name' => $tagName]);

            if (!$tag) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $tagName));
                $db->insert('tags', [
                    'name' => $tagName,
                    'slug' => $slug,
                ]);
                $tagId = (int) $db->lastInsertId();
            } else {
                $tagId = (int) $tag['id'];
            }

            // Check if already linked
            $existing = $db->fetch(
                "SELECT 1 FROM image_tags WHERE image_id = :image_id AND tag_id = :tag_id",
                ['image_id' => $imageId, 'tag_id' => $tagId]
            );

            if (!$existing) {
                $db->insert('image_tags', [
                    'image_id' => $imageId,
                    'tag_id' => $tagId,
                    'source' => 'ai',
                    'relevance_score' => 0.9,
                ]);

                // Update usage count
                $db->execute("UPDATE tags SET usage_count = usage_count + 1 WHERE id = :id", ['id' => $tagId]);
            }
        }
    }

    /**
     * Suggest category based on AI analysis
     */
    private function suggestCategory(int $imageId, string $categoryName): void
    {
        $db = app()->getDatabase();

        // Find matching category
        $category = $db->fetch(
            "SELECT id FROM categories WHERE name = :name OR slug = :slug",
            ['name' => $categoryName, 'slug' => strtolower($categoryName)]
        );

        if ($category) {
            // Check if already linked
            $existing = $db->fetch(
                "SELECT 1 FROM image_categories WHERE image_id = :image_id AND category_id = :cat_id",
                ['image_id' => $imageId, 'cat_id' => $category['id']]
            );

            if (!$existing) {
                $db->insert('image_categories', [
                    'image_id' => $imageId,
                    'category_id' => $category['id'],
                    'is_primary' => false,
                ]);
            }
        }
    }

    /**
     * Generate unique SEO-friendly slug
     */
    private function generateUniqueSlug(string $title, int $excludeId, $db): string
    {
        // Sanitize to create base slug
        $baseSlug = strtolower(trim($title));
        $baseSlug = preg_replace('/[^a-z0-9-]/', '-', $baseSlug);
        $baseSlug = preg_replace('/-+/', '-', $baseSlug);
        $baseSlug = trim($baseSlug, '-');

        if (empty($baseSlug)) {
            $baseSlug = 'image';
        }

        // Check if base slug exists (excluding current image)
        $exists = $db->fetch(
            "SELECT id FROM images WHERE slug = :slug AND id != :id",
            ['slug' => $baseSlug, 'id' => $excludeId]
        );

        if (!$exists) {
            return $baseSlug;
        }

        // Find next available number
        $counter = 2;
        while ($counter <= 1000) {
            $newSlug = $baseSlug . '-' . $counter;
            $exists = $db->fetch(
                "SELECT id FROM images WHERE slug = :slug AND id != :id",
                ['slug' => $newSlug, 'id' => $excludeId]
            );

            if (!$exists) {
                return $newSlug;
            }
            $counter++;
        }

        // Fallback with random suffix
        return $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
