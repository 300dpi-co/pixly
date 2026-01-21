<?php

declare(strict_types=1);

/**
 * Feature Flags Configuration
 *
 * Controls which features are enabled/disabled.
 * Some features may also be gated by license tier.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Core Features
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'enabled' => true,
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'bulk_upload' => false, // Premium feature
        'bulk_upload_limit' => 10,
    ],

    'ai' => [
        'enabled' => true,
        'auto_analyze' => true,
        'provider' => 'deepseek', // deepseek, openai, etc.
        'advanced_analysis' => false, // Premium feature
    ],

    /*
    |--------------------------------------------------------------------------
    | User Features
    |--------------------------------------------------------------------------
    */
    'registration' => [
        'enabled' => true,
        'require_email_verification' => true,
        'require_admin_approval' => false,
    ],

    'contributors' => [
        'enabled' => true,
        'require_approval' => true,
    ],

    'profiles' => [
        'enabled' => true,
        'public_by_default' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Features
    |--------------------------------------------------------------------------
    */
    'comments' => [
        'enabled' => true,
        'require_approval' => true,
        'allow_guests' => false,
    ],

    'favorites' => [
        'enabled' => true,
    ],

    'appreciations' => [
        'enabled' => true,
    ],

    'downloads' => [
        'enabled' => true,
        'track_downloads' => true,
        'require_login' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Features
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'sitemap_enabled' => true,
        'structured_data' => true,
        'meta_tags' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Premium Features (License Required)
    |--------------------------------------------------------------------------
    | These features are available based on license tier.
    */
    'premium' => [
        // API Access - Business tier and above
        'api_access' => [
            'enabled' => false,
            'tier_required' => 'business',
        ],

        // White Label - Business tier and above
        'white_label' => [
            'enabled' => false,
            'tier_required' => 'business',
        ],

        // Advanced Analytics - Business tier and above
        'advanced_analytics' => [
            'enabled' => false,
            'tier_required' => 'business',
        ],

        // Priority Support - Pro tier and above
        'priority_support' => [
            'enabled' => false,
            'tier_required' => 'pro',
        ],

        // Bulk Upload - Pro tier and above
        'bulk_upload' => [
            'enabled' => false,
            'tier_required' => 'pro',
        ],

        // Advanced AI - Pro tier and above
        'advanced_ai' => [
            'enabled' => false,
            'tier_required' => 'pro',
        ],

        // Custom Watermark - Pro tier and above
        'custom_watermark' => [
            'enabled' => false,
            'tier_required' => 'pro',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | External Integrations
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'unsplash' => [
            'enabled' => true,
        ],
        'pexels' => [
            'enabled' => true,
        ],
        'stripe' => [
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketing Features
    |--------------------------------------------------------------------------
    */
    'marketing' => [
        'ads_enabled' => true,
        'popups_enabled' => true,
        'announcements_enabled' => true,
        'newsletter_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Adult Content Features
    |--------------------------------------------------------------------------
    */
    'adult' => [
        'enabled' => false,
        'age_gate' => false,
        'nsfw_blur' => false,
        'quick_exit' => false,
    ],
];
