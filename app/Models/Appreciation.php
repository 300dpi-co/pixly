<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Appreciation Model - For recognizing quality content
 *
 * Appreciations are different from likes:
 * - Requires logged-in user
 * - Contributes to "Top Contributors" ranking (combined with likes)
 * - Shows gratitude for high-quality content
 */
class Appreciation extends Model
{
    protected string $table = 'appreciations';
    protected bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'image_id',
    ];

    /**
     * Toggle appreciation for an image
     */
    public static function toggle(int $userId, int $imageId): array
    {
        $instance = new static();
        $db = $instance->db();

        // Check if already appreciated
        $existing = $db->fetch(
            "SELECT id FROM appreciations WHERE user_id = :user_id AND image_id = :image_id",
            ['user_id' => $userId, 'image_id' => $imageId]
        );

        if ($existing) {
            // Remove appreciation
            $db->delete('appreciations', 'id = :id', ['id' => $existing['id']]);
            self::updateCount($imageId, -1);
            return ['appreciated' => false, 'count' => self::getCount($imageId)];
        } else {
            // Add appreciation
            $db->insert('appreciations', [
                'user_id' => $userId,
                'image_id' => $imageId,
            ]);
            self::updateCount($imageId, 1);
            return ['appreciated' => true, 'count' => self::getCount($imageId)];
        }
    }

    /**
     * Check if user has appreciated an image
     */
    public static function hasAppreciated(int $userId, int $imageId): bool
    {
        $instance = new static();
        return (bool) $instance->db()->fetchColumn(
            "SELECT 1 FROM appreciations WHERE user_id = :user_id AND image_id = :image_id",
            ['user_id' => $userId, 'image_id' => $imageId]
        );
    }

    /**
     * Get appreciation count for an image
     */
    public static function getCount(int $imageId): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM appreciations WHERE image_id = :id",
            ['id' => $imageId]
        );
    }

    /**
     * Update count on images table
     */
    private static function updateCount(int $imageId, int $delta): void
    {
        $instance = new static();
        $instance->db()->query(
            "UPDATE images SET appreciate_count = GREATEST(0, appreciate_count + :delta) WHERE id = :id",
            ['delta' => $delta, 'id' => $imageId]
        );
    }

    /**
     * Get IDs of appreciated images for a user (for bulk checking)
     */
    public static function getAppreciatedIds(int $userId, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $instance = new static();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $rows = $instance->db()->fetchAll(
            "SELECT image_id FROM appreciations WHERE user_id = ? AND image_id IN ({$placeholders})",
            array_merge([$userId], $ids)
        );

        return array_column($rows, 'image_id');
    }

    /**
     * Get top contributors based on combined likes + appreciations
     *
     * @param int $limit Number of contributors to return
     * @return array List of top contributors with their stats
     */
    public static function getTopContributors(int $limit = 10): array
    {
        $instance = new static();
        $limit = (int) $limit; // Ensure integer

        return $instance->db()->fetchAll(
            "SELECT
                u.id,
                u.username,
                u.display_name,
                u.avatar_path,
                COUNT(DISTINCT i.id) as image_count,
                COALESCE(SUM(i.favorite_count), 0) as total_likes,
                COALESCE(SUM(i.appreciate_count), 0) as total_appreciations,
                COALESCE(SUM(i.favorite_count), 0) + COALESCE(SUM(i.appreciate_count), 0) as total_score
            FROM users u
            INNER JOIN images i ON i.user_id = u.id AND i.status = 'published'
            WHERE u.status = 'active'
            GROUP BY u.id, u.username, u.display_name, u.avatar_path
            HAVING total_score > 0
            ORDER BY total_score DESC, image_count DESC
            LIMIT {$limit}"
        );
    }

    /**
     * Get user's appreciated images
     */
    public static function getUserAppreciated(int $userId, int $limit = 20, int $offset = 0): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT image_id FROM appreciations
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['user_id' => $userId]
        );
    }
}
