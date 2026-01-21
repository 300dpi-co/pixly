<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\ImageProcessor;

/**
 * Admin Image Controller
 *
 * Handles image management in admin panel.
 */
class ImageController extends Controller
{
    /**
     * List all images
     */
    public function index(): Response
    {
        $db = $this->db();

        $page = max(1, (int) $this->request->input('page', 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        // Filter parameters
        $status = $this->request->input('status');
        $moderation = $this->request->input('moderation');
        $search = $this->request->input('search');

        // Build query
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }

        if ($moderation) {
            $where[] = "moderation_status = :moderation";
            $params['moderation'] = $moderation;
        }

        if ($search) {
            $where[] = "(title LIKE :search OR slug LIKE :search2)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM images {$whereClause}", $params);

        // Get images
        $images = $db->fetchAll(
            "SELECT id, uuid, title, slug, thumbnail_path, storage_path, status, moderation_status,
                    view_count, favorite_count, created_at
             FROM images
             {$whereClause}
             ORDER BY created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = (int) ceil($total / $perPage);

        return $this->view('admin/images/index', [
            'title' => 'Images',
            'currentPage' => 'images',
            'images' => $images,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'status' => $status,
                'moderation' => $moderation,
                'search' => $search,
            ],
        ], 'admin');
    }

    /**
     * Show edit form
     */
    public function edit(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $image = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $id]);

        if (!$image) {
            return $this->redirectWithError(url('/admin/images'), 'Image not found.');
        }

        // Get categories
        $categories = $db->fetchAll(
            "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name"
        );

        // Get image's current categories
        $imageCategories = $db->fetchAll(
            "SELECT category_id FROM image_categories WHERE image_id = :id",
            ['id' => $id]
        );
        $imageCategoryIds = array_column($imageCategories, 'category_id');

        // Get all tags
        $tags = $db->fetchAll("SELECT id, name FROM tags ORDER BY usage_count DESC, name LIMIT 100");

        // Get image's current tags
        $imageTags = $db->fetchAll(
            "SELECT t.id, t.name FROM tags t
             JOIN image_tags it ON t.id = it.tag_id
             WHERE it.image_id = :id",
            ['id' => $id]
        );

