<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Services\AI\MetadataGenerator;

/**
 * Process Scheduled Queue Command
 *
 * Processes ONE scheduled image per run.
 * Designed to be run via cron every 4 minutes for staggered publishing.
 * Exits immediately after processing one image.
 *
 * Usage: php app/Console/cli.php images:process-scheduled
 */
class ProcessScheduledQueueCommand extends Command
{
    protected string $name = 'images:process-scheduled';
    protected string $description = 'Process ONE scheduled image (for cron every 4 minutes)';

    public function run(array $args): int
    {
        $this->info("Processing scheduled queue...");

        $db = $this->db();

        // Check how many scheduled images are ready
        $ready = (int) $db->fetchColumn(
            "SELECT COUNT(*)
             FROM images i
             JOIN ai_processing_queue q ON q.image_id = i.id
             WHERE i.status = 'scheduled'
               AND i.scheduled_at <= NOW()
               AND q.queue_type = 'scheduled'
               AND q.status = 'pending'"
        );

        if ($ready === 0) {
            $this->line("No scheduled images ready for processing.");

            // Show upcoming scheduled items
            $upcoming = $db->fetch(
                "SELECT i.scheduled_at, i.title
                 FROM images i
                 WHERE i.status = 'scheduled'
                   AND i.scheduled_at > NOW()
                 ORDER BY i.scheduled_at ASC
                 LIMIT 1"
            );

            if ($upcoming) {
                $this->line("Next scheduled: {$upcoming['title']} at {$upcoming['scheduled_at']}");
            }

            return 0;
        }

        $this->line("Found {$ready} scheduled image(s) ready");

        $generator = new MetadataGenerator();
        $result = $generator->processScheduledItem();

        $this->line('');

        if ($result['processed'] > 0) {
            if ($result['failed'] > 0) {
                $this->warn("Published with warnings: {$result['message']}");
            } else {
                $this->info("Published: Image #{$result['image_id']} - {$result['title']}");
            }
        } else {
            $this->line($result['message']);
        }

        // Show remaining scheduled count
        $remaining = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM images WHERE status = 'scheduled'"
        );

        $this->line('');
        $this->line("Remaining scheduled: {$remaining}");

        return $result['failed'] > 0 ? 1 : 0;
    }
}
