<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Queue Service
 *
 * Central service for managing image processing queues.
 * Handles fast queue (trusted users), scheduled queue (bulk uploads),
 * and moderation queue (regular users).
 */
class QueueService
{
    private $db;

    public function __construct()
    {
        $this->db = \app()->getDatabase();
    }

    /**
     * Add image to fast queue (trusted users)
     */
    public function addToFastQueue(int $imageId, int $priority = 10): void
    {
        // Update image status
        $this->db->update('images', [
            'status' => 'processing',
            'queue_type' => 'fast',
        ], 'id = :id', ['id' => $imageId]);

        // Add to AI queue
        $this->db->insert('ai_processing_queue', [
            'image_id' => $imageId,
            'task_type' => 'all',
            'priority' => $priority,
            'status' => 'pending',
            'queue_type' => 'fast',
        ]);
    }

    /**
     * Add image to scheduled queue (bulk uploads)
     */
    public function addToScheduledQueue(int $imageId, string $scheduledAt, int $batchId): void
    {
        $this->db->update('images', [
            'status' => 'scheduled',
            'queue_type' => 'scheduled',
            'batch_id' => $batchId,
            'scheduled_at' => $scheduledAt,
        ], 'id = :id', ['id' => $imageId]);

        // Add to AI queue with scheduled type (won't be processed until scheduled time)
        $this->db->insert('ai_processing_queue', [
            'image_id' => $imageId,
            'task_type' => 'all',
            'priority' => 5,
            'status' => 'pending',
            'queue_type' => 'scheduled',
        ]);
    }

    /**
     * Add image to moderation queue (regular users)
     */
    public function addToModerationQueue(int $imageId): void
    {
        $this->db->update('images', [
            'status' => 'draft',
            'queue_type' => 'moderation',
            'moderation_status' => 'pending',
        ], 'id = :id', ['id' => $imageId]);

        // Add to AI queue (will be processed after moderation approval)
        $this->db->insert('ai_processing_queue', [
            'image_id' => $imageId,
            'task_type' => 'all',
            'priority' => 5,
            'status' => 'pending',
            'queue_type' => 'fast', // Processed as fast queue after approval
        ]);
    }

