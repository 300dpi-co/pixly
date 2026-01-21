<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Newsletter Subscriber Model
 */
class NewsletterSubscriber extends Model
{
    protected string $table = 'newsletter_subscribers';

    protected array $fillable = [
        'email',
        'name',
        'status',
        'confirmation_token',
        'confirmed_at',
        'unsubscribed_at',
        'source',
        'ip_address',
        'user_agent',
    ];

    /**
     * Subscribe a new email
     */
    public static function subscribe(string $email, ?string $name = null, string $source = 'website'): array
    {
        $instance = new static();
        $db = $instance->db();

        // Check if already exists
        $existing = $db->fetch(
            "SELECT * FROM newsletter_subscribers WHERE email = :email",
            ['email' => $email]
        );

        if ($existing) {
            if ($existing['status'] === 'unsubscribed') {
                // Resubscribe
                $db->query(
                    "UPDATE newsletter_subscribers
                     SET status = 'pending', unsubscribed_at = NULL,
                         confirmation_token = :token, updated_at = NOW()
                     WHERE id = :id",
                    ['token' => bin2hex(random_bytes(32)), 'id' => $existing['id']]
                );
                return ['success' => true, 'message' => 'Please check your email to confirm your subscription.'];
            }
            return ['success' => false, 'message' => 'This email is already subscribed.'];
        }

        // Create new subscriber
        $token = bin2hex(random_bytes(32));
        $doubleOptin = MarketingSetting::get('newsletter_double_optin', '1') === '1';

        $db->insert('newsletter_subscribers', [
            'email' => $email,
            'name' => $name,
            'status' => $doubleOptin ? 'pending' : 'confirmed',
            'confirmation_token' => $doubleOptin ? $token : null,
            'confirmed_at' => $doubleOptin ? null : date('Y-m-d H:i:s'),
            'source' => $source,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);

        if ($doubleOptin) {
            return ['success' => true, 'message' => 'Please check your email to confirm your subscription.', 'token' => $token];
        }

        return ['success' => true, 'message' => 'You have been subscribed successfully!'];
    }

    /**
     * Confirm subscription
     */
    public static function confirm(string $token): bool
    {
        $instance = new static();
        $db = $instance->db();

        $subscriber = $db->fetch(
            "SELECT * FROM newsletter_subscribers WHERE confirmation_token = :token AND status = 'pending'",
            ['token' => $token]
        );

        if (!$subscriber) return false;

        $db->query(
            "UPDATE newsletter_subscribers
             SET status = 'confirmed', confirmed_at = NOW(), confirmation_token = NULL
             WHERE id = :id",
            ['id' => $subscriber['id']]
        );

        return true;
    }

    /**
     * Unsubscribe
     */
    public static function unsubscribe(string $email): bool
    {
        $instance = new static();
        $db = $instance->db();

        $result = $db->query(
            "UPDATE newsletter_subscribers
             SET status = 'unsubscribed', unsubscribed_at = NOW()
             WHERE email = :email",
            ['email' => $email]
        );

        return $result->rowCount() > 0;
    }

    /**
     * Get subscribers by status
     */
    public static function byStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM newsletter_subscribers WHERE status = :status ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            ['status' => $status]
        );
    }

    /**
     * Get subscriber counts
     */
    public static function getCounts(): array
    {
        $instance = new static();
        return $instance->db()->fetch(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed
             FROM newsletter_subscribers"
        );
    }

    /**
     * Export confirmed subscribers as CSV
     */
    public static function exportCsv(): string
    {
        $instance = new static();
        $subscribers = $instance->db()->fetchAll(
            "SELECT email, name, confirmed_at, source FROM newsletter_subscribers WHERE status = 'confirmed' ORDER BY confirmed_at DESC"
        );

        $csv = "Email,Name,Confirmed At,Source\n";
        foreach ($subscribers as $sub) {
            $csv .= '"' . $sub['email'] . '","' . ($sub['name'] ?? '') . '","' . $sub['confirmed_at'] . '","' . $sub['source'] . "\"\n";
        }

        return $csv;
    }
}
