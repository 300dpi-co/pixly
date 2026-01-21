<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

/**
 * Favorite API Controller
 *
 * Handles favorite add/remove operations via API.
 */
class FavoriteApiController extends Controller
{
    /**
     * Add image to favorites
     */
    public function store(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        // Check if image exists
        $image = $db->fetch(
            "SELECT id FROM images WHERE id = :id AND status = 'published'",
            ['id' => $id]
        );

        if (!$image) {
            return $this->json(['error' => 'Image not found'], 404);
        }

        // Check if already favorited
        $existing = $db->fetch(
            "SELECT id FROM favorites WHERE user_id = :user_id AND image_id = :image_id",
            ['user_id' => $userId, 'image_id' => $id]
        );

        if ($existing) {
            return $this->json(['message' => 'Already in favorites']);
        }

        // Add to favorites
        $db->insert('favorites', [
            'user_id' => $userId,
            'image_id' => $id,
        ]);

        // Update favorite count
        $db->execute(
            "UPDATE images SET favorite_count = favorite_count + 1 WHERE id = :id",
            ['id' => $id]
        );

        return $this->json(['success' => true, 'message' => 'Added to favorites']);
    }

    /**
     * Remove image from favorites
     */
    public function destroy(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();
        $userId = $_SESSION['user_id'];

        // Check if favorited
        $existing = $db->fetch(
            "SELECT id FROM favorites WHERE user_id = :user_id AND image_id = :image_id",
            ['user_id' => $userId, 'image_id' => $id]
        );

        if (!$existing) {
            return $this->json(['error' => 'Not in favorites'], 404);
        }

        // Remove from favorites
        $db->execute(
            "DELETE FROM favorites WHERE user_id = :user_id AND image_id = :image_id",
            ['user_id' => $userId, 'image_id' => $id]
        );

        // Update favorite count
        $db->execute(
            "UPDATE images SET favorite_count = GREATEST(0, favorite_count - 1) WHERE id = :id",
            ['id' => $id]
        );

        return $this->json(['success' => true, 'message' => 'Removed from favorites']);
    }
}
