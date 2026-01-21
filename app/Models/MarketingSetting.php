<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Marketing Setting Model
 */
class MarketingSetting extends Model
{
    protected string $table = 'marketing_settings';

    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
    ];

    private static array $cache = [];

    /**
     * Get a setting value
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $instance = new static();
        $result = $instance->db()->fetch(
            "SELECT setting_value FROM marketing_settings WHERE setting_key = :key",
            ['key' => $key]
        );

        $value = $result ? $result['setting_value'] : $default;
        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        $instance = new static();
        $db = $instance->db();

        $existing = $db->fetch(
            "SELECT id FROM marketing_settings WHERE setting_key = :key",
            ['key' => $key]
        );

        if ($existing) {
            $db->query(
                "UPDATE marketing_settings SET setting_value = :value, setting_group = :group WHERE setting_key = :key",
                ['value' => $value, 'group' => $group, 'key' => $key]
            );
        } else {
            $db->insert('marketing_settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => $group,
            ]);
        }

        self::$cache[$key] = $value;
    }

    /**
     * Get all settings by group
     */
    public static function byGroup(string $group): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll(
            "SELECT setting_key, setting_value FROM marketing_settings WHERE setting_group = :group",
            ['group' => $group]
        );

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll("SELECT setting_key, setting_value FROM marketing_settings");

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get all settings grouped
     */
    public static function getAllGrouped(): array
    {
        $instance = new static();
        $rows = $instance->db()->fetchAll("SELECT * FROM marketing_settings ORDER BY setting_group, setting_key");

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
        }
        return $grouped;
    }

    /**
     * Update multiple settings
     */
    public static function updateMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value, $group);
        }
    }

    /**
     * Clear cache
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
