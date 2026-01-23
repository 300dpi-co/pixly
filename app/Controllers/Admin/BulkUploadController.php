<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\UploadService;
use App\Services\ImageProcessor;
use App\Services\QueueService;

/**
 * Admin Bulk Upload Controller
 *
 * Handles bulk image uploads with scheduling options.
 * For 4+ images, shows scheduling options for staggered publishing.
 */
class BulkUploadController extends Controller
{
    /**
     * Show bulk upload form
     */
    public function index(): Response
    {
        $db = $this->db();

        // Get categories for selection
        $categories = $db->fetchAll(
            "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name"
        );

        // Get recent batches
        $batches = $db->fetchAll(
            "SELECT b.*,
                    (SELECT COUNT(*) FROM images WHERE batch_id = b.id) as image_count,
                    (SELECT COUNT(*) FROM images WHERE batch_id = b.id AND status = 'published') as published_count
             FROM upload_batches b
             WHERE b.user_id = :user_id
             ORDER BY b.created_at DESC
             LIMIT 5",
            ['user_id' => $this->user()['id']]
        );

        return $this->view('admin/bulk-upload/index', [
            'title' => 'Bulk Upload',
            'currentPage' => 'bulk-upload',
            'categories' => $categories,
            'batches' => $batches,
        ], 'admin');
    }

