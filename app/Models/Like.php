<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Like Model - Supports both logged-in users and guests
 */
class Like extends Model
{
    protected string $table = 'likes';
    protected bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'guest_id',
        'likeable_type',
        'likeable_id',
        'ip_address',
    ];

    /**
     * Get or create a guest identifier
     */
    public static function getGuestId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['guest_like_id'])) {
            $_SESSION['guest_like_id'] = bin2hex(random_bytes(16));
        }

        return $_SESSION['guest_like_id'];
    }

    /**
     * Toggle like for a user or guest
     */
    public static function toggle(?int $userId, string $type, int $id): array
    {
        $instance = new static();
        $db = $instance->db();
        $guestId = $userId ? null : self::getGuestId();

        // Check if already liked
        if ($userId) {
            $existing = $db->fetch(
                "SELECT id FROM likes WHERE user_id = :user_id AND likeable_type = :type AND likeable_id = :id",
                ['user_id' => $userId, 'type' => $type, 'id' => $id]
            );
        } else {
            $existing = $db->fetch(
                "SELECT id FROM likes WHERE guest_id = :guest_id AND likeable_type = :type AND likeable_id = :id",
                ['guest_id' => $guestId, 'type' => $type, 'id' => $id]
            );
        }

        if ($existing) {
            // Unlike
            $db->delete('likes', 'id = :id', ['id' => $existing['id']]);
            self::updateCount($type, $id, -1);
            return ['liked' => false, 'count' => self::getCount($type, $id)];
        } else {
            // Like
            $data = [
                'likeable_type' => $type,
                'likeable_id' => $id,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ];

            if ($userId) {
                $data['user_id'] = $userId;
            } else {
                $data['guest_id'] = $guestId;
            }

            $db->insert('likes', $data);
            self::updateCount($type, $id, 1);
            return ['liked' => true, 'count' => self::getCount($type, $id)];
        }
    }

    /**
     * Check if user or guest has liked
     */
    public static function hasLiked(?int $userId, string $type, int $id): bool
    {
        $instance = new static();

        if ($userId) {
            return (bool) $instance->db()->fetchColumn(
                "SELECT 1 FROM likes WHERE user_id = :user_id AND likeable_type = :type AND likeable_id = :id",
                ['user_id' => $userId, 'type' => $type, 'id' => $id]
            );
        }

        // Check guest
        $guestId = self::getGuestId();
        return (bool) $instance->db()->fetchColumn(
            "SELECT 1 FROM likes WHERE guest_id = :guest_id AND likeable_type = :type AND likeable_id = :id",
            ['guest_id' => $guestId, 'type' => $type, 'id' => $id]
        );
    }

    /**
     * Get like count
     */
    public static function getCount(string $type, int $id): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM likes WHERE likeable_type = :type AND likeable_id = :id",
            ['type' => $type, 'id' => $id]
        );
    }

    /**
     * Update count on parent table
     */
    private static function updateCount(string $type, int $id, int $delta): void
    {
        $instance = new static();
        $table = $type === 'blog_post' ? 'blog_posts' : 'images';

        $instance->db()->query(
            "UPDATE {$table} SET like_count = GREATEST(0, like_count + :delta) WHERE id = :id",
            ['delta' => $delta, 'id' => $id]
        );
    }

    /**
     * Get user's liked items
     */
    public static function getUserLiked(int $userId, string $type, int $limit = 20, int $offset = 0): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT likeable_id FROM likes
             WHERE user_id = :user_id AND likeable_type = :type
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['user_id' => $userId, 'type' => $type]
        );
    }

    /**
     * Get IDs of liked items for a user (for bulk checking)
     */
    public static function getLikedIds(int $userId, string $type, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $instance = new static();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $rows = $instance->db()->fetchAll(
            "SELECT likeable_id FROM likes
             WHERE user_id = ? AND likeable_type = ? AND likeable_id IN ({$placeholders})",
            array_merge([$userId, $type], $ids)
        );

        return array_column($rows, 'likeable_id');
    }
}
