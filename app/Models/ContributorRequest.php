<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * ContributorRequest Model
 *
 * Handles contributor role requests from users.
 */
class ContributorRequest extends Model
{
    protected string $table = 'contributor_requests';

    protected array $fillable = [
        'user_id',
        'status',
        'request_reason',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Create a new contributor request
     */
    public static function createRequest(int $userId, ?string $reason = null): self
    {
        return self::create([
            'user_id' => $userId,
            'status' => 'pending',
            'request_reason' => $reason,
        ]);
    }

    /**
     * Get pending requests with user info
     */
    public static function getPendingRequests(): array
    {
        $instance = new self();
        return $instance->db()->fetchAll(
            "SELECT cr.*, u.username, u.email, u.created_at as user_created_at
             FROM contributor_requests cr
             JOIN users u ON cr.user_id = u.id
             WHERE cr.status = 'pending'
             ORDER BY cr.created_at ASC"
        );
    }

    /**
     * Get all requests with pagination
     */
    public static function getAllRequests(int $page = 1, int $perPage = 20, ?string $status = null): array
    {
        $instance = new self();
        $offset = ($page - 1) * $perPage;

        $whereClause = '';
        $params = ['limit' => $perPage, 'offset' => $offset];

        if ($status) {
            $whereClause = 'WHERE cr.status = :status';
            $params['status'] = $status;
        }

        $rows = $instance->db()->fetchAll(
            "SELECT cr.*, u.username, u.email, u.created_at as user_created_at,
                    reviewer.username as reviewer_username
             FROM contributor_requests cr
             JOIN users u ON cr.user_id = u.id
             LEFT JOIN users reviewer ON cr.reviewed_by = reviewer.id
             {$whereClause}
             ORDER BY cr.created_at DESC
             LIMIT :limit OFFSET :offset",
            $params
        );

        $countSql = "SELECT COUNT(*) FROM contributor_requests cr {$whereClause}";
        $countParams = $status ? ['status' => $status] : [];
        $total = (int) $instance->db()->fetchColumn($countSql, $countParams);

        return [
            'data' => $rows,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Approve the request
     */
    public function approve(int $adminId, ?string $note = null): bool
    {
        $this->status = 'approved';
        $this->reviewed_by = $adminId;
        $this->reviewed_at = date('Y-m-d H:i:s');
        if ($note) {
            $this->admin_note = $note;
        }
        $this->save();

        // Update user role to contributor
        $this->db()->update(
            'users',
            ['role' => 'contributor'],
            'id = :user_id',
            ['user_id' => $this->user_id]
        );

        return true;
    }

    /**
     * Reject the request
     */
    public function reject(int $adminId, ?string $note = null): bool
    {
        $this->status = 'rejected';
        $this->reviewed_by = $adminId;
        $this->reviewed_at = date('Y-m-d H:i:s');
        if ($note) {
            $this->admin_note = $note;
        }
        $this->save();

        return true;
    }

    /**
     * Get request by user ID
     */
    public static function findByUserId(int $userId): ?self
    {
        return self::firstWhere('user_id', $userId);
    }

    /**
     * Check if user has pending request
     */
    public static function hasPending(int $userId): bool
    {
        $instance = new self();
        $count = (int) $instance->db()->fetchColumn(
            "SELECT COUNT(*) FROM contributor_requests WHERE user_id = :user_id AND status = 'pending'",
            ['user_id' => $userId]
        );
        return $count > 0;
    }

    /**
     * Get count by status
     */
    public static function countByStatus(string $status): int
    {
        return self::count('status = :status', ['status' => $status]);
    }
}
