<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Services\AI\MetadataGenerator;

/**
 * AI Process Command
 *
 * Processes images in the AI queue.
 */
class AiProcessCommand extends Command
{
    protected string $name = 'ai:process';
    protected string $description = 'Process images in AI queue';

    public function run(array $args): int
    {
        $options = $this->parseOptions($args);
        $limit = (int) ($options['limit'] ?? 10);

        $this->info("Processing AI queue (limit: {$limit})...");

        $generator = new MetadataGenerator();
        $results = $generator->processQueue($limit);

        $this->line('');
        $this->info("Processed: {$results['processed']}");

        if ($results['failed'] > 0) {
            $this->warn("Failed: {$results['failed']}");
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        // Show queue status
        $db = $this->db();
        $pending = $db->fetchColumn("SELECT COUNT(*) FROM ai_processing_queue WHERE status = 'pending'");
        $this->line('');
        $this->line("Remaining in queue: {$pending}");

        return $results['failed'] > 0 ? 1 : 0;
    }
}
