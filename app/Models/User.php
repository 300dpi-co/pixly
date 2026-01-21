<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'status',
        'email_verified_at',
        'verification_token',
        'avatar_path',
        'bio',
    ];

    protected array $hidden = [
        'password_hash',
        'verification_token',
    ];

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return self::firstWhere('email', $email);
    }

    /**
     * Find user by username
     */
    public static function findByUsername(string $username): ?self
    {
        return self::firstWhere('username', $username);
    }

    /**
     * Find user by verification token
     */
    public static function findByVerificationToken(string $token): ?self
    {
        return self::firstWhere('verification_token', $token);
    }

    /**
     * Create a new user
     */
    public static function register(array $data): self
    {
        $user = new self([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_ARGON2ID),
            'role' => 'user',
            'status' => 'pending',
            'verification_token' => bin2hex(random_bytes(32)),
        ]);

        $user->save();
        return $user;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Check if email is verified
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is moderator or higher
     */
    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin', 'superadmin']);
    }

    /**
     * Check if user is a contributor
     */
    public function isContributor(): bool
    {
        return $this->role === 'contributor';
    }

    /**
     * Check if user can upload images
     * Contributors, moderators, admins, and superadmins can upload
     */
    public function canUpload(): bool
    {
        return in_array($this->role, ['contributor', 'moderator', 'admin', 'superadmin']);
    }

    /**
     * Check if user has pending contributor request
     */
    public function hasPendingContributorRequest(): bool
    {
        $request = $this->db()->fetch(
            "SELECT id FROM contributor_requests WHERE user_id = :user_id AND status = 'pending'",
            ['user_id' => $this->id]
        );
        return $request !== null;
    }

    /**
     * Get user's latest contributor request
     */
    public function getContributorRequest(): ?array
    {
        return $this->db()->fetch(
            "SELECT * FROM contributor_requests WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1",
            ['user_id' => $this->id]
        );
    }

    /**
     * Verify email
     */
    public function verifyEmail(): void
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->verification_token = null;
        $this->status = 'active';
        $this->save();
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->last_login_at = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * Generate password reset token
     */
    public function createPasswordResetToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->db()->insert('password_resets', [
            'email' => $this->email,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);

        return $token;
    }

    /**
     * Find user by password reset token
     */
    public static function findByResetToken(string $token): ?self
    {
        $instance = new self();
        $hashedToken = hash('sha256', $token);

        $reset = $instance->db()->fetch(
            "SELECT email FROM password_resets
             WHERE token = :token AND expires_at > NOW() AND used_at IS NULL
             ORDER BY created_at DESC LIMIT 1",
            ['token' => $hashedToken]
        );

        if (!$reset) {
            return null;
        }

        return self::findByEmail($reset['email']);
    }

    /**
     * Reset password
     */
    public function resetPassword(string $password, string $token): void
    {
        $this->password_hash = password_hash($password, PASSWORD_ARGON2ID);
        $this->save();

        // Mark token as used
        $hashedToken = hash('sha256', $token);
        $this->db()->update(
            'password_resets',
            ['used_at' => date('Y-m-d H:i:s')],
            'token = :token',
            ['token' => $hashedToken]
        );
    }

    /**
     * Log authentication event
     */
    public function logAuthEvent(string $eventType, ?string $ip = null, ?string $userAgent = null, array $details = []): void
    {
        $this->db()->insert('auth_logs', [
            'user_id' => $this->id,
            'event_type' => $eventType,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'details' => !empty($details) ? json_encode($details) : null,
        ]);
    }

    /**
     * Get user's favorites count
     */
    public function getFavoritesCount(): int
    {
        return (int) $this->db()->fetchColumn(
            "SELECT COUNT(*) FROM favorites WHERE user_id = :id",
            ['id' => $this->id]
        );
    }
}
