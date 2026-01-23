<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Services\AI\MetadataGenerator;

/**
 * Process Fast Queue Command
 *
 * Processes all pending fast queue items (trusted user uploads).
 * No artificial delays - processes as quickly as possible.
 * Run via cron every minute.
 *
 * Usage: php app/Console/cli.php images:process-fast [--limit=50]
 */
class ProcessFastQueueCommand extends Command
{
    protected string $name = 'images:process-fast';
    protected string $description = 'Process fast queue (trusted users) without delays';

    public function run(array $args): int
    {
        $options = $this->parseOptions($args);
        $limit = (int) ($options['limit'] ?? 50);

        $this->info("Processing fast queue (limit: {$limit})...");

        $db = $this->db();

        // Check pending count first
        $pending = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.queue_type = 'fast'
               AND q.status = 'pending'
               AND i.moderation_status = 'approved'"
        );

        if ($pending === 0) {
            $this->line("No items in fast queue.");
            return 0;
        }

        $this->line("Found {$pending} pending item(s)");

        $generator = new MetadataGenerator();
        $results = $generator->processFastQueue($limit);

        $this->line('');
        $this->info("Processed: {$results['processed']}");

        if ($results['failed'] > 0) {
            $this->warn("Failed: {$results['failed']}");
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        // Show remaining queue status
        $remaining = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM ai_processing_queue q
             JOIN images i ON q.image_id = i.id
             WHERE q.queue_type = 'fast'
               AND q.status = 'pending'
               AND i.moderation_status = 'approved'"
        );

        $this->line('');
        $this->line("Remaining in fast queue: {$remaining}");

        return $results['failed'] > 0 ? 1 : 0;
    }
}
