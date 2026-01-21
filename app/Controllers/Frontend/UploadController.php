<?php

declare(strict_types=1);

namespace App\Controllers\Frontend;

use App\Core\Controller;
use App\Core\Response;
use App\Models\User;
use App\Services\UploadService;
use App\Services\ImageProcessor;

/**
 * User Upload Controller
 *
 * Handles image uploads by registered users.
 */
class UploadController extends Controller
{
    /**
     * Check if user can upload images
     */
    private function canUpload(): bool
    {
        $contributorSystemEnabled = setting('contributor_system_enabled', '0') === '1';

        // If contributor system is disabled, all authenticated users can upload
        if (!$contributorSystemEnabled) {
            return true;
        }

        // If enabled, check user role
        $user = User::find($_SESSION['user_id']);
        return $user && $user->canUpload();
    }

    /**
     * Show upload form
     */
    public function show(): Response
    {
        // Check upload permissions
        if (!$this->canUpload()) {
            $contributorSystemEnabled = setting('contributor_system_enabled', '0') === '1';
            if ($contributorSystemEnabled) {
                return $this->redirectWithError('/contributor/request', 'You need to be a contributor to upload images.');
            }
            return $this->redirectWithError('/', 'You do not have permission to upload images.');
        }

        $db = $this->db();

        // Get categories for selection
        $categories = $db->fetchAll(
            "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name"
        );

        return $this->view('frontend/upload', [
            'title' => 'Upload Image',
            'meta_description' => 'Share your images with the community',
            'categories' => $categories,
        ]);
    }

    /**
     * Handle image upload
     */
    public function upload(): Response
    {
        // Check upload permissions
        if (!$this->canUpload()) {
            return $this->redirectWithError('/contributor/request', 'You need to be a contributor to upload images.');
        }

        $uploadService = new UploadService();
        $imageProcessor = new ImageProcessor();
        $db = $this->db();
        $user = $this->user();

        // Check if file was uploaded
        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->redirectWithError('/upload', 'Please select an image to upload.');
        }

        $file = $_FILES['image'];

        // Upload the file
        $uploadResult = $uploadService->upload($file);

        if (!$uploadResult) {
            return $this->redirectWithError('/upload', implode(', ', $uploadService->getErrors()));
        }

        try {
            // Process the image (thumbnails, WebP)
            $processResult = $imageProcessor->process($uploadResult['relative_path']);

            // Get form data
            $title = trim($this->request->input('title', ''));
            $description = trim($this->request->input('description', ''));
            $tags = trim($this->request->input('tags', ''));
            $categoryId = (int) $this->request->input('category_id', 0);

            // Generate title from filename if not provided
            if (empty($title)) {
                $title = $this->generateTitle($file['name']);
            }

            // Generate slug
            $slug = $this->generateSlug($title);

            // Check if user is trusted
            $bypassModeration = $this->shouldBypassModeration($user);

            // Insert into database
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
                'description' => $description ?: null,
                'alt_text' => $title,
                'dominant_color' => $processResult['dominant_color'],
                'color_palette' => json_encode($processResult['color_palette']),
                'moderation_status' => $bypassModeration ? 'approved' : 'pending',
                'status' => $bypassModeration ? 'published' : 'draft',
            ]);

            $imageId = (int) $db->lastInsertId();

            // Add to category if selected
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

            // Process tags
            if (!empty($tags)) {
                $this->processTags($imageId, $tags);
            }

            // Queue for AI processing
            $db->insert('ai_processing_queue', [
                'image_id' => $imageId,
                'task_type' => 'all',
                'priority' => 5,
                'status' => 'pending',
            ]);

            if ($bypassModeration) {
                return $this->redirectWithSuccess('/image/' . $slug, 'Image uploaded successfully!');
            }

            return $this->redirectWithSuccess('/upload', 'Image uploaded! It will be visible after moderation.');

        } catch (\Exception $e) {
            $uploadService->delete($uploadResult['relative_path']);
            return $this->redirectWithError('/upload', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Process and save tags
     */
    private function processTags(int $imageId, string $tagsString): void
    {
        $db = $this->db();
        $tags = array_filter(array_map('trim', explode(',', $tagsString)));

        foreach ($tags as $tagName) {
            if (strlen($tagName) < 2 || strlen($tagName) > 50) continue;

            $tagSlug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $tagName));
            $tagSlug = preg_replace('/-+/', '-', trim($tagSlug, '-'));

            // Find or create tag
            $tag = $db->fetch("SELECT id FROM tags WHERE slug = :slug", ['slug' => $tagSlug]);

            if (!$tag) {
                $db->insert('tags', [
                    'name' => $tagName,
                    'slug' => $tagSlug,
                    'usage_count' => 1,
                ]);
                $tagId = (int) $db->lastInsertId();
            } else {
                $tagId = (int) $tag['id'];
                $db->execute("UPDATE tags SET usage_count = usage_count + 1 WHERE id = :id", ['id' => $tagId]);
            }

            // Link tag to image
            $db->insert('image_tags', [
                'image_id' => $imageId,
                'tag_id' => $tagId,
            ]);
        }
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
        while (true) {
            $newSlug = $baseSlug . '-' . $counter;
            $exists = $db->fetch("SELECT id FROM images WHERE slug = :slug", ['slug' => $newSlug]);

            if (!$exists) {
                return $newSlug;
            }

            $counter++;
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
     * Check if user should bypass moderation
     */
    private function shouldBypassModeration(array $user): bool
    {
        if (in_array($user['role'], ['admin', 'superadmin', 'moderator'])) {
            return true;
        }
        if (!empty($user['is_trusted'])) {
            return true;
        }
        return false;
    }
}
