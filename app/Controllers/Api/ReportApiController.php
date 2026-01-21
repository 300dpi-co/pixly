<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;

/**
 * Report API Controller
 *
 * Handles image report/flag operations via API.
 * Used for copyright claims, DMCA requests, and content flagging.
 */
class ReportApiController extends Controller
{
    /**
     * Store a new report
     */
    public function store(): Response
    {
        $db = $this->db();

        $imageId = (int) $this->request->input('image_id');
        $reason = trim($this->request->input('reason', ''));
        $details = trim($this->request->input('details', ''));
        $email = trim($this->request->input('email', ''));

        // Validate reason
        $validReasons = ['copyright', 'dmca', 'inappropriate', 'spam', 'other'];
        if (empty($reason) || !in_array($reason, $validReasons)) {
            return $this->json(['error' => 'Please select a valid reason'], 400);
        }

        // Check if image exists
        $image = $db->fetch(
            "SELECT id, title FROM images WHERE id = :id",
            ['id' => $imageId]
        );

        if (!$image) {
            return $this->json(['error' => 'Image not found'], 404);
        }

        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'Please provide a valid email address'], 400);
        }

        // Rate limiting: Check for recent reports from same IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $recentReport = $db->fetch(
            "SELECT id FROM image_reports
             WHERE ip_address = :ip AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
             LIMIT 1",
            ['ip' => $ip]
        );

        if ($recentReport) {
            return $this->json(['error' => 'You have already submitted a report recently. Please wait before submitting another.'], 429);
        }

        // Get user ID if logged in
        $userId = $_SESSION['user_id'] ?? null;

        // Insert report
        $db->insert('image_reports', [
            'image_id' => $imageId,
            'user_id' => $userId,
            'reason' => $reason,
            'details' => $details ?: null,
            'reporter_email' => $email ?: null,
            'ip_address' => $ip,
            'status' => 'pending',
        ]);

        $reportId = (int) $db->lastInsertId();

        // Log the report for admin notification
        error_log("New image report #$reportId: Image #{$imageId} ({$image['title']}) - Reason: $reason");

        return $this->json([
            'success' => true,
            'message' => 'Report submitted successfully. We will review it shortly.',
            'report_id' => $reportId,
        ], 201);
    }
}
