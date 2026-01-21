<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

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

        $db->update('images', [
            'moderation_status' => 'approved',
            'status' => 'published',
            'moderated_by' => $_SESSION['user_id'] ?? null,
            'moderated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);

        // Add to AI queue if not already processed
        $inQueue = $db->fetch(
            "SELECT id FROM ai_processing_queue WHERE image_id = :id",
            ['id' => $id]
        );

        if (!$inQueue && empty($image['ai_generated_title'])) {
            $db->insert('ai_processing_queue', [
                'image_id' => $id,
                'task_type' => 'all',
                'priority' => 5,
                'status' => 'pending',
            ]);
        }

        if ($this->isAjax()) {
            return Response::json(['success' => true]);
        }

        session_flash('success', 'Image approved.');
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

            $db->update('images', [
                'moderation_status' => 'approved',
                'status' => 'published',
                'moderated_by' => $moderatorId,
                'moderated_at' => $now,
            ], 'id = :id AND moderation_status = :status', [
                'id' => $id,
                'status' => 'pending'
            ]);

            // Add to AI queue
            $inQueue = $db->fetch(
                "SELECT id FROM ai_processing_queue WHERE image_id = :id",
                ['id' => $id]
            );

            if (!$inQueue) {
                $db->insert('ai_processing_queue', [
                    'image_id' => $id,
                    'task_type' => 'all',
                    'priority' => 5,
                    'status' => 'pending',
                ]);
            }

            $count++;
        }

        if ($this->isAjax()) {
            return Response::json(['success' => true, 'count' => $count]);
        }

        session_flash('success', "{$count} images approved.");
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
}
