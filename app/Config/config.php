<?php

declare(strict_types=1);

/**
 * Main Application Configuration
 *
 * All configuration values for the application.
 * For sensitive values, consider using environment variables.
 */

// Load database configuration from separate file if it exists (created by installer)
$databaseConfig = [];
$databaseConfigFile = __DIR__ . '/database.php';
if (file_exists($databaseConfigFile)) {
    $databaseConfig = require $databaseConfigFile;
} else {
    // Default fallback (used during development or before installation)
    $databaseConfig = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'fwp_gallery',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ];
}

return [
    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    */
    'app' => [
        'name' => 'Pixly',
        'url' => (function() {
            // Auto-detect URL from request, including subdirectory path
            // Check multiple sources for HTTPS (handles proxies/load balancers)
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
                || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $protocol = $isHttps ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

            // Detect subdirectory path from SCRIPT_NAME
            $basePath = '';
            if (!empty($_SERVER['SCRIPT_NAME'])) {
                $basePath = dirname($_SERVER['SCRIPT_NAME']);
                // Normalize path separators and remove trailing slashes
                $basePath = str_replace('\\', '/', $basePath);
                if ($basePath === '/' || $basePath === '.') {
                    $basePath = '';
                }
            }

            return "{$protocol}://{$host}{$basePath}";
        })(),
        'env' => 'production', // development, staging, production
        'debug' => false,
        'timezone' => 'UTC',
        'locale' => 'en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */
    'database' => $databaseConfig,

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    */
    'session' => [
        'lifetime' => 7200, // 2 hours
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'csrf_token_name' => '_token',
        'password_min_length' => 12,
        'login_attempts_limit' => 5,
        'login_lockout_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    */
    'images' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'max_dimension' => 8000,
        'quality' => 85,
        'sizes' => [
            'thumbnail' => ['width' => 300, 'height' => 300, 'crop' => true],
            'medium' => ['width' => 800, 'height' => null, 'crop' => false],
            'large' => ['width' => 1200, 'height' => null, 'crop' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => PUBLIC_PATH . '/cache',
        'default_ttl' => 3600, // 1 hour
        'page_ttl' => 300, // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 24,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'site_title' => 'Pixly',
        'site_description' => 'Discover trending images, creative visuals, and inspiring content.',
        'title_separator' => ' | ',
        'default_image' => '/assets/images/og-default.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | External API Keys
    |--------------------------------------------------------------------------
    | Store sensitive keys in environment variables for production
    */
    'api' => [
        'deepseek' => [
            'key' => '', // Add your DeepSeek API key
            'endpoint' => 'https://api.deepseek.com',
            'model' => 'deepseek-chat',
            'daily_limit' => 100,
            // For vision, use DeepInfra with Janus-Pro (get free API key at https://deepinfra.com)
            'vision_endpoint' => 'https://api.deepinfra.com/v1/openai',
            'vision_model' => 'deepseek-ai/Janus-Pro-7B',
            'vision_key' => '', // Add your DeepInfra API key here for image analysis
        ],
        'unsplash' => [
            'access_key' => '', // Add your Unsplash access key
            'secret_key' => '',
        ],
        'pexels' => [
            'api_key' => '', // Add your Pexels API key
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ads Configuration
    |--------------------------------------------------------------------------
    */
    'ads' => [
        'enabled' => true,
        'network' => 'juicyads',
        'placements' => [
            'header' => true,
            'sidebar' => true,
            'between_images' => true,
            'footer' => true,
        ],
        'images_between_ads' => 8,
    ],
];
