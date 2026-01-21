<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;

/**
 * Trends Fetch Command
 *
 * Fetches trending keywords from external sources.
 */
class TrendsFetchCommand extends Command
{
    protected string $name = 'trends:fetch';
    protected string $description = 'Fetch trending keywords';

    public function run(array $args): int
    {
        $this->info('Fetching trending keywords...');

        $db = $this->db();

        // Update tag trend scores based on recent usage
        $this->line('  - Calculating tag trend scores...');

        // Get tags used in last 7 days with growth rate
        $recentTags = $db->fetchAll(
            "SELECT t.id, t.name, t.usage_count,
                    COUNT(it.image_id) as recent_count
             FROM tags t
             LEFT JOIN image_tags it ON t.id = it.tag_id
             LEFT JOIN images i ON it.image_id = i.id
                AND i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY t.id
             HAVING recent_count > 0
             ORDER BY recent_count DESC"
        );

        $updated = 0;
        foreach ($recentTags as $tag) {
            // Calculate trend score based on recent activity vs total
            $recentRatio = $tag['usage_count'] > 0
                ? $tag['recent_count'] / $tag['usage_count']
                : 0;

            $trendScore = min(100, ($tag['recent_count'] * 10) + ($recentRatio * 50));

            $db->update('tags', [
                'trend_score' => round($trendScore, 2),
                'is_trending' => $trendScore > 20,
            ], 'id = :where_id', ['where_id' => $tag['id']]);

            $updated++;
        }

        // Reset scores for tags with no recent activity
        $db->execute(
            "UPDATE tags SET trend_score = 0, is_trending = 0
             WHERE id NOT IN (
                 SELECT DISTINCT tag_id FROM image_tags it
                 JOIN images i ON it.image_id = i.id
                 WHERE i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             )"
        );

        $this->line('');
        $this->info("Updated trend scores for {$updated} tags.");

        // Show top trending tags
        $topTrending = $db->fetchAll(
            "SELECT name, trend_score FROM tags WHERE is_trending = 1 ORDER BY trend_score DESC LIMIT 10"
        );

        if (!empty($topTrending)) {
            $this->line('');
            $this->line('Top trending tags:');
            foreach ($topTrending as $tag) {
                $this->line("  - {$tag['name']} (score: {$tag['trend_score']})");
            }
        }

        return 0;
    }
}