        return $this->view('admin/images/edit', [
            'title' => 'Edit Image',
            'currentPage' => 'images',
            'image' => $image,
            'categories' => $categories,
            'imageCategoryIds' => $imageCategoryIds,
            'tags' => $tags,
            'imageTags' => $imageTags,
        ], 'admin');
    }

    /**
     * Update image
     */
    public function update(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $image = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $id]);

        if (!$image) {
            return $this->redirectWithError(url('/admin/images'), 'Image not found.');
        }

        // Validate input
        $title = trim($this->request->input('title', ''));
        $slug = trim($this->request->input('slug', ''));
        $altText = trim($this->request->input('alt_text', ''));
        $description = trim($this->request->input('description', ''));
        $status = $this->request->input('status', 'draft');
        $moderationStatus = $this->request->input('moderation_status', 'pending');
        $featured = $this->request->input('featured') === 'on';
        $regenerateSlug = $this->request->input('regenerate_slug') === 'on';

        if (empty($title)) {
            return $this->redirectWithError(url("/admin/images/{$id}/edit"), 'Title is required.');
        }

        // Handle slug
        if ($regenerateSlug || empty($slug)) {
            $slug = $this->generateUniqueSlug($title, $id);
        } else {
            // Sanitize provided slug
            $slug = $this->sanitizeSlug($slug);
            // Check uniqueness (excluding current image)
            $exists = $db->fetch(
                "SELECT id FROM images WHERE slug = :slug AND id != :id",
                ['slug' => $slug, 'id' => $id]
            );
            if ($exists) {
                $slug = $this->generateUniqueSlug($slug, $id);
            }
        }

        // Update image
        $db->update('images', [
            'title' => $title,
            'slug' => $slug,
            'alt_text' => $altText ?: $title,
            'description' => $description,
            'status' => $status,
            'moderation_status' => $moderationStatus,
            'featured' => $featured,
            'published_at' => $status === 'published' && !$image['published_at'] ? date('Y-m-d H:i:s') : $image['published_at'],
        ], 'id = :where_id', ['where_id' => $id]);

        // Update categories
        $db->execute("DELETE FROM image_categories WHERE image_id = :id", ['id' => $id]);
        $categoryIds = $this->request->input('categories', []);
        if (is_array($categoryIds)) {
            foreach ($categoryIds as $index => $categoryId) {
                $db->insert('image_categories', [
                    'image_id' => $id,
                    'category_id' => (int) $categoryId,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Handle tags
        $this->syncTags($id, $this->request->input('tags', ''));

        return $this->redirectWithSuccess(url('/admin/images'), 'Image updated successfully.');
    }

    /**
     * Delete image
     */
    public function destroy(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $image = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $id]);

        if (!$image) {
            return $this->json(['error' => 'Image not found'], 404);
        }

        // Delete files
        $processor = new ImageProcessor();
        $processor->deleteAll(
            $image['storage_path'],
            $image['thumbnail_path'],
            $image['webp_path']
        );

        // Delete from database (cascades to relations)
        $db->execute("DELETE FROM images WHERE id = :id", ['id' => $id]);

        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }

        return $this->redirectWithSuccess(url('/admin/images'), 'Image deleted successfully.');
    }

    /**
     * Sync tags for an image
     */
    private function syncTags(int $imageId, string $tagString): void
    {
        $db = $this->db();

        // Delete existing tags
        $db->execute("DELETE FROM image_tags WHERE image_id = :id", ['id' => $imageId]);

        if (empty($tagString)) {
            return;
        }

        // Parse tags
        $tagNames = array_filter(array_map('trim', explode(',', $tagString)));

        foreach ($tagNames as $tagName) {
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

            // Link to image
            $db->insert('image_tags', [
                'image_id' => $imageId,
                'tag_id' => $tagId,
                'source' => 'manual',
            ]);

            // Update usage count
            $db->execute("UPDATE tags SET usage_count = usage_count + 1 WHERE id = :id", ['id' => $tagId]);
        }
    }

    /**
     * Bulk action on images
     */
    public function bulk(): Response
    {
        $action = $this->request->input('action');
        $ids = $this->request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['error' => 'No images selected'], 400);
        }

        $db = $this->db();

        switch ($action) {
            case 'publish':
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->execute(
                    "UPDATE images SET status = 'published', published_at = NOW() WHERE id IN ({$placeholders})",
                    array_values($ids)
                );
                break;

            case 'unpublish':
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->execute(
                    "UPDATE images SET status = 'draft' WHERE id IN ({$placeholders})",
                    array_values($ids)
                );
                break;

            case 'approve':
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->execute(
                    "UPDATE images SET moderation_status = 'approved' WHERE id IN ({$placeholders})",
                    array_values($ids)
                );
                break;

            case 'delete':
                foreach ($ids as $id) {
                    $image = $db->fetch("SELECT storage_path, thumbnail_path, webp_path FROM images WHERE id = :id", ['id' => $id]);
                    if ($image) {
                        $processor = new ImageProcessor();
                        $processor->deleteAll($image['storage_path'], $image['thumbnail_path'], $image['webp_path']);
                        $db->execute("DELETE FROM images WHERE id = :id", ['id' => $id]);
                    }
                }
                break;

            default:
                return $this->json(['error' => 'Invalid action'], 400);
        }

        return $this->json(['success' => true, 'count' => count($ids)]);
    }

    /**
     * Sanitize slug string
     */
    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: 'image';
    }

    /**
     * Generate unique slug from title
     * Excludes specified image ID from uniqueness check
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $db = $this->db();

        $baseSlug = $this->sanitizeSlug($title);

        // Check if base slug exists (excluding current image)
        if ($excludeId) {
            $exists = $db->fetch(
                "SELECT id FROM images WHERE slug = :slug AND id != :id",
                ['slug' => $baseSlug, 'id' => $excludeId]
            );
        } else {
            $exists = $db->fetch(
                "SELECT id FROM images WHERE slug = :slug",
                ['slug' => $baseSlug]
            );
        }

        if (!$exists) {
            return $baseSlug;
        }

        // Find next available number
        $counter = 2;
        while (true) {
            $newSlug = $baseSlug . '-' . $counter;

            if ($excludeId) {
                $exists = $db->fetch(
                    "SELECT id FROM images WHERE slug = :slug AND id != :id",
                    ['slug' => $newSlug, 'id' => $excludeId]
                );
            } else {
                $exists = $db->fetch(
                    "SELECT id FROM images WHERE slug = :slug",
                    ['slug' => $newSlug]
                );
            }

            if (!$exists) {
                return $newSlug;
            }

            $counter++;

            if ($counter > 1000) {
                return $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
            }
        }
    }
}
