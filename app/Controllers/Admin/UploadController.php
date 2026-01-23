<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\UploadService;
use App\Services\ImageProcessor;
use App\Services\AI\MetadataGenerator;

/**
 * Admin Upload Controller
 *
 * Handles image uploads in admin panel.
 */
class UploadController extends Controller
{
    /**
     * Show upload form
     */
    public function show(): Response
    {
        $db = $this->db();

        // Get categories for selection
        $categories = $db->fetchAll(
            "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name"
        );

        return $this->view('admin/upload', [
            'title' => 'Upload Images',
            'currentPage' => 'upload',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Handle image upload
     */
    public function upload(): Response
    {
        $uploadService = new UploadService();
        $imageProcessor = new ImageProcessor();
        $db = $this->db();
        $user = $this->user();

        // Check if files were uploaded
        if (empty($_FILES['images'])) {
            return $this->redirectWithError(
                url('/admin/images/upload'),
                'No files were uploaded.'
            );
        }

        $files = $_FILES['images'];
        $uploadedCount = 0;
        $queuedForAI = 0;
        $errors = [];
        $uploadedImageIds = [];

        // Normalize files array for multiple uploads
        $normalizedFiles = $this->normalizeFilesArray($files);

        // Check if uploader should bypass moderation (check once, not per file)
        $bypassModeration = $this->shouldBypassModeration($user);

        // Get category ID once
        $categoryId = (int) $this->request->input('category_id');

        foreach ($normalizedFiles as $file) {
            // Skip empty slots
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Upload the file
            $uploadResult = $uploadService->upload($file);

            if (!$uploadResult) {
                $errors[] = $file['name'] . ': ' . implode(', ', $uploadService->getErrors());
                continue;
            }

            try {
                // Process the image (thumbnails, WebP)
                $processResult = $imageProcessor->process($uploadResult['relative_path']);

                // Generate slug from filename
                $slug = $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME));

                // Generate basic title from filename
                $title = $this->generateTitle($file['name']);

                // Insert into database
                // Status stays 'draft' until AI processing completes
                $db->insert('images', [
                    'uuid' => $this->generateUuid(),
                    'user_id' => $user['id'],
                    'uploaded_by' => $user['id'],
                    'original_filename' => $uploadResult['original_filename'],
                    'storage_path' => $uploadResult['relative_path'],
                    'thumbnail_path' => $processResult['thumbnail'],
                    'thumbnail_webp_path' => $processResult['thumbnail_webp'] ?? null,
                    'medium_path' => $processResult['medium'] ?? null,
                    'medium_webp_path' => $processResult['medium_webp'] ?? null,
                    'webp_path' => $processResult['webp'],
                    'file_size' => $uploadResult['file_size'],
                    'is_animated' => $processResult['is_animated'] ?? false,
                    'mime_type' => $uploadResult['mime_type'],
                    'width' => $uploadResult['width'],
                    'height' => $uploadResult['height'],
                    'title' => $title,
                    'slug' => $slug,
                    'alt_text' => $title,
                    'dominant_color' => $processResult['dominant_color'],
                    'color_palette' => json_encode($processResult['color_palette']),
                    'moderation_status' => $bypassModeration ? 'approved' : 'pending',
                    'status' => 'draft', // Always draft until AI processed
                ]);

                $imageId = (int) $db->lastInsertId();
                $uploadedImageIds[] = $imageId;

                // Add to category if selected
                if ($categoryId > 0) {
                    $db->insert('image_categories', [
                        'image_id' => $imageId,
                        'category_id' => $categoryId,
                        'is_primary' => true,
                    ]);

                    // Update category image count
                    $db->execute(
                        "UPDATE categories SET image_count = image_count + 1 WHERE id = :id",
                        ['id' => $categoryId]
                    );
                }

                // Queue for AI processing (generates metadata)
                $db->insert('ai_processing_queue', [
                    'image_id' => $imageId,
                    'task_type' => 'all',
                    'priority' => $bypassModeration ? 10 : 5,
                    'status' => 'pending',
                ]);
                $queuedForAI++;

                $uploadedCount++;

            } catch (\Exception $e) {
                $errors[] = $file['name'] . ': ' . $e->getMessage();
                // Clean up uploaded file if DB insert failed
                $uploadService->delete($uploadResult['relative_path']);
            }
        }

        // Process AI for first few images only (avoid timeout on bulk uploads)
        // For single/small uploads: process immediately
        // For bulk uploads: process first 2, queue the rest
        $processedAI = 0;
        $maxImmediateProcess = min(2, count($uploadedImageIds));

        if ($bypassModeration && !empty($uploadedImageIds)) {
            foreach ($uploadedImageIds as $imageId) {
                if ($processedAI >= $maxImmediateProcess) {
                    break;
                }
                $this->processImageAI($imageId);
                $processedAI++;
            }
        }

        // Build response message
        if ($uploadedCount > 0) {
            $message = "Successfully uploaded {$uploadedCount} image(s).";

            if ($bypassModeration) {
                $remaining = $queuedForAI - $processedAI;
                if ($remaining > 0) {
                    $message .= " {$processedAI} processed, {$remaining} queued for AI processing.";
                }
            } else {
                $message .= " Queued for moderation.";
            }

            if (!empty($errors)) {
                $message .= ' Some files had errors: ' . implode('; ', $errors);
            }
            return $this->redirectWithSuccess(url('/admin/images'), $message);
        }

        return $this->redirectWithError(
            url('/admin/images/upload'),
            'Upload failed: ' . implode('; ', $errors)
        );
    }

    /**
     * Normalize files array for multiple upload
     */
    private function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                $normalized[] = [
                    'name' => $name,
                    'type' => $files['type'][$index],
                    'tmp_name' => $files['tmp_name'][$index],
                    'error' => $files['error'][$index],
                    'size' => $files['size'][$index],
                ];
            }
        } else {
            $normalized[] = $files;
        }

        return $normalized;
    }

    /**
     * Generate unique slug from title
     * Uses numeric suffix only when needed (e.g., beautiful-sunset, beautiful-sunset-2)
     */
    private function generateSlug(string $title): string
    {
        $db = $this->db();

        $baseSlug = strtolower(trim($title));
        $baseSlug = preg_replace('/[^a-z0-9-]/', '-', $baseSlug);
        $baseSlug = preg_replace('/-+/', '-', $baseSlug);
        $baseSlug = trim($baseSlug, '-');

        if (empty($baseSlug)) {
            $baseSlug = 'image';
        }

        // Check if base slug exists
        $exists = $db->fetch(
            "SELECT id FROM images WHERE slug = :slug",
            ['slug' => $baseSlug]
        );

        if (!$exists) {
            return $baseSlug;
        }

        // Find next available number
        $counter = 2;
        while (true) {
            $newSlug = $baseSlug . '-' . $counter;
            $exists = $db->fetch(
                "SELECT id FROM images WHERE slug = :slug",
                ['slug' => $newSlug]
            );

            if (!$exists) {
                return $newSlug;
            }

            $counter++;

            // Safety limit
            if ($counter > 1000) {
                return $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
            }
        }
    }

    /**
     * Generate title from filename
     */
    private function generateTitle(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // Replace underscores and dashes with spaces
        $name = str_replace(['_', '-'], ' ', $name);
        // Remove numbers at the end (like image_001)
        $name = preg_replace('/\s*\d+$/', '', $name);
        // Capitalize words
        return ucwords(trim($name));
    }

    /**
     * Generate UUID v4
     */
    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Check if user should bypass moderation
     * Admin, Moderator, Superadmin, or Trusted users skip moderation
     */
    private function shouldBypassModeration(array $user): bool
    {
        // Admin roles bypass moderation
        if (in_array($user['role'], ['admin', 'superadmin', 'moderator'])) {
            return true;
        }

        // Trusted users bypass moderation
        if (!empty($user['is_trusted'])) {
            return true;
        }

        return false;
    }

    private function aiDebugLog(string $message): void
    {
        $logFile = ROOT_PATH . '/storage/logs/ai_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] [Upload] {$message}\n", FILE_APPEND);
    }

    /**
     * Process image with AI immediately
     * Generates metadata and auto-publishes if moderation approved
     */
    private function processImageAI(int $imageId): void
    {
        $this->aiDebugLog("processImageAI called for image ID: {$imageId}");

        try {
            $generator = new MetadataGenerator();
            $this->aiDebugLog("MetadataGenerator created, provider: " . $generator->getProvider());

            // Process the image - it will check if AI is configured internally
            $success = $generator->processImage($imageId);
            $this->aiDebugLog("processImage result: " . ($success ? "SUCCESS" : "FAILED"));

            $db = $this->db();

            if ($success) {
                // Mark queue item as completed
                $db->update(
                    'ai_processing_queue',
                    ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')],
                    'image_id = :image_id AND status IN (:s1, :s2)',
                    ['image_id' => $imageId, 's1' => 'pending', 's2' => 'processing']
                );
            } else {
                // AI not configured or failed - still publish if moderation approved
                $errors = $generator->getErrors();
                $this->aiDebugLog("AI failed, errors: " . implode('; ', $errors));

                $image = $db->fetch("SELECT moderation_status FROM images WHERE id = :id", ['id' => $imageId]);
                if ($image && $image['moderation_status'] === 'approved') {
                    $db->update('images', [
                        'status' => 'published',
                        'published_at' => date('Y-m-d H:i:s'),
                    ], 'id = :id', ['id' => $imageId]);
                    $this->aiDebugLog("Published without AI (moderation was approved)");
                }
            }
        } catch (\Throwable $e) {
            // Log error but don't fail the upload - still publish if approved
            $this->aiDebugLog("EXCEPTION: " . $e->getMessage());

            try {
                $db = $this->db();
                $image = $db->fetch("SELECT moderation_status FROM images WHERE id = :id", ['id' => $imageId]);
                if ($image && $image['moderation_status'] === 'approved') {
                    $db->update('images', [
                        'status' => 'published',
                        'published_at' => date('Y-m-d H:i:s'),
                    ], 'id = :id', ['id' => $imageId]);
                }
            } catch (\Throwable $e2) {
                // Ignore
            }
        }
    }
}
