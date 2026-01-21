<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

/**
 * Comment API Controller
 *
 * Handles comment operations via API.
 */
class CommentApiController extends Controller
{
    /**
     * Store a new comment
     */
    public function store(): Response
    {
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        $imageId = (int) $this->request->input('image_id');
        $content = trim($this->request->input('content', ''));
        $parentId = $this->request->input('parent_id') ? (int) $this->request->input('parent_id') : null;

        // Validate
        if (empty($content)) {
            return $this->json(['error' => 'Comment content is required'], 400);
        }

        if (strlen($content) > 1000) {
            return $this->json(['error' => 'Comment is too long (max 1000 characters)'], 400);
        }

        // Check if image exists
        $image = $db->fetch(
            "SELECT id FROM images WHERE id = :id AND status = 'published'",
            ['id' => $imageId]
        );

        if (!$image) {
            return $this->json(['error' => 'Image not found'], 404);
        }

        // If parent_id provided, verify it exists
        if ($parentId) {
            $parent = $db->fetch(
                "SELECT id FROM comments WHERE id = :id AND image_id = :image_id",
                ['id' => $parentId, 'image_id' => $imageId]
            );

            if (!$parent) {
                return $this->json(['error' => 'Parent comment not found'], 404);
            }
        }

        // Insert comment
        $db->insert('comments', [
            'image_id' => $imageId,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $content,
            'status' => 'pending', // Require moderation
        ]);

        $commentId = (int) $db->lastInsertId();

        // Get user info
        $user = $db->fetch("SELECT username FROM users WHERE id = :id", ['id' => $userId]);

        return $this->json([
            'success' => true,
            'message' => 'Comment submitted for moderation',
            'comment' => [
                'id' => $commentId,
                'content' => $content,
                'author' => $user['username'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ], 201);
    }
}
