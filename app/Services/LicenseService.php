<?php

declare(strict_types=1);

namespace App\Services;

/**
 * License Service
 *
 * Handles license key management and feature access.
 * Phase 1: Always returns free tier (no server yet)
 * Phase 2: Will validate against license server
 */
class LicenseService
{
    private const LICENSE_KEY_SETTING = 'license_key';
    private const CACHE_KEY = 'license_status';
    private const CACHE_TTL = 3600; // 1 hour

    // License tiers
    public const TIER_FREE = 'free';
    public const TIER_PRO = 'pro';
    public const TIER_BUSINESS = 'business';
    public const TIER_ENTERPRISE = 'enterprise';

    private ?string $licenseKey = null;
    private ?array $cachedStatus = null;

    /**
     * Get current license tier
     * Phase 1: Always returns free
     */
    public function getTier(): string
    {
        // Phase 1: Always free
        // Phase 2: Validate license key against server
        return self::TIER_FREE;
    }

    /**
     * Check if a feature is available for current license
     */
    public function hasFeature(string $feature): bool
    {
        $tier = $this->getTier();
        $features = $this->getFeaturesForTier($tier);

        return in_array($feature, $features, true);
    }

    /**
     * Get license key from database
     */
    public function getLicenseKey(): ?string
    {
        if ($this->licenseKey !== null) {
            return $this->licenseKey;
        }

        try {
            $db = db();
            $result = $db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = :key",
                ['key' => self::LICENSE_KEY_SETTING]
            );

            $this->licenseKey = $result['setting_value'] ?? null;
            return $this->licenseKey;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Set license key in database
     */
    public function setLicenseKey(string $key): bool
    {
        try {
            $db = db();

            // Check if setting exists
            $exists = $db->fetch(
                "SELECT id FROM settings WHERE setting_key = :key",
                ['key' => self::LICENSE_KEY_SETTING]
            );

            if ($exists) {
                $db->update(
                    'settings',
                    ['setting_value' => $key, 'updated_at' => date('Y-m-d H:i:s')],
                    'setting_key = :key',
                    ['key' => self::LICENSE_KEY_SETTING]
                );
            } else {
                $db->insert('settings', [
                    'setting_key' => self::LICENSE_KEY_SETTING,
                    'setting_value' => $key,
                    'setting_type' => 'string',
                    'description' => 'License key for premium features',
                    'is_public' => false,
                ]);
            }

            $this->licenseKey = $key;
            $this->clearCache();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Validate license key format
     */
    public function isValidKeyFormat(string $key): bool
    {
        // Expected format: XXXX-XXXX-XXXX-XXXX (16 chars + 3 dashes)
        return preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key) === 1;
    }

    /**
     * Check if license is active (validated)
     * Phase 1: Always returns true for free tier
     */
    public function isActive(): bool
    {
        // Phase 1: Always active (free tier)
        return true;
    }

    /**
     * Get license status info
     */
    public function getStatus(): array
    {
        return [
            'tier' => $this->getTier(),
            'is_active' => $this->isActive(),
            'license_key' => $this->getLicenseKey() ? $this->maskLicenseKey($this->getLicenseKey()) : null,
            'features' => $this->getFeaturesForTier($this->getTier()),
        ];
    }

    /**
     * Get features available for a tier
     */
    private function getFeaturesForTier(string $tier): array
    {
        $features = [
            self::TIER_FREE => [
                'basic_upload',
                'basic_ai_analysis',
                'categories',
                'tags',
                'user_management',
                'basic_seo',
            ],
            self::TIER_PRO => [
                'basic_upload',
                'basic_ai_analysis',
                'categories',
                'tags',
                'user_management',
                'basic_seo',
                'bulk_upload',
                'advanced_ai_analysis',
                'priority_processing',
                'custom_watermark',
            ],
            self::TIER_BUSINESS => [
                'basic_upload',
                'basic_ai_analysis',
                'categories',
                'tags',
                'user_management',
                'basic_seo',
                'bulk_upload',
                'advanced_ai_analysis',
                'priority_processing',
                'custom_watermark',
                'api_access',
                'advanced_analytics',
                'white_label',
                'priority_support',
            ],
            self::TIER_ENTERPRISE => [
                '*', // All features
            ],
        ];

        return $features[$tier] ?? $features[self::TIER_FREE];
    }

    /**
     * Mask license key for display
     */
    private function maskLicenseKey(string $key): string
    {
        if (strlen($key) < 8) {
            return '****';
        }

        return substr($key, 0, 4) . '-****-****-' . substr($key, -4);
    }

    /**
     * Clear license cache
     */
    private function clearCache(): void
    {
        $cacheFile = $this->getCacheFile();
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
        $this->cachedStatus = null;
    }

    /**
     * Get cache file path
     */
    private function getCacheFile(): string
    {
        return (defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 2) . '/storage')
            . '/cache/' . self::CACHE_KEY . '.json';
    }
}
