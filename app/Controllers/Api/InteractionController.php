<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Like;
use App\Models\Save;
use App\Models\Appreciation;

/**
 * API Controller for likes and saves
 */
class InteractionController extends Controller
{
    /**
     * Toggle like on an item (works for both guests and logged-in users)
     */
    public function like(): Response
    {
        $data = $this->request->json();
        $type = $data['type'] ?? '';
        $id = (int) ($data['id'] ?? 0);

        if (!in_array($type, ['image', 'blog_post']) || $id <= 0) {
            return Response::json(['error' => 'Invalid request'], 400);
        }

        // Pass user_id if logged in, null for guests
        $userId = $_SESSION['user_id'] ?? null;
        $result = Like::toggle($userId, $type, $id);

        return Response::json([
            'success' => true,
            'liked' => $result['liked'],
            'count' => $result['count'],
        ]);
    }

    /**
     * Toggle save on an item
     */
    public function save(): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required'], 401);
        }

        $data = $this->request->json();
        $type = $data['type'] ?? '';
        $id = (int) ($data['id'] ?? 0);

        if (!in_array($type, ['image', 'blog_post']) || $id <= 0) {
            return Response::json(['error' => 'Invalid request'], 400);
        }

        $result = Save::toggle($_SESSION['user_id'], $type, $id);

        return Response::json([
            'success' => true,
            'saved' => $result['saved'],
            'count' => $result['count'],
        ]);
    }

    /**
     * Toggle appreciation on an image (requires login)
     */
    public function appreciate(): Response
    {
        // Check if appreciate system is enabled
        if (setting('appreciate_system_enabled', '1') !== '1') {
            return Response::json(['error' => 'Appreciate feature is currently disabled'], 403);
        }

        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required to appreciate'], 401);
        }

        $data = $this->request->json();
        $imageId = (int) ($data['image_id'] ?? 0);

        if ($imageId <= 0) {
            return Response::json(['error' => 'Invalid image ID'], 400);
        }

        // Verify image exists and is published
        $image = $this->db()->fetch(
            "SELECT id, user_id FROM images WHERE id = :id AND status = 'published'",
            ['id' => $imageId]
        );

        if (!$image) {
            return Response::json(['error' => 'Image not found'], 404);
        }

        // Cannot appreciate your own images
        if ($image['user_id'] == $_SESSION['user_id']) {
            return Response::json(['error' => 'Cannot appreciate your own images'], 400);
        }

        $result = Appreciation::toggle($_SESSION['user_id'], $imageId);

        return Response::json([
            'success' => true,
            'appreciated' => $result['appreciated'],
            'count' => $result['count'],
        ]);
    }

    /**
     * Check like/save status for multiple items
     */
    public function status(): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required'], 401);
        }

        $data = $this->request->json();
        $type = $data['type'] ?? '';
        $ids = $data['ids'] ?? [];

        if (!in_array($type, ['image', 'blog_post']) || empty($ids)) {
            return Response::json(['error' => 'Invalid request'], 400);
        }

        $ids = array_map('intval', $ids);
        $likedIds = Like::getLikedIds($_SESSION['user_id'], $type, $ids);
        $savedIds = Save::getSavedIds($_SESSION['user_id'], $type, $ids);

        return Response::json([
            'success' => true,
            'liked' => $likedIds,
            'saved' => $savedIds,
        ]);
    }
}
