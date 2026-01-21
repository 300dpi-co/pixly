<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Ad Model
 */
class Ad extends Model
{
    protected string $table = 'ads';

    protected array $fillable = [
        'name',
        'placement_id',
        'ad_type',
        'content',
        'image_path',
        'destination_url',
        'adsense_slot',
        'juicyads_zone',
        'device_target',
        'pages_target',
        'pages_exclude',
        'geo_target',
        'start_date',
        'end_date',
        'is_active',
        'priority',
    ];

    /**
     * Get all ads with placement info
     */
    public static function allWithPlacements(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT a.*, p.name as placement_name, p.location as placement_location, p.slug as placement_slug
             FROM ads a
             LEFT JOIN ad_placements p ON a.placement_id = p.id
             ORDER BY a.created_at DESC"
        );
    }

    /**
     * Get active ads for a placement (alias for forPlacement)
     */
    public static function getActiveForPlacement(int $placementId): array
    {
        return self::forPlacement($placementId);
    }

    /**
     * Get active ads for a placement
     */
    public static function forPlacement(int $placementId, string $device = 'all'): array
    {
        $instance = new static();
        $now = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM ads
                WHERE placement_id = :placement_id
                AND is_active = 1
                AND (start_date IS NULL OR start_date <= :now1)
                AND (end_date IS NULL OR end_date >= :now2)";

        if ($device !== 'all') {
            $sql .= " AND (device_target = 'all' OR device_target = :device)";
        }

        $sql .= " ORDER BY priority DESC, RAND()";

        $params = [
            'placement_id' => $placementId,
            'now1' => $now,
            'now2' => $now,
        ];

        if ($device !== 'all') {
            $params['device'] = $device;
        }

        return $instance->db()->fetchAll($sql, $params);
    }

    /**
     * Increment impressions
     */
    public function incrementImpressions(): void
    {
        $this->db()->query(
            "UPDATE ads SET impressions = impressions + 1 WHERE id = :id",
            ['id' => $this->id]
        );
    }

    /**
     * Increment clicks
     */
    public function incrementClicks(): void
    {
        $this->db()->query(
            "UPDATE ads SET clicks = clicks + 1 WHERE id = :id",
            ['id' => $this->id]
        );
    }

    /**
     * Get CTR
     */
    public function getCtr(): float
    {
        if ($this->impressions == 0) return 0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    /**
     * Render the ad HTML
     */
    public function render(): string
    {
        switch ($this->ad_type) {
            case 'custom_html':
                return $this->content ?? '';

            case 'image':
                $html = '<a href="' . e($this->destination_url) . '" target="_blank" rel="noopener sponsored">';
                $html .= '<img src="/uploads/ads/' . e($this->image_path) . '" alt="' . e($this->name) . '">';
                $html .= '</a>';
                return $html;

            case 'adsense':
                return $this->content ?? '';

            case 'juicyads':
                return $this->content ?? '';

            default:
                return '';
        }
    }
}
