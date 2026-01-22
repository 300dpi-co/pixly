<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\ClaudeAIService;
use App\Services\ReplicateAIService;
use App\Services\AIHordeService;

/**
 * Metadata Generator
 *
 * Uses AI to generate SEO-optimized metadata for images.
 * Supports AI Horde (free), Claude, and Replicate backends.
 */
class MetadataGenerator
{
    private ClaudeAIService|ReplicateAIService|AIHordeService $ai;
    private string $provider;
    private array $errors = [];

    public function __construct(?string $provider = null)
    {
        $this->provider = $provider ?? $this->getConfiguredProvider();

        if ($this->provider === 'aihorde') {
            $this->ai = new AIHordeService();
        } elseif ($this->provider === 'replicate') {
            $this->ai = new ReplicateAIService();
        } else {
            $this->ai = new ClaudeAIService();
        }
    }

    /**
     * Get the configured AI provider from settings
     */
    private function getConfiguredProvider(): string
    {
        try {
            $db = \app()->getDatabase();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = 'ai_provider'"
            );
            if ($result && !empty($result['setting_value'])) {
                return $result['setting_value'];
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        // Check which API key is configured (priority: AI Horde > Replicate > Claude)
        $aihorde = new AIHordeService();
        if ($aihorde->isConfigured()) {
            return 'aihorde';
        }

        $replicate = new ReplicateAIService();
        if ($replicate->isConfigured()) {
            return 'replicate';
        }

        return 'claude';
    }

    /**
     * Get current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
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
        $imagePath = \ROOT_PATH . '/uploads/' . $image['storage_path'];

        if (!file_exists($imagePath)) {
            $this->errors[] = 'Image file not found: ' . $image['storage_path'];
            return false;
        }

        // Check if AI is configured
        if (!$this->ai->isConfigured()) {
            $providerName = match($this->provider) {
                'aihorde' => 'AI Horde',
                'replicate' => 'Replicate',
                default => 'Claude',
            };
            $this->errors[] = "{$providerName} AI not configured. Add API key in Settings > API Keys.";
            return false;
        }

        // Get existing categories for context
        $categories = $db->fetchAll("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");

        // Analyze image with AI
        $analysis = null;
        try {
            $analysis = $this->ai->analyzeImage($imagePath, $categories);
        } catch (\Throwable $e) {
            $this->errors[] = 'AI analysis failed: ' . $e->getMessage();
            return false;
        }

        if (empty($analysis)) {
            $this->errors[] = 'AI analysis returned empty result';
            return false;
        }

        // Update image record with AI-generated data
        $updates = [
            'ai_description' => $analysis['description'] ?? null,
            'ai_tags' => json_encode($analysis['tags'] ?? []),
            'ai_category_suggestions' => json_encode($analysis['categories'] ?? []),
            'ai_processed_at' => date('Y-m-d H:i:s'),
            'dominant_color' => $analysis['dominant_color'] ?? null,
            'color_palette' => !empty($analysis['colors']) ? json_encode($analysis['colors']) : null,
        ];

        // Optionally update main fields if they're empty or generic
        if (empty($image['description']) && !empty($analysis['description'])) {
            $updates['description'] = $analysis['description'];
        }

        if (empty($image['caption']) && !empty($analysis['caption'])) {
            $updates['caption'] = $analysis['caption'];
        }

        // Update title if it's just the filename
        $titleUpdated = false;
        if ($this->isGenericTitle($image['title']) && !empty($analysis['title'])) {
            $updates['title'] = $analysis['title'];
            $updates['alt_text'] = $analysis['alt_text'] ?? $analysis['title'];
            $titleUpdated = true;
        } elseif (empty($image['alt_text']) && !empty($analysis['alt_text'])) {
            $updates['alt_text'] = $analysis['alt_text'];
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

        // Assign categories (first one is primary)
        if (!empty($analysis['categories'])) {
            $isFirst = true;
            foreach ($analysis['categories'] as $categoryName) {
                $this->suggestCategory($imageId, $categoryName, $isFirst);
                $isFirst = false;
            }
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
    private function suggestCategory(int $imageId, string $categoryName, bool $isPrimary = false): void
    {
        $db = app()->getDatabase();
        $categoryName = trim($categoryName);

        if (empty($categoryName)) {
            return;
        }

        // Find matching category (case-insensitive)
        $categorySlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($categoryName)));
        $category = $db->fetch(
            "SELECT id FROM categories WHERE LOWER(name) = LOWER(:name) OR slug = :slug",
            ['name' => $categoryName, 'slug' => $categorySlug]
        );

        if ($category) {
            // Check if already linked
            $existing = $db->fetch(
                "SELECT 1 FROM image_categories WHERE image_id = :image_id AND category_id = :cat_id",
                ['image_id' => $imageId, 'cat_id' => $category['id']]
            );

            if (!$existing) {
                // Check if image already has a primary category
                $hasPrimary = $db->fetch(
                    "SELECT 1 FROM image_categories WHERE image_id = :image_id AND is_primary = 1",
                    ['image_id' => $imageId]
                );

                $db->insert('image_categories', [
                    'image_id' => $imageId,
                    'category_id' => $category['id'],
                    'is_primary' => $isPrimary && !$hasPrimary ? 1 : 0,
                ]);

                // Update category image count
                $db->execute(
                    "UPDATE categories SET image_count = image_count + 1 WHERE id = :id",
                    ['id' => $category['id']]
                );
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