    /**
     * Handle bulk upload - creates batch and shows scheduling options
     */
    public function upload(): Response
    {
        $uploadService = new UploadService();
        $imageProcessor = new ImageProcessor();
        $queueService = new QueueService();
        $db = $this->db();
        $user = $this->user();

        // Check if files were uploaded
        if (empty($_FILES['images'])) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'No files were uploaded.');
        }

        $files = $_FILES['images'];
        $normalizedFiles = $this->normalizeFilesArray($files);

        // Filter out empty slots
        $validFiles = array_filter($normalizedFiles, fn($f) => $f['error'] !== UPLOAD_ERR_NO_FILE);

        if (count($validFiles) < 1) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'No valid files were selected.');
        }

        // For 1-3 images, redirect to regular upload flow
        if (count($validFiles) <= 3) {
            // Process normally through regular upload
            return $this->processSmallBatch($validFiles, $user, $uploadService, $imageProcessor);
        }

        // For 4+ images, create a batch and show scheduling options
        $batch = $queueService->createBatch($user['id'], 'auto_publish');
        $categoryId = (int) $this->request->input('category_id');
        $uploadedIds = [];
        $errors = [];

        foreach ($validFiles as $file) {
            $result = $this->uploadSingleImage($file, $user, $batch['id'], $categoryId, $uploadService, $imageProcessor);

            if ($result['success']) {
                $uploadedIds[] = $result['image_id'];
            } else {
                $errors[] = $result['error'];
            }
        }

        // Update batch total
        $db->update('upload_batches', [
            'total_images' => count($uploadedIds),
        ], 'id = :id', ['id' => $batch['id']]);

        if (empty($uploadedIds)) {
            return $this->redirectWithError(
                url('/admin/bulk-upload'),
                'Upload failed: ' . implode('; ', $errors)
            );
        }

        // Redirect to scheduling page
        $errorMsg = !empty($errors) ? '&errors=' . urlencode(implode('; ', $errors)) : '';
        return $this->redirect(url('/admin/bulk-upload/schedule/' . $batch['uuid']) . '?count=' . count($uploadedIds) . $errorMsg);
    }

    /**
     * Show scheduling options page
     */
    public function schedule(string $uuid): Response
    {
        $queueService = new QueueService();
        $batch = $queueService->getBatchByUuid($uuid);

        if (!$batch) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Batch not found.');
        }

        // Verify ownership
        if ($batch['user_id'] != $this->user()['id']) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Access denied.');
        }

        $db = $this->db();
        $images = $db->fetchAll(
            "SELECT id, title, thumbnail_path FROM images WHERE batch_id = :batch_id ORDER BY id",
            ['batch_id' => $batch['id']]
        );

        return $this->view('admin/bulk-upload/schedule', [
            'title' => 'Schedule Bulk Upload',
            'currentPage' => 'bulk-upload',
            'batch' => $batch,
            'images' => $images,
            'count' => count($images),
        ], 'admin');
    }

    /**
     * Process scheduling selection
     */
    public function scheduleSubmit(string $uuid): Response
    {
        $queueService = new QueueService();
        $batch = $queueService->getBatchByUuid($uuid);

        if (!$batch) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Batch not found.');
        }

        if ($batch['user_id'] != $this->user()['id']) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Access denied.');
        }

        $scheduleType = $this->request->input('schedule_type', 'auto_publish');
        $startAt = $this->request->input('start_at');
        $interval = (int) $this->request->input('interval', 4);

        // Validate interval (3-10 minutes)
        $interval = max(3, min(10, $interval));

        if ($scheduleType === 'scheduled' && empty($startAt)) {
            return $this->redirectWithError(
                url('/admin/bulk-upload/schedule/' . $uuid),
                'Please select a start date and time.'
            );
        }

        $db = $this->db();

        // Determine start time
        if ($scheduleType === 'auto_publish') {
            $startTime = date('Y-m-d H:i:s');
        } else {
            $startTime = date('Y-m-d H:i:s', strtotime($startAt));
        }

        // Update batch
        $db->update('upload_batches', [
            'schedule_type' => $scheduleType,
            'scheduled_start_at' => $startTime,
            'publish_interval_minutes' => $interval,
            'status' => 'processing',
        ], 'id = :id', ['id' => $batch['id']]);

        // Schedule all images with staggered times
        $queueService->scheduleBatchImages($batch['id'], $startTime, $interval);

        $message = $scheduleType === 'auto_publish'
            ? 'Bulk upload scheduled! Images will be published every ' . $interval . ' minutes starting now.'
            : 'Bulk upload scheduled! Images will start publishing at ' . date('M j, g:i A', strtotime($startTime)) . '.';

        return $this->redirectWithSuccess(
            url('/admin/bulk-upload/status/' . $uuid),
            $message
        );
    }

    /**
     * Show batch status page
     */
    public function batchStatus(string $uuid): Response
    {
        $queueService = new QueueService();
        $status = $queueService->getBatchStatus($uuid);

        if (!$status) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Batch not found.');
        }

        if ($status['batch']['user_id'] != $this->user()['id']) {
            return $this->redirectWithError(url('/admin/bulk-upload'), 'Access denied.');
        }

        return $this->view('admin/bulk-upload/status', [
            'title' => 'Batch Status',
            'currentPage' => 'bulk-upload',
            'batch' => $status['batch'],
            'images' => $status['images'],
            'stats' => $status['stats'],
        ], 'admin');
    }

    /**
     * Process small batch (1-3 images) immediately
     */
    private function processSmallBatch(array $files, array $user, UploadService $uploadService, ImageProcessor $imageProcessor): Response
    {
        $db = $this->db();
        $uploadedCount = 0;
        $errors = [];
        $categoryId = (int) $this->request->input('category_id');

        foreach ($files as $file) {
            $result = $this->uploadSingleImage($file, $user, null, $categoryId, $uploadService, $imageProcessor);

            if ($result['success']) {
                $uploadedCount++;

                // Process AI immediately for small batches
                $this->processImageAI($result['image_id']);
            } else {
                $errors[] = $result['error'];
            }
        }

        if ($uploadedCount > 0) {
            $message = "Successfully uploaded and processed {$uploadedCount} image(s).";
            if (!empty($errors)) {
                $message .= ' Some files had errors: ' . implode('; ', $errors);
            }
            return $this->redirectWithSuccess(url('/admin/images'), $message);
        }

        return $this->redirectWithError(
            url('/admin/bulk-upload'),
            'Upload failed: ' . implode('; ', $errors)
        );
    }

    /**
     * Upload a single image file
     */
    private function uploadSingleImage(
        array $file,
        array $user,
        ?int $batchId,
        int $categoryId,
        UploadService $uploadService,
        ImageProcessor $imageProcessor
    ): array {
        $db = $this->db();

        $uploadResult = $uploadService->upload($file);

        if (!$uploadResult) {
            return ['success' => false, 'error' => $file['name'] . ': ' . implode(', ', $uploadService->getErrors())];
        }

        try {
            $processResult = $imageProcessor->process($uploadResult['relative_path']);

            $slug = $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME));
            $title = $this->generateTitle($file['name']);

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
                'moderation_status' => 'approved',
                'status' => $batchId ? 'queued' : 'draft',
                'queue_type' => $batchId ? 'scheduled' : null,
                'batch_id' => $batchId,
            ]);

            $imageId = (int) $db->lastInsertId();

            // Add to category
            if ($categoryId > 0) {
                $db->insert('image_categories', [
                    'image_id' => $imageId,
                    'category_id' => $categoryId,
                    'is_primary' => true,
                ]);

                $db->execute(
                    "UPDATE categories SET image_count = image_count + 1 WHERE id = :id",
                    ['id' => $categoryId]
                );
            }

            // Add to AI queue
            $db->insert('ai_processing_queue', [
                'image_id' => $imageId,
                'task_type' => 'all',
                'priority' => $batchId ? 5 : 10,
                'status' => 'pending',
                'queue_type' => $batchId ? 'scheduled' : 'fast',
            ]);

            return ['success' => true, 'image_id' => $imageId];

        } catch (\Exception $e) {
            $uploadService->delete($uploadResult['relative_path']);
            return ['success' => false, 'error' => $file['name'] . ': ' . $e->getMessage()];
        }
    }

    /**
     * Normalize files array for multiple uploads
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
     * Generate unique slug
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

        $exists = $db->fetch("SELECT id FROM images WHERE slug = :slug", ['slug' => $baseSlug]);

        if (!$exists) {
            return $baseSlug;
        }

        $counter = 2;
        while ($counter <= 1000) {
            $newSlug = $baseSlug . '-' . $counter;
            $exists = $db->fetch("SELECT id FROM images WHERE slug = :slug", ['slug' => $newSlug]);
            if (!$exists) {
                return $newSlug;
            }
            $counter++;
        }

        return $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    /**
     * Generate title from filename
     */
    private function generateTitle(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/\s*\d+$/', '', $name);
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
     * Process image with AI
     */
    private function processImageAI(int $imageId): void
    {
        try {
            $generator = new \App\Services\AI\MetadataGenerator();
            $success = $generator->processImage($imageId);

            if ($success) {
                $this->db()->update(
                    'ai_processing_queue',
                    ['status' => 'completed'],
                    'image_id = :image_id AND status IN (:s1, :s2)',
                    ['image_id' => $imageId, 's1' => 'pending', 's2' => 'processing']
                );
            }
        } catch (\Throwable $e) {
            error_log('AI processing failed for image ' . $imageId . ': ' . $e->getMessage());
        }
    }
}
