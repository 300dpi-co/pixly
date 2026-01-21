<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Blog Comment Model
 */
class BlogComment extends Model
{
    protected string $table = 'blog_comments';

    protected array $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'guest_name',
        'guest_email',
        'guest_website',
        'content',
        'status',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get comments for a post (threaded)
     */
    public static function forPost(int $postId): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT c.*, u.username, u.email as user_email
             FROM {$instance->table} c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.post_id = :post_id AND c.status = 'approved'
             ORDER BY c.created_at ASC",
            ['post_id' => $postId]
        );

        // Build threaded structure
        $comments = [];
        $indexed = [];

        foreach ($rows as $row) {
            $row['replies'] = [];
            $indexed[$row['id']] = $row;
        }

        foreach ($indexed as $id => &$comment) {
            if ($comment['parent_id'] && isset($indexed[$comment['parent_id']])) {
                $indexed[$comment['parent_id']]['replies'][] = &$comment;
            } else {
                $comments[] = &$comment;
            }
        }

        return $comments;
    }

    /**
     * Get all comments for admin
     */
    public static function allForAdmin(array $filters = []): array
    {
        $instance = new static();
        $where = '1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $where .= ' AND c.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['post_id'])) {
            $where .= ' AND c.post_id = :post_id';
            $params['post_id'] = $filters['post_id'];
        }

        $rows = $instance->db()->fetchAll(
            "SELECT c.*, u.username, p.title as post_title, p.slug as post_slug
             FROM {$instance->table} c
             LEFT JOIN users u ON c.user_id = u.id
             LEFT JOIN blog_posts p ON c.post_id = p.id
             WHERE {$where}
             ORDER BY c.created_at DESC",
            $params
        );

        return $rows;
    }

    /**
     * Get pending comments count
     */
    public static function pendingCount(): int
    {
        $instance = new static();
        return (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM {$instance->table} WHERE status = 'pending'"
        );
    }

    /**
     * Approve comment
     */
    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();

        // Update post comment count
        $this->updatePostCommentCount();
    }

    /**
     * Mark as spam
     */
    public function markSpam(): void
    {
        $this->status = 'spam';
        $this->save();
        $this->updatePostCommentCount();
    }

    /**
     * Trash comment
     */
    public function trash(): void
    {
        $this->status = 'trash';
        $this->save();
        $this->updatePostCommentCount();
    }

    /**
     * Update post comment count
     */
    private function updatePostCommentCount(): void
    {
        $count = (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM blog_comments WHERE post_id = :post_id AND status = 'approved'",
            ['post_id' => $this->post_id]
        );
        $this->db()->update('blog_posts', ['comment_count' => $count], 'id = ?', [$this->post_id]);
    }

    /**
     * Get author name
     */
    public function getAuthorName(): string
    {
        return $this->username ?? $this->guest_name ?? 'Anonymous';
    }

    /**
     * Get author email
     */
    public function getAuthorEmail(): string
    {
        return $this->user_email ?? $this->guest_email ?? '';
    }

    /**
     * Get gravatar URL
     */
    public function getGravatarUrl(int $size = 48): string
    {
        $email = strtolower(trim($this->getAuthorEmail()));
        $hash = md5($email);
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }
}
