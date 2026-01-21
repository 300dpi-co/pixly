<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Popup Ad Model
 */
class PopupAd extends Model
{
    protected string $table = 'popup_ads';

    protected array $fillable = [
        'name',
        'content',
        'trigger_type',
        'trigger_delay',
        'trigger_scroll_percent',
        'show_on_mobile',
        'overlay_opacity',
        'position',
        'animation',
        'width',
        'frequency',
        'cookie_days',
        'pages_include',
        'pages_exclude',
        'device_target',
        'start_date',
        'end_date',
        'is_active',
        'priority',
    ];

    /**
     * Get active popup for current page
     */
    public static function getActiveForPage(string $currentPath, bool $isMobile = false): ?array
    {
        $instance = new static();
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM popup_ads
                WHERE is_active = 1
                AND (start_date IS NULL OR start_date <= :now1)
                AND (end_date IS NULL OR end_date >= :now2)";

        if ($isMobile) {
            $sql .= " AND show_on_mobile = 1";
        }

        $sql .= " ORDER BY priority DESC LIMIT 1";

        $popup = $instance->db()->fetch($sql, ['now1' => $now, 'now2' => $now]);

        if (!$popup) return null;

        // Check page exclusions
        if (!empty($popup['pages_exclude'])) {
            $excludes = json_decode($popup['pages_exclude'], true) ?? [];
            foreach ($excludes as $pattern) {
                if (fnmatch($pattern, $currentPath)) {
                    return null;
                }
            }
        }

        // Check page includes
        if (!empty($popup['pages_include'])) {
            $includes = json_decode($popup['pages_include'], true) ?? [];
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

        return $popup;
    }

    /**
     * Increment impressions
     */
    public function incrementImpressions(): void
    {
        $this->db()->query(
            "UPDATE popup_ads SET impressions = impressions + 1 WHERE id = :id",
            ['id' => $this->id]
        );
    }

    /**
     * Increment closes
     */
    public function incrementCloses(): void
    {
        $this->db()->query(
            "UPDATE popup_ads SET closes = closes + 1 WHERE id = :id",
            ['id' => $this->id]
        );
    }

    /**
     * Increment clicks
     */
    public function incrementClicks(): void
    {
        $this->db()->query(
            "UPDATE popup_ads SET clicks = clicks + 1 WHERE id = :id",
            ['id' => $this->id]
        );
    }

    /**
     * Get all active popups
     */
    public static function getActive(): array
    {
        $instance = new static();
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM popup_ads
                WHERE is_active = 1
                AND (start_date IS NULL OR start_date <= :now1)
                AND (end_date IS NULL OR end_date >= :now2)
                ORDER BY priority DESC";

        return $instance->db()->fetchAll($sql, ['now1' => $now, 'now2' => $now]) ?: [];
    }

    /**
     * Get cookie name for this popup
     */
    public function getCookieName(): string
    {
        return 'popup_' . $this->id . '_seen';
    }
}
