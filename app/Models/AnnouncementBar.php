<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Announcement Bar Model
 */
class AnnouncementBar extends Model
{
    protected string $table = 'announcement_bars';

    protected array $fillable = [
        'message',
        'link_url',
        'link_text',
        'bg_color',
        'text_color',
        'is_dismissible',
        'cookie_days',
        'position',
        'is_sticky',
        'pages_include',
        'pages_exclude',
        'start_date',
        'end_date',
        'is_active',
        'priority',
    ];

    /**
     * Get active announcement for current page
     */
    public static function getActiveForPage(string $currentPath): ?array
    {
        $instance = new static();
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM announcement_bars
                WHERE is_active = 1
                AND (start_date IS NULL OR start_date <= :now1)
                AND (end_date IS NULL OR end_date >= :now2)
                ORDER BY priority DESC
                LIMIT 1";

        $bar = $instance->db()->fetch($sql, ['now1' => $now, 'now2' => $now]);

        if (!$bar) return null;

        // Check page exclusions
        if (!empty($bar['pages_exclude'])) {
            $excludes = json_decode($bar['pages_exclude'], true) ?? [];
            foreach ($excludes as $pattern) {
                if (fnmatch($pattern, $currentPath)) {
                    return null;
                }
            }
        }

        // Check page includes
        if (!empty($bar['pages_include'])) {
            $includes = json_decode($bar['pages_include'], true) ?? [];
            if (!empty($includes)) {
                $matched = false;
                foreach ($includes as $pattern) {
                    if (fnmatch($pattern, $currentPath)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) return null;
            }
        }

        return $bar;
    }

    /**
     * Get all active announcements
     */
    public static function getActive(): array
    {
        $instance = new static();
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM announcement_bars
                WHERE is_active = 1
                AND (start_date IS NULL OR start_date <= :now1)
                AND (end_date IS NULL OR end_date >= :now2)
                ORDER BY priority DESC";

        return $instance->db()->fetchAll($sql, ['now1' => $now, 'now2' => $now]) ?: [];
    }

    /**
     * Get cookie name for this announcement
     */
    public function getCookieName(): string
    {
        return 'announcement_' . $this->id . '_dismissed';
    }
}
