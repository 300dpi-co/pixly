<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\ClaudeAIService;
use App\Services\ReplicateAIService;
use App\Services\AIHordeService;
use App\Services\OpenRouterService;
use App\Services\AI\MetadataGenerator;

/**
 * Admin AI Controller
 *
 * Handles AI processing of images.
 */
class AIController extends Controller
{
    /**
     * Show AI processing page
     */
    public function index(): Response
    {
        $db = $this->db();

        // Ensure AI settings exist in database
        $this->ensureAISettingsExist($db);

        // Clean up: Delete old completed entries (keep queue clean)
        $db->execute("DELETE FROM ai_processing_queue WHERE status = 'completed'");

        // Clean up: Remove orphaned queue entries for already-published images
        $db->execute(
            "DELETE FROM ai_processing_queue
             WHERE image_id IN (
                 SELECT id FROM images
                 WHERE status = 'published' AND ai_processed_at IS NOT NULL
             )"
        );

        // Reset any stuck "processing" entries back to pending (older than 10 min)
        $db->execute(
            "UPDATE ai_processing_queue SET status = 'pending'
             WHERE status = 'processing' AND started_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
        );

        $generator = new MetadataGenerator();
        $provider = $generator->getProvider();

        // Check if the current provider is configured
        if ($provider === 'openrouter') {
            $ai = new OpenRouterService();
        } elseif ($provider === 'aihorde') {
            $ai = new AIHordeService();
        } elseif ($provider === 'replicate') {
            $ai = new ReplicateAIService();
        } else {
            $ai = new ClaudeAIService();
        }

        // Get IMAGE stats (synced with admin/images)
        $imageStats = [
            'draft' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE status = 'draft'"),
            'published' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images WHERE status = 'published'"),
            'total' => (int) $db->fetchColumn("SELECT COUNT(*) FROM images"),
        ];

        // Get QUEUE stats (only pending/failed since completed are deleted)
        $queueStats = [
            'pending' => (int) $db->fetchColumn("SELECT COUNT(*) FROM ai_processing_queue WHERE status = 'pending'"),
            'processing' => (int) $db->fetchColumn("SELECT COUNT(*) FROM ai_processing_queue WHERE status = 'processing'"),
            'failed' => (int) $db->fetchColumn("SELECT COUNT(*) FROM ai_processing_queue WHERE status = 'failed'"),
        ];

        // Get queue items (only pending/processing/failed - no completed)
        $queue = $db->fetchAll(
            "SELECT q.*, i.title, i.thumbnail_path, i.status as image_status
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.status IN ('pending', 'processing', 'failed')
             ORDER BY
                CASE q.status
                    WHEN 'processing' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'failed' THEN 3
                END,
                q.priority DESC,
                q.created_at DESC
             LIMIT 100"
        );

        // Get draft images not in queue (need to be queued)
        $unqueued = $db->fetchAll(
            "SELECT i.id, i.title, i.thumbnail_path, i.created_at, i.status
             FROM images i
             LEFT JOIN ai_processing_queue q ON i.id = q.image_id
             WHERE i.status = 'draft' AND q.id IS NULL
             ORDER BY i.created_at DESC
             LIMIT 50"
        );

        return $this->view('admin/ai/index', [
            'title' => 'AI Processing',
            'currentPage' => 'ai',
            'isConfigured' => $ai->isConfigured(),
            'provider' => $provider,
            'providerName' => match($provider) {
                'openrouter' => 'OpenRouter (Qwen 2.5 VL)',
                'aihorde' => 'AI Horde (Caption + Tags)',
                'replicate' => 'Replicate (LLaVA)',
                default => 'Claude',
            },
            'imageStats' => $imageStats,
            'queueStats' => $queueStats,
            'queue' => $queue,
            'unqueued' => $unqueued,
        ], 'admin');
    }

    /**
     * Process queued images
     */
    public function process(): Response
    {
        $generator = new MetadataGenerator();

        $limit = (int) $this->request->input('limit', 5);
        $limit = min($limit, 50); // Max 50 at a time

        $results = $generator->processQueue($limit);

        if ($this->request->isAjax()) {
            return $this->json($results);
        }

        if ($results['processed'] > 0) {
            return $this->redirectWithSuccess(
                url('/admin/ai'),
                "Processed {$results['processed']} image(s). " .
                ($results['failed'] > 0 ? "{$results['failed']} failed." : '')
            );
        }

        return $this->redirectWithError(
            url('/admin/ai'),
            $results['errors'][0] ?? 'No images to process'
        );
    }

    /**
     * Process all pending images in queue (batch mode)
     */
    public function processAll(): Response
    {
        $db = $this->db();
        $generator = new MetadataGenerator();

        // Get count of pending items
        $pendingCount = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM ai_processing_queue WHERE status = 'pending'"
        );

        if ($pendingCount === 0) {
            return $this->redirectWithSuccess(url('/admin/ai'), 'No pending images to process.');
        }

