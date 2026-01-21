<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Ad Placement Model
 *
 * Works with the marketing_tables migration structure
 */
class AdPlacement extends Model
{
    protected string $table = 'ad_placements';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'location',
        'default_size',
        'is_active',
        'sort_order',
    ];

    /**
     * Get all active placements
     */
    public static function active(): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM ad_placements WHERE is_active = 1 ORDER BY sort_order, name"
        );
    }

    /**
     * Find placement by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        $instance = new static();
        return $instance->db()->fetch(
            "SELECT * FROM ad_placements WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        ) ?: null;
    }

    /**
     * Get placements by location
     */
    public static function byLocation(string $location): array
    {
        $instance = new static();
        return $instance->db()->fetchAll(
            "SELECT * FROM ad_placements WHERE location = :location AND is_active = 1 ORDER BY sort_order",
            ['location' => $location]
        );
    }

    /**
     * Get a single active placement for rendering
     */
    public static function getActive(string $slug, string $device = 'all'): ?array
    {
        $instance = new static();

        $sql = "SELECT * FROM ad_placements
                WHERE slug = :slug
                AND is_active = 1
                LIMIT 1";

        return $instance->db()->fetch($sql, ['slug' => $slug]) ?: null;
    }

    /**
     * Get ads for a placement
     */
    public static function getAdsForPlacement(int $placementId, string $device = 'all'): array
    {
        $instance = new static();

        $sql = "SELECT * FROM ads
                WHERE placement_id = :placement_id
                AND is_active = 1
                AND (start_date IS NULL OR start_date <= NOW())
                AND (end_date IS NULL OR end_date >= NOW())";

        if ($device !== 'all') {
            $sql .= " AND (device_target = 'all' OR device_target = :device)";
            $params = ['placement_id' => $placementId, 'device' => $device];
        } else {
            $params = ['placement_id' => $placementId];
        }

        $sql .= " ORDER BY priority DESC";

        return $instance->db()->fetchAll($sql, $params);
    }

    /**
     * Get stats summary
     */
    public static function getStats(): array
    {
        $instance = new static();
        return $instance->db()->fetch(
            "SELECT
                COUNT(*) as total_placements,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_placements,
                0 as total_impressions,
                0 as total_clicks,
                0 as total_revenue
             FROM ad_placements"
        ) ?: [
            'total_placements' => 0,
            'active_placements' => 0,
            'total_impressions' => 0,
            'total_clicks' => 0,
            'total_revenue' => 0,
        ];
    }
}
