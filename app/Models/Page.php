<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Page Model
 *
 * Handles editable content pages (About, Terms, Privacy, etc.)
 */
class Page extends Model
{
    protected string $table = 'pages';

    protected array $fillable = [
        'slug',
        'title',
        'content',
        'meta_description',
        'is_active',
        'is_system',
        'show_in_footer',
        'sort_order',
    ];

    /**
     * Get all active pages
     */
    public static function active(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM pages WHERE is_active = 1 ORDER BY sort_order, title"
        );
    }

    /**
     * Get pages for footer
     */
    public static function forFooter(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT slug, title FROM pages WHERE is_active = 1 AND show_in_footer = 1 ORDER BY sort_order, title"
        );
    }

    /**
     * Find page by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        $instance = new static();
        $page = $instance->db()->fetch(
            "SELECT * FROM pages WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
        return $page ?: null;
    }

    /**
     * Find active page by slug
     */
    public static function findActiveBySlug(string $slug): ?array
    {
        $instance = new static();
        $page = $instance->db()->fetch(
            "SELECT * FROM pages WHERE slug = :slug AND is_active = 1 LIMIT 1",
            ['slug' => $slug]
        );
        return $page ?: null;
    }

    /**
     * Get all pages for admin
     */
    public static function allForAdmin(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM pages ORDER BY sort_order, title"
        );
    }

    /**
     * Parse content placeholders
     */
    public static function parseContent(string $content): string
    {
        $siteName = setting('site_name', config('app.name'));
        $siteUrl = config('app.url');
        $currentDate = date('F j, Y');

        $replacements = [
            '{{site_name}}' => htmlspecialchars($siteName),
            '{{site_url}}' => htmlspecialchars($siteUrl),
            '{{current_date}}' => $currentDate,
            '{{current_year}}' => date('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Check if slug exists (excluding given id)
     */
    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $instance = new static();
        $sql = "SELECT COUNT(*) as count FROM pages WHERE slug = :slug";
        $params = ['slug' => $slug];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $result = $instance->db()->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }
}