        // Process in batches to avoid timeout
        $batchSize = 10;
        $totalProcessed = 0;
        $totalFailed = 0;
        $maxBatches = 5; // Process max 50 images per request

        for ($batch = 0; $batch < $maxBatches; $batch++) {
            $results = $generator->processQueue($batchSize);
            $totalProcessed += $results['processed'];
            $totalFailed += $results['failed'];

            // Stop if no more items
            if ($results['processed'] === 0 && $results['failed'] === 0) {
                break;
            }
        }

        $remaining = $pendingCount - $totalProcessed - $totalFailed;

        $message = "Processed {$totalProcessed} image(s).";
        if ($totalFailed > 0) {
            $message .= " {$totalFailed} failed.";
        }
        if ($remaining > 0) {
            $message .= " {$remaining} still pending - click again to continue.";
        }

        return $this->redirectWithSuccess(url('/admin/ai'), $message);
    }

    /**
     * Add images to processing queue
     */
    public function queue(): Response
    {
        $db = $this->db();
        $imageIds = $this->request->input('image_ids', []);

        if (empty($imageIds)) {
            // Queue all unprocessed images
            $unprocessed = $db->fetchAll(
                "SELECT i.id FROM images i
                 LEFT JOIN ai_processing_queue q ON i.id = q.image_id
                 WHERE i.ai_processed_at IS NULL AND q.id IS NULL
                 LIMIT 50"
            );
            $imageIds = array_column($unprocessed, 'id');
        }

        if (empty($imageIds)) {
            return $this->redirectWithError(url('/admin/ai'), 'No images to queue');
        }

        $queued = 0;
        foreach ($imageIds as $imageId) {
            // Check if already in queue
            $existing = $db->fetch(
                "SELECT id FROM ai_processing_queue WHERE image_id = :id AND status IN ('pending', 'processing')",
                ['id' => $imageId]
            );

            if (!$existing) {
                $db->insert('ai_processing_queue', [
                    'image_id' => (int) $imageId,
                    'task_type' => 'all',
                    'priority' => 5,
                    'status' => 'pending',
                ]);
                $queued++;
            }
        }

        return $this->redirectWithSuccess(
            url('/admin/ai'),
            "Added {$queued} image(s) to the processing queue."
        );
    }

    /**
     * Process a single image immediately
     */
    public function processSingle(string|int $id): Response
    {
        $id = (int) $id;
        $generator = new MetadataGenerator();

        $success = $generator->processImage($id);

        if ($this->request->isAjax()) {
            if ($success) {
                return $this->json(['success' => true]);
            }
            return $this->json(['success' => false, 'errors' => $generator->getErrors()], 400);
        }

        if ($success) {
            return $this->redirectWithSuccess(url('/admin/ai'), 'Image processed successfully.');
        }

        return $this->redirectWithError(
            url('/admin/ai'),
            'Processing failed: ' . implode(', ', $generator->getErrors())
        );
    }

    /**
     * Clear failed items from queue
     */
    public function clearFailed(): Response
    {
        $db = $this->db();
        $db->execute("DELETE FROM ai_processing_queue WHERE status = 'failed'");

        return $this->redirectWithSuccess(url('/admin/ai'), 'Cleared failed items from queue.');
    }

    /**
     * Retry failed items
     */
    public function retryFailed(): Response
    {
        $db = $this->db();
        $db->execute(
            "UPDATE ai_processing_queue SET status = 'pending', attempts = 0, error_message = NULL WHERE status = 'failed'"
        );

        return $this->redirectWithSuccess(url('/admin/ai'), 'Failed items queued for retry.');
    }

    /**
     * Ensure all AI-related settings exist in database
     */
    private function ensureAISettingsExist($db): void
    {
        // Remove old unused API keys
        $db->execute("DELETE FROM settings WHERE setting_key IN ('deepseek_api_key', 'deepinfra_api_key')");

        $requiredSettings = [
            [
                'setting_key' => 'ai_provider',
                'setting_value' => 'huggingface',
                'setting_type' => 'string',
                'description' => 'AI provider (huggingface, replicate, or claude)',
                'is_public' => 0,
            ],
            [
                'setting_key' => 'huggingface_api_key',
                'setting_value' => '',
                'setting_type' => 'encrypted',
                'description' => 'Hugging Face API key for Florence-2 + WD14 (recommended for adult sites)',
                'is_public' => 0,
            ],
            [
                'setting_key' => 'replicate_api_key',
                'setting_value' => '',
                'setting_type' => 'encrypted',
                'description' => 'Replicate API key for LLaVA image analysis',
                'is_public' => 0,
            ],
            [
                'setting_key' => 'claude_api_key',
                'setting_value' => '',
                'setting_type' => 'encrypted',
                'description' => 'Claude API key for image analysis',
                'is_public' => 0,
            ],
        ];

        foreach ($requiredSettings as $setting) {
            $exists = $db->fetch(
                "SELECT 1 FROM settings WHERE setting_key = :key",
                ['key' => $setting['setting_key']]
            );

            if (!$exists) {
                $db->insert('settings', $setting);
            }
        }
    }
}