    /**
     * Get position of image in fast queue
     */
    public function getQueuePosition(int $imageId): ?int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as position
             FROM ai_processing_queue
             WHERE queue_type = 'fast'
               AND status = 'pending'
               AND id <= (SELECT id FROM ai_processing_queue WHERE image_id = :image_id LIMIT 1)
             ORDER BY priority DESC, created_at ASC",
            ['image_id' => $imageId]
        );

        return $result ? (int) $result['position'] : null;
    }

    /**
     * Get queue status for an image
     */
    public function getImageQueueStatus(int $imageId): array
    {
        $image = $this->db->fetch(
            "SELECT id, status, queue_type, scheduled_at, moderation_status
             FROM images WHERE id = :id",
            ['id' => $imageId]
        );

        if (!$image) {
            return ['status' => 'not_found'];
        }

        $queueItem = $this->db->fetch(
            "SELECT status, error_message, attempts
             FROM ai_processing_queue WHERE image_id = :id ORDER BY id DESC LIMIT 1",
            ['id' => $imageId]
        );

        $position = null;
        if ($queueItem && $queueItem['status'] === 'pending') {
            $position = $this->getQueuePosition($imageId);
        }

        return [
            'status' => $image['status'],
            'queue_type' => $image['queue_type'],
            'scheduled_at' => $image['scheduled_at'],
            'moderation_status' => $image['moderation_status'],
            'queue_status' => $queueItem['status'] ?? null,
            'queue_position' => $position,
            'queue_error' => $queueItem['error_message'] ?? null,
            'queue_attempts' => $queueItem['attempts'] ?? 0,
        ];
    }

    /**
     * Get fast queue count
     */
    public function getFastQueueCount(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM ai_processing_queue WHERE queue_type = 'fast' AND status = 'pending'"
        );
    }

    /**
     * Get scheduled queue count
     */
    public function getScheduledQueueCount(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM images WHERE status = 'scheduled' AND scheduled_at <= NOW()"
        );
    }

    /**
     * Get next scheduled image for processing
     */
    public function getNextScheduledImage(): ?array
    {
        return $this->db->fetch(
            "SELECT i.*, q.id as queue_id
             FROM images i
             JOIN ai_processing_queue q ON q.image_id = i.id
             WHERE i.status = 'scheduled'
               AND i.scheduled_at <= NOW()
               AND q.queue_type = 'scheduled'
               AND q.status = 'pending'
             ORDER BY i.scheduled_at ASC
             LIMIT 1"
        );
    }

    /**
     * Get pending fast queue items
     */
    public function getFastQueueItems(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT q.*, i.storage_path, i.title
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.queue_type = 'fast'
               AND q.status = 'pending'
               AND i.moderation_status = 'approved'
             ORDER BY q.priority DESC, q.created_at ASC
             LIMIT {$limit}"
        );
    }

    /**
     * Create a new upload batch
     */
    public function createBatch(
        int $userId,
        string $scheduleType,
        ?string $scheduledStartAt = null,
        int $publishInterval = 4
    ): array {
        $uuid = $this->generateUuid();

        $this->db->insert('upload_batches', [
            'uuid' => $uuid,
            'user_id' => $userId,
            'schedule_type' => $scheduleType,
            'scheduled_start_at' => $scheduledStartAt,
            'publish_interval_minutes' => $publishInterval,
            'status' => 'pending',
        ]);

        $batchId = (int) $this->db->lastInsertId();

        return [
            'id' => $batchId,
            'uuid' => $uuid,
        ];
    }

    /**
     * Get batch by UUID
     */
    public function getBatchByUuid(string $uuid): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM upload_batches WHERE uuid = :uuid",
            ['uuid' => $uuid]
        );
    }

    /**
     * Get batch status with image details
     */
    public function getBatchStatus(string $uuid): ?array
    {
        $batch = $this->getBatchByUuid($uuid);
        if (!$batch) {
            return null;
        }

        // Get images in this batch
        $images = $this->db->fetchAll(
            "SELECT id, title, status, scheduled_at, slug, thumbnail_path
             FROM images WHERE batch_id = :batch_id
             ORDER BY scheduled_at ASC",
            ['batch_id' => $batch['id']]
        );

        $published = 0;
        $scheduled = 0;
        $processing = 0;

        foreach ($images as $img) {
            if ($img['status'] === 'published') $published++;
            elseif ($img['status'] === 'scheduled') $scheduled++;
            elseif ($img['status'] === 'processing') $processing++;
        }

        return [
            'batch' => $batch,
            'images' => $images,
            'stats' => [
                'total' => count($images),
                'published' => $published,
                'scheduled' => $scheduled,
                'processing' => $processing,
            ],
        ];
    }

    /**
     * Schedule images in a batch with staggered times
     */
    public function scheduleBatchImages(int $batchId, string $startAt, int $intervalMinutes = 4): void
    {
        $batch = $this->db->fetch("SELECT * FROM upload_batches WHERE id = :id", ['id' => $batchId]);
        if (!$batch) {
            return;
        }

        // Get all unpublished images in this batch
        $images = $this->db->fetchAll(
            "SELECT id FROM images WHERE batch_id = :batch_id AND status != 'published' ORDER BY id ASC",
            ['batch_id' => $batchId]
        );

        $startTime = new \DateTime($startAt);
        $totalImages = count($images);

        foreach ($images as $index => $image) {
            $scheduledAt = clone $startTime;
            $minutesToAdd = $index * $intervalMinutes;
            $scheduledAt->modify("+{$minutesToAdd} minutes");

            // Update image status and scheduled time
            $this->db->update('images', [
                'status' => 'scheduled',
                'queue_type' => 'scheduled',
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $image['id']]);

            // Ensure queue entry exists and is reset to pending
            $existingQueue = $this->db->fetch(
                "SELECT id FROM ai_processing_queue WHERE image_id = :image_id",
                ['image_id' => $image['id']]
            );

            if ($existingQueue) {
                // Reset existing queue entry
                $this->db->update('ai_processing_queue', [
                    'status' => 'pending',
                    'queue_type' => 'scheduled',
                    'attempts' => 0,
                    'error_message' => null,
                ], 'id = :id', ['id' => $existingQueue['id']]);
            } else {
                // Create new queue entry
                $this->db->insert('ai_processing_queue', [
                    'image_id' => $image['id'],
                    'task_type' => 'all',
                    'priority' => 5,
                    'status' => 'pending',
                    'queue_type' => 'scheduled',
                ]);
            }
        }

        // Update batch status
        $this->db->update('upload_batches', [
            'status' => 'processing',
            'total_images' => $totalImages,
            'scheduled_start_at' => $startAt,
            'publish_interval_minutes' => $intervalMinutes,
        ], 'id = :id', ['id' => $batchId]);
    }

    /**
     * Update batch progress after an image is published
     */
    public function updateBatchProgress(int $batchId): void
    {
        $stats = $this->db->fetch(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published
             FROM images WHERE batch_id = :batch_id",
            ['batch_id' => $batchId]
        );

        $this->db->update('upload_batches', [
            'processed_images' => (int) $stats['published'],
        ], 'id = :id', ['id' => $batchId]);

        // Mark batch as completed if all images are published
        if ((int) $stats['published'] >= (int) $stats['total']) {
            $this->db->update('upload_batches', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $batchId]);
        }
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
     * Check if queue is busy (> 10 pending items)
     */
    public function isQueueBusy(): bool
    {
        return $this->getFastQueueCount() > 10;
    }

    /**
     * Mark image as published after successful processing
     */
    public function markAsPublished(int $imageId): void
    {
        $image = $this->db->fetch("SELECT batch_id FROM images WHERE id = :id", ['id' => $imageId]);

        $this->db->update('images', [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $imageId]);

        // Update batch progress if part of a batch
        if ($image && $image['batch_id']) {
            $this->updateBatchProgress((int) $image['batch_id']);
        }
    }

    /**
     * Mark queue item as completed
     */
    public function markQueueItemCompleted(int $queueId): void
    {
        $this->db->update('ai_processing_queue', [
            'status' => 'completed',
        ], 'id = :id', ['id' => $queueId]);
    }

    /**
     * Mark queue item as failed
     */
    public function markQueueItemFailed(int $queueId, string $error): void
    {
        $this->db->update('ai_processing_queue', [
            'status' => 'failed',
            'error_message' => $error,
        ], 'id = :id', ['id' => $queueId]);
    }
}
