<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;
use App\Services\QueueService;

/**
 * API Controller for queue status polling
 *
 * Provides real-time status updates for image processing.
 */
class QueueStatusController extends Controller
{
    /**
     * Get processing status for an image
     */
    public function status(int $id): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required'], 401);
        }

        $db = $this->db();

        // Get image and verify ownership
        $image = $db->fetch(
            "SELECT id, title, slug, status, queue_type, uploaded_by, thumbnail_path
             FROM images WHERE id = :id",
            ['id' => $id]
        );

        if (!$image) {
            return Response::json(['error' => 'Image not found'], 404);
        }

        // Verify ownership
        if ($image['uploaded_by'] != $_SESSION['user_id']) {
            return Response::json(['error' => 'Access denied'], 403);
        }

        $queueService = new QueueService();
        $queueStatus = $queueService->getImageQueueStatus($id);

        // Determine user-friendly status message
        $message = $this->getStatusMessage($image['status'], $queueStatus);

        return Response::json([
            'success' => true,
            'image_id' => $image['id'],
            'title' => $image['title'],
            'slug' => $image['slug'],
            'status' => $image['status'],
            'queue_type' => $image['queue_type'],
            'queue_status' => $queueStatus['queue_status'],
            'queue_position' => $queueStatus['queue_position'],
            'message' => $message,
            'thumbnail' => $image['thumbnail_path'] ? '/uploads/' . $image['thumbnail_path'] : null,
            'is_complete' => in_array($image['status'], ['published', 'rejected']),
            'redirect_url' => $image['status'] === 'published' ? '/image/' . $image['slug'] : null,
        ]);
    }

    /**
     * Get queue position for an image
     */
    public function position(int $id): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required'], 401);
        }

        $db = $this->db();

        // Verify ownership
        $image = $db->fetch(
            "SELECT uploaded_by FROM images WHERE id = :id",
            ['id' => $id]
        );

        if (!$image) {
            return Response::json(['error' => 'Image not found'], 404);
        }

        if ($image['uploaded_by'] != $_SESSION['user_id']) {
            return Response::json(['error' => 'Access denied'], 403);
        }

        $queueService = new QueueService();
        $position = $queueService->getQueuePosition($id);

        return Response::json([
            'success' => true,
            'position' => $position,
            'total_in_queue' => $queueService->getFastQueueCount(),
        ]);
    }

    /**
     * Get batch status (for bulk uploads)
     */
    public function batchStatus(string $uuid): Response
    {
        if (!$this->isAuthenticated()) {
            return Response::json(['error' => 'Login required'], 401);
        }

        $queueService = new QueueService();
        $batch = $queueService->getBatchByUuid($uuid);

        if (!$batch) {
            return Response::json(['error' => 'Batch not found'], 404);
        }

        // Verify ownership
        if ($batch['user_id'] != $_SESSION['user_id']) {
            return Response::json(['error' => 'Access denied'], 403);
        }

        $status = $queueService->getBatchStatus($uuid);

        return Response::json([
            'success' => true,
            'batch' => [
                'uuid' => $batch['uuid'],
                'status' => $batch['status'],
                'schedule_type' => $batch['schedule_type'],
                'scheduled_start_at' => $batch['scheduled_start_at'],
                'publish_interval_minutes' => $batch['publish_interval_minutes'],
            ],
            'stats' => $status['stats'],
            'images' => array_map(function ($img) {
                return [
                    'id' => $img['id'],
                    'title' => $img['title'],
                    'status' => $img['status'],
                    'scheduled_at' => $img['scheduled_at'],
                    'slug' => $img['slug'],
                    'url' => $img['status'] === 'published' ? '/image/' . $img['slug'] : null,
                ];
            }, $status['images']),
        ]);
    }

    /**
     * Get user-friendly status message
     */
    private function getStatusMessage(string $imageStatus, array $queueStatus): string
    {
        switch ($imageStatus) {
            case 'draft':
                if ($queueStatus['moderation_status'] === 'pending') {
                    return 'Waiting for moderation...';
                }
                return 'Draft - not yet processed';

            case 'processing':
                return 'Generating title and tags...';

            case 'queued':
                $position = $queueStatus['queue_position'];
                if ($position) {
                    return "Position in queue: {$position}";
                }
                return 'In queue for processing...';

            case 'scheduled':
                $scheduledAt = $queueStatus['scheduled_at'];
                if ($scheduledAt) {
                    $dt = new \DateTime($scheduledAt);
                    return 'Scheduled for ' . $dt->format('M j, g:i A');
                }
                return 'Scheduled for publishing';

            case 'published':
                return 'Published!';

            case 'rejected':
                return 'Rejected by moderator';

            default:
                return 'Unknown status';
        }
    }
}
