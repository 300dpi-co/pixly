<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AI\MetadataGenerator;

class ModerationController extends Controller
{
    public function index(): Response
    {
        $db = $this->db();

        // Get pending images with uploader info
        $pending = $db->fetchAll(
            "SELECT i.*, u.username as uploader_name, u.is_trusted as uploader_trusted
             FROM images i
             LEFT JOIN users u ON i.uploaded_by = u.id
             WHERE i.moderation_status = 'pending'
             ORDER BY i.created_at DESC"
        );

        // Get moderation stats
        $stats = [
            'pending' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE moderation_status = 'pending'"),
            'approved_today' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM images WHERE moderation_status = 'approved' AND DATE(updated_at) = CURDATE()"
            ),
            'rejected_today' => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM images WHERE moderation_status = 'rejected' AND DATE(updated_at) = CURDATE()"
            ),
        ];

        // Get trusted users
        $trustedUsers = $db->fetchAll(
            "SELECT id, username, email, created_at FROM users WHERE is_trusted = 1 ORDER BY username"
        );

        // Get users for "add trusted" dropdown (active non-trusted users)
        $availableUsers = $db->fetchAll(
            "SELECT id, username, email FROM users
             WHERE is_trusted = 0 AND status = 'active' AND role = 'user'
             ORDER BY username"
        );

        return $this->view('admin/moderation/index', [
            'title' => 'Moderation',
            'currentPage' => 'moderation',
            'pending' => $pending,
            'stats' => $stats,
            'trustedUsers' => $trustedUsers,
            'availableUsers' => $availableUsers,
        ], 'admin');
    }

    /**
     * Approve single image
     */
    public function approve(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $image = $db->fetch("SELECT * FROM images WHERE id = :id", ['id' => $id]);

        if (!$image) {
            return Response::json(['error' => 'Image not found'], 404);
        }

        // Update moderation status (AI will set status to published after processing)
        $db->update('images', [
            'moderation_status' => 'approved',
            'moderated_by' => $_SESSION['user_id'] ?? null,
            'moderated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        // Check if already in queue
        $inQueue = $db->fetch(
            "SELECT id, status FROM ai_processing_queue WHERE image_id = :id",
            ['id' => $id]
        );

        if (!$inQueue) {
            // Add to queue
            $db->insert('ai_processing_queue', [
                'image_id' => $id,
                'task_type' => 'all',
                'priority' => 10, // High priority for approved images
                'status' => 'pending',
            ]);
        } elseif ($inQueue['status'] === 'failed') {
            // Reset failed items
            $db->update('ai_processing_queue', [
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
            ], 'id = :id', ['id' => $inQueue['id']]);
        }

        // Process AI immediately - generates metadata and publishes
        $this->processImageAI($id);

        if ($this->isAjax()) {
            return Response::json(['success' => true]);
        }

        session_flash('success', 'Image approved and processed.');
        return Response::redirect('/admin/moderation');
    }

    /**
     * Reject single image
     */
    public function reject(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $reason = $_POST['reason'] ?? '';

        $db->update('images', [
            'moderation_status' => 'rejected',
            'status' => 'rejected',
            'moderation_notes' => $reason,
            'moderated_by' => $_SESSION['user_id'] ?? null,
            'moderated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        if ($this->isAjax()) {
            return Response::json(['success' => true]);
        }

        session_flash('success', 'Image rejected.');
        return Response::redirect('/admin/moderation');
    }

    /**
     * Bulk approve images
     */
    public function bulkApprove(): Response
    {
        $ids = $_POST['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return Response::json(['error' => 'No images selected'], 400);
        }

        $db = $this->db();
        $moderatorId = $_SESSION['user_id'] ?? null;
        $now = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($ids as $id) {
            $id = (int) $id;

            // Update moderation status
            $db->update('images', [
                'moderation_status' => 'approved',
                'moderated_by' => $moderatorId,
                'moderated_at' => $now,
            ], 'id = :id AND moderation_status = :status', [
                'id' => $id,
                'status' => 'pending'
            ]);

            // Add to AI queue if not present
            $inQueue = $db->fetch(
                "SELECT id, status FROM ai_processing_queue WHERE image_id = :id",
                ['id' => $id]
            );

            if (!$inQueue) {
                $db->insert('ai_processing_queue', [
                    'image_id' => $id,
                    'task_type' => 'all',
                    'priority' => 10,
                    'status' => 'pending',
                ]);
            } elseif ($inQueue['status'] === 'failed') {
                $db->update('ai_processing_queue', [
                    'status' => 'pending',
                    'attempts' => 0,
                    'error_message' => null,
                ], 'id = :id', ['id' => $inQueue['id']]);
            }

            // Process AI immediately
            $this->processImageAI($id);

            $count++;
        }

        if ($this->isAjax()) {
            return Response::json(['success' => true, 'count' => $count]);
        }

        session_flash('success', "{$count} images approved and processed.");
        return Response::redirect('/admin/moderation');
    }

    /**
     * Bulk reject images
     */
    public function bulkReject(): Response
    {
        $ids = $_POST['ids'] ?? [];
        $reason = $_POST['reason'] ?? 'Bulk rejected';

        if (empty($ids) || !is_array($ids)) {
            return Response::json(['error' => 'No images selected'], 400);
        }

        $db = $this->db();
        $moderatorId = $_SESSION['user_id'] ?? null;
        $now = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($ids as $id) {
            $id = (int) $id;

            $db->update('images', [
                'moderation_status' => 'rejected',
                'status' => 'rejected',
                'moderation_notes' => $reason,
                'moderated_by' => $moderatorId,
                'moderated_at' => $now,
            ], 'id = :id AND moderation_status = :status', [
                'id' => $id,
                'status' => 'pending'
            ]);

            $count++;
        }

        if ($this->isAjax()) {
            return Response::json(['success' => true, 'count' => $count]);
        }

        session_flash('success', "{$count} images rejected.");
        return Response::redirect('/admin/moderation');
    }

    /**
     * Add trusted user
     */
    public function addTrusted(): Response
    {
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            session_flash('error', 'Invalid user.');
            return Response::redirect('/admin/moderation');
        }

        $db = $this->db();

        $db->update('users', [
            'is_trusted' => 1,
        ], 'id = :id', ['id' => $userId]);

        session_flash('success', 'User marked as trusted. Their uploads will skip moderation.');
        return Response::redirect('/admin/moderation');
    }

    /**
     * Remove trusted user
     */
    public function removeTrusted(string|int $id): Response
    {
        $id = (int) $id;
        $db = $this->db();

        $db->update('users', [
            'is_trusted' => 0,
        ], 'id = :id', ['id' => $id]);

        if ($this->isAjax()) {
            return Response::json(['success' => true]);
        }

        session_flash('success', 'User removed from trusted list.');
        return Response::redirect('/admin/moderation');
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Process image with AI immediately
     * Generates metadata and auto-publishes
     */
    private function processImageAI(int $imageId): void
    {
        try {
            $generator = new MetadataGenerator();

            if (!$generator->getProvider()) {
                return; // AI not configured
            }

            $success = $generator->processImage($imageId);

            if ($success) {
                $db = $this->db();
                $db->update(
                    'ai_processing_queue',
                    ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')],
                    'image_id = :image_id AND status IN (:s1, :s2)',
                    ['image_id' => $imageId, 's1' => 'pending', 's2' => 'processing']
                );
            }
        } catch (\Throwable $e) {
            error_log('AI processing failed for image ' . $imageId . ': ' . $e->getMessage());
        }
    }
}
