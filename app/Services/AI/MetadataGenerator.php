<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\ClaudeAIService;
use App\Services\ReplicateAIService;
use App\Services\AIHordeService;
use App\Services\OpenRouterService;

/**
 * Metadata Generator
 *
 * Uses AI to generate SEO-optimized metadata for images.
 * Supports OpenRouter (recommended), AI Horde, Claude, and Replicate backends.
 */
class MetadataGenerator
{
    private ClaudeAIService|ReplicateAIService|AIHordeService|OpenRouterService $ai;
    private string $provider;
    private array $errors = [];

    public function __construct(?string $provider = null)
    {
        $this->provider = $provider ?? $this->getConfiguredProvider();

        if ($this->provider === 'openrouter') {
            $this->ai = new OpenRouterService();
        } elseif ($this->provider === 'aihorde') {
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

        // Check which API key is configured (priority: OpenRouter > AI Horde > Replicate > Claude)
        $openrouter = new OpenRouterService();
        if ($openrouter->isConfigured()) {
            return 'openrouter';
        }

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
                'openrouter' => 'OpenRouter',
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

        // Validate that we got actual content, not just empty strings
        if (!$this->validateAnalysisHasContent($analysis)) {
            $this->errors[] = 'AI analysis returned empty or invalid content';
            return false;
        }

        // Reconnect to database - connection may have timed out during long AI processing
        $db = app()->reconnectDatabase();

        // Update image record with AI-generated data
        $updates = [
            'ai_description' => $analysis['description'] ?? null,
            'ai_tags' => json_encode($analysis['tags'] ?? []),
            'ai_category_suggestions' => json_encode($analysis['categories'] ?? []),
            'ai_processed_at' => date('Y-m-d H:i:s'),
            'dominant_color' => $analysis['dominant_color'] ?? null,
            'color_palette' => !empty($analysis['colors']) ? json_encode($analysis['colors']) : null,
        ];

        // Always use AI-generated content when available (it's more SEO-optimized)
        if (!empty($analysis['description'])) {
            $updates['description'] = $analysis['description'];
        }

        if (!empty($analysis['caption'])) {
            $updates['caption'] = $analysis['caption'];
        }

        // Always use AI title - it's more descriptive and SEO-friendly than filename-based titles
        if (!empty($analysis['title'])) {
            $updates['title'] = $analysis['title'];
            $updates['alt_text'] = $analysis['alt_text'] ?? $analysis['title'];
        } elseif (!empty($analysis['alt_text'])) {
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
     * Process multiple images from queue (generic)
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
             ORDER BY q.priority DESC, q.created_at ASC
             LIMIT {$limit}"
        );

        $results = [
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $isFirstItem = true;
        foreach ($queue as $item) {
            // Add delay between API calls to avoid rate limiting (skip for first item)
            if (!$isFirstItem) {
                sleep(2); // 2 second delay between images
            }
            $isFirstItem = false;

            // Mark as processing
            $db->update('ai_processing_queue', [
                'status' => 'processing',
                'attempts' => $item['attempts'] + 1,
            ], 'id = :where_id', ['where_id' => $item['id']]);

            // Process the image
            $success = $this->processImage($item['image_id']);

            if ($success) {
                // Delete from queue after successful processing (keep queue clean)
                $db->delete('ai_processing_queue', 'id = :id', ['id' => $item['id']]);
                $results['processed']++;
            } else {
                $errorMessage = implode('; ', $this->errors);
                $maxAttempts = $item['max_attempts'] ?? 3; // Default to 3 attempts
                $newStatus = ($item['attempts'] + 1) >= $maxAttempts ? 'failed' : 'pending';

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
     * Process fast queue items (trusted users) - no artificial delays
     * Uses atomic claim to prevent concurrent processing
     */
    public function processFastQueue(int $limit = 50): array
    {
        $db = app()->getDatabase();

        $results = [
            'processed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        // Process one at a time with atomic claim to prevent race conditions
        $limit = (int) $limit;
        for ($i = 0; $i < $limit; $i++) {
            // Atomically claim the next pending item
            $claimed = $this->claimNextQueueItem($db, 'fast');

            if (!$claimed) {
                break; // No more items to process
            }

            // Process the image
            $success = $this->processImage($claimed['image_id']);

            if ($success) {
                // Delete from queue after successful processing
                $db->delete('ai_processing_queue', 'id = :id', ['id' => $claimed['id']]);
                $results['processed']++;
            } else {
                $errorMessage = implode('; ', $this->errors);
                $maxAttempts = $claimed['max_attempts'] ?? 3;
                $newStatus = ($claimed['attempts']) >= $maxAttempts ? 'failed' : 'pending';

                $db->update('ai_processing_queue', [
                    'status' => $newStatus,
                    'error_message' => $errorMessage,
                ], 'id = :where_id', ['where_id' => $claimed['id']]);

                $results['failed']++;
                $results['errors'][] = "Image {$claimed['image_id']}: {$errorMessage}";
            }
        }

        return $results;
    }

    /**
     * Atomically claim the next queue item to prevent race conditions
     */
    private function claimNextQueueItem($db, string $queueType): ?array
    {
        // Use UPDATE with LIMIT to atomically claim an item
        // This prevents two processes from claiming the same item
        $db->execute(
            "UPDATE ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             SET q.status = 'processing', q.attempts = q.attempts + 1
             WHERE q.queue_type = :queue_type
               AND q.status = 'pending'
               AND i.moderation_status = 'approved'
             ORDER BY q.priority DESC, q.created_at ASC
             LIMIT 1",
            ['queue_type' => $queueType]
        );

        // Now fetch the item we just claimed
        return $db->fetch(
            "SELECT q.*, i.storage_path, i.moderation_status
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.queue_type = :queue_type
               AND q.status = 'processing'
             ORDER BY q.created_at ASC
             LIMIT 1",
            ['queue_type' => $queueType]
        );
    }

    /**
     * Process ONE scheduled image (for cron job - runs every 4 minutes)
     * Returns immediately after processing one image.
     * Uses atomic claim to prevent concurrent processing.
     */
    public function processScheduledItem(): array
    {
        $db = app()->getDatabase();

        // Atomically claim the next scheduled image
        $db->execute(
            "UPDATE ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             SET q.status = 'processing', q.attempts = q.attempts + 1
             WHERE i.status = 'scheduled'
               AND i.scheduled_at <= NOW()
               AND q.queue_type = 'scheduled'
               AND q.status = 'pending'
             ORDER BY i.scheduled_at ASC
             LIMIT 1"
        );

        // Get the image we just claimed
        $image = $db->fetch(
            "SELECT i.*, q.id as queue_id, q.attempts
             FROM images i
             JOIN ai_processing_queue q ON q.image_id = i.id
             WHERE i.status = 'scheduled'
               AND q.queue_type = 'scheduled'
               AND q.status = 'processing'
             ORDER BY i.scheduled_at ASC
             LIMIT 1"
        );

        if (!$image) {
            return [
                'processed' => 0,
                'failed' => 0,
                'message' => 'No scheduled images ready for processing',
            ];
        }

        // Mark image as processing
        $db->update('images', [
            'status' => 'processing',
        ], 'id = :where_id', ['where_id' => $image['id']]);

        // Process the image with AI
        $success = $this->processImage($image['id']);

        if ($success) {
            // Delete from queue
            $db->delete('ai_processing_queue', 'id = :id', ['id' => $image['queue_id']]);

            // Update batch progress if part of a batch
            if ($image['batch_id']) {
                $queueService = new \App\Services\QueueService();
                $queueService->updateBatchProgress((int) $image['batch_id']);
            }

            return [
                'processed' => 1,
                'failed' => 0,
                'image_id' => $image['id'],
                'title' => $image['title'],
                'message' => 'Successfully processed and published',
            ];
        } else {
            $errorMessage = implode('; ', $this->errors);

            // Still try to publish without AI metadata
            $db->update('images', [
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
            ], 'id = :where_id', ['where_id' => $image['id']]);

            $db->update('ai_processing_queue', [
                'status' => 'failed',
                'error_message' => $errorMessage,
            ], 'id = :where_id', ['where_id' => $image['queue_id']]);

            // Update batch progress
            if ($image['batch_id']) {
                $queueService = new \App\Services\QueueService();
                $queueService->updateBatchProgress((int) $image['batch_id']);
            }

            return [
                'processed' => 1,
                'failed' => 1,
                'image_id' => $image['id'],
                'message' => "Published without AI metadata: {$errorMessage}",
            ];
        }
    }

    /**
     * Validate that AI analysis contains actual content
     */
    private function validateAnalysisHasContent(array $analysis): bool
    {
        // Must have a non-empty title (at least 5 characters)
        $title = $analysis['title'] ?? '';
        if (empty($title) || strlen(trim($title)) < 5) {
            return false;
        }

        // Must have at least 3 tags
        $tags = $analysis['tags'] ?? [];
        if (!is_array($tags) || count($tags) < 3) {
            return false;
        }

        // Tags must not be empty strings
        $validTags = array_filter($tags, fn($t) => !empty(trim($t)) && strlen(trim($t)) > 1);
        if (count($validTags) < 3) {
            return false;
        }

        // Description should have some content (at least 10 characters)
        $description = $analysis['description'] ?? '';
        if (empty($description) || strlen(trim($description)) < 10) {
            return false;
        }

        return true;
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
