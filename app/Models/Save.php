<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Save/Bookmark Model
 */
class Save extends Model
{
    protected string $table = 'saves';
    protected bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'saveable_type',
        'saveable_id',
    ];

    /**
     * Toggle save for a user
     */
    public static function toggle(int $userId, string $type, int $id): array
    {
        $instance = new static();
        $db = $instance->db();

        // Check if already saved
        $existing = $db->fetch(
            "SELECT id FROM saves WHERE user_id = :user_id AND saveable_type = :type AND saveable_id = :id",
            ['user_id' => $userId, 'type' => $type, 'id' => $id]
        );

        if ($existing) {
            // Unsave
            $db->delete('saves', 'id = :id', ['id' => $existing['id']]);
            self::updateCount($type, $id, -1);
            return ['saved' => false, 'count' => self::getCount($type, $id)];
        } else {
            // Save
            $db->insert('saves', [
                'user_id' => $userId,
                'saveable_type' => $type,
                'saveable_id' => $id,
            ]);
            self::updateCount($type, $id, 1);
            return ['saved' => true, 'count' => self::getCount($type, $id)];
        }
    }

    /**
     * Check if user has saved
     */
    public static function hasSaved(int $userId, string $type, int $id): bool
    {
        $instance = new static();
        return (bool) $instance->db()->fetchColumn(
            "SELECT 1 FROM saves WHERE user_id = :user_id AND saveable_type = :type AND saveable_id = :id",
            ['user_id' => $userId, 'type' => $type, 'id' => $id]
        );
    }

    /**
     * Get save count
     */
    public static function getCount(string $type, int $id): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM saves WHERE saveable_type = :type AND saveable_id = :id",
            ['type' => $type, 'id' => $id]
        );
    }

    /**
     * Update count on parent table
     */
    private static function updateCount(string $type, int $id, int $delta): void
    {
        $instance = new static();

        if ($type === 'blog_post') {
            $instance->db()->query(
                "UPDATE blog_posts SET save_count = GREATEST(0, save_count + :delta) WHERE id = :id",
                ['delta' => $delta, 'id' => $id]
            );
        }
        // Images don't have save_count column
    }

    /**
     * Get user's saved items
     */
    public static function getUserSaved(int $userId, string $type, int $limit = 20, int $offset = 0): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT saveable_id FROM saves
             WHERE user_id = :user_id AND saveable_type = :type
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['user_id' => $userId, 'type' => $type]
        );
    }

    /**
     * Get IDs of saved items for a user (for bulk checking)
     */
    public static function getSavedIds(int $userId, string $type, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $instance = new static();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $rows = $instance->db()->fetchAll(
            "SELECT saveable_id FROM saves
             WHERE user_id = ? AND saveable_type = ? AND saveable_id IN ({$placeholders})",
            array_merge([$userId, $type], $ids)
        );

        return array_column($rows, 'saveable_id');
    }

    /**
     * Count user's saved items
     */
    public static function countUserSaved(int $userId, string $type): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM saves WHERE user_id = :user_id AND saveable_type = :type",
            ['user_id' => $userId, 'type' => $type]
        );
    }
}
