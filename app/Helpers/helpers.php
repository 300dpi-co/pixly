<?php

declare(strict_types=1);

/**
 * Global Helper Functions
 */

if (!function_exists('app')) {
    /**
     * Get the application instance
     */
    function app(): App\Core\Application
    {
        return App\Core\Application::getInstance();
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value using dot notation
     */
    function config(string $key, mixed $default = null): mixed
    {
        $config = app()->getConfig();
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (!is_array($config) || !array_key_exists($k, $config)) {
                return $default;
            }
            $config = $config[$k];
        }

        return $config;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('setting')) {
    /**
     * Get a setting value from database
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @param bool $forceRefresh Force reload from database (bypass cache)
     * @return mixed
     */
    function setting(string $key, mixed $default = null, bool $forceRefresh = false): mixed
    {
        static $settings = null;

        // Force refresh clears static cache
        if ($forceRefresh) {
            $settings = null;
        }

        // Load all settings once and cache in memory
        if ($settings === null) {
            // Try cache first (unless force refresh)
            $cached = $forceRefresh ? null : cache()->get('settings');
            if ($cached !== null) {
                $settings = $cached;
            } else {
                $settings = [];
                try {
                    $db = app()->getDatabase();
                    $rows = $db->fetchAll("SELECT setting_key, setting_value, setting_type FROM settings");
                    foreach ($rows as $row) {
                        $value = $row['setting_value'];
                        // Cast based on type
                        switch ($row['setting_type']) {
                            case 'bool':
                                // Convert string "1" to true, anything else to false
                                $value = ($value === '1' || $value === 1);
                                break;
                            case 'int':
                                $value = (int) $value;
                                break;
                            case 'json':
                                $value = json_decode($value, true);
                                break;
                        }
                        $settings[$row['setting_key']] = $value;
                    }
                    cache()->set('settings', $settings, 3600); // Cache for 1 hour
                } catch (\Exception $e) {
                    $settings = [];
                }
            }
        }

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('clear_settings_cache')) {
    /**
     * Clear the settings cache (both file and memory)
     */
    function clear_settings_cache(): void
    {
        cache()->delete('settings');
        cache()->clearPattern('settings');
        // Force next setting() call to reload from database
        setting('_force_refresh', null, true);
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL for the application
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        $path = ltrim($path, '/');
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate URL for an asset
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     */
    function redirect(string $url, int $statusCode = 302): never
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous page
     */
    function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get or generate CSRF token
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF hidden input field
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        $name = config('security.csrf_token_name', '_token');
        return '<input type="hidden" name="' . e($name) . '" value="' . e($token) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value from session
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('session_flash')) {
    /**
     * Set a flash message
     */
    function session_flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('session_get_flash')) {
    /**
     * Get and remove a flash message
     */
    function session_get_flash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(mixed ...$vars): never
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit(1);
    }
}

if (!function_exists('slug')) {
    /**
     * Generate URL-friendly slug from string
     */
    function slug(string $text, string $separator = '-'): string
    {
        // Convert to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // Replace non-alphanumeric characters with separator
        $text = preg_replace('/[^a-z0-9]+/u', $separator, $text);

        // Remove duplicate separators
        $text = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $text);

        // Trim separators from ends
        return trim($text, $separator);
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit string length with ellipsis
     */
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit, 'UTF-8') . $end;
    }
}

if (!function_exists('human_filesize')) {
    /**
     * Convert bytes to human-readable format
     */
    function human_filesize(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}

if (!function_exists('time_ago')) {
    /**
     * Convert timestamp to human-readable time ago
     */
    function time_ago(string|int $timestamp): string
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($diff / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}

if (!function_exists('render_ad')) {
    /**
     * Render ad for a placement
     */
    function render_ad(string $placement): string
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->render($placement);
    }
}

if (!function_exists('show_ad_between')) {
    /**
     * Check if ad should be shown between images at index
     */
    function show_ad_between(int $index): bool
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->shouldShowBetweenImages($index);
    }
}

if (!function_exists('cache')) {
    /**
     * Get cache service instance or cached value
     */
    function cache(?string $key = null, mixed $default = null): mixed
    {
        static $cacheService = null;
        if ($cacheService === null) {
            $cacheService = new \App\Services\CacheService();
        }

        if ($key === null) {
            return $cacheService;
        }

        return $cacheService->get($key, $default);
    }
}

if (!function_exists('cache_remember')) {
    /**
     * Get cached value or compute and store it
     */
    function cache_remember(string $key, int $ttl, callable $callback): mixed
    {
        return cache()->remember($key, $ttl, $callback);
    }
}

if (!function_exists('render_announcement')) {
    /**
     * Render the active announcement bar
     */
    function render_announcement(): string
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->renderAnnouncement();
    }
}

if (!function_exists('render_popups')) {
    /**
     * Render popup scripts for the page
     */
    function render_popups(): string
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->renderPopupsScript();
    }
}

if (!function_exists('head_tracking_codes')) {
    /**
     * Get tracking codes for head section
     */
    function head_tracking_codes(): string
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->getHeadTrackingCodes();
    }
}

if (!function_exists('body_tracking_codes')) {
    /**
     * Get tracking codes for body end
     */
    function body_tracking_codes(): string
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->getBodyTrackingCodes();
    }
}

if (!function_exists('ads_enabled')) {
    /**
     * Check if ads are enabled
     */
    function ads_enabled(): bool
    {
        static $adService = null;
        if ($adService === null) {
            $adService = new \App\Services\AdService();
        }
        return $adService->isEnabled();
    }
}

if (!function_exists('marketing_setting')) {
    /**
     * Get a marketing setting value
     */
    function marketing_setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\MarketingSetting::get($key, $default);
    }
}

if (!function_exists('social_links')) {
    /**
     * Get social media links
     */
    function social_links(): array
    {
        $settings = \App\Models\MarketingSetting::getAll();
        $links = [];

        $platforms = ['twitter', 'facebook', 'instagram', 'tiktok', 'youtube', 'pinterest', 'discord', 'telegram'];
        foreach ($platforms as $platform) {
            $key = 'social_' . $platform;
            if (!empty($settings[$key])) {
                $links[$platform] = $settings[$key];
            }
        }

        return $links;
    }
}

// ============================================
// IMAGE HELPERS (WebP Support)
// ============================================

if (!function_exists('browser_supports_webp')) {
    /**
     * Check if browser supports WebP based on Accept header
     */
    function browser_supports_webp(): bool
    {
        static $supports = null;

        if ($supports === null) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $supports = str_contains($accept, 'image/webp');
        }

        return $supports;
    }
}

if (!function_exists('image_url')) {
    /**
     * Get the best image URL (WebP if supported, fallback otherwise)
     *
     * @param string $path Original image path (relative to /uploads/)
     * @param string|null $webpPath WebP version path (if available)
     * @param bool $absolute Return absolute URL
     * @return string
     */
    function image_url(string $path, ?string $webpPath = null, bool $absolute = false): string
    {
        // If WebP available and browser supports it, use WebP
        if ($webpPath && browser_supports_webp()) {
            $url = '/uploads/' . $webpPath;
        } else {
            $url = '/uploads/' . $path;
        }

        if ($absolute) {
            return rtrim(config('app.url', ''), '/') . $url;
        }

        return $url;
    }
}

if (!function_exists('picture_tag')) {
    /**
     * Generate a <picture> element with WebP source and fallback
     *
     * @param array $image Image array with paths (storage_path, webp_path, thumbnail_path, thumbnail_webp_path)
     * @param string $size Size to use: 'original', 'thumbnail', 'medium'
     * @param array $attributes HTML attributes for the img tag
     * @param bool $lazy Use lazy loading
     * @return string HTML picture element
     */
    function picture_tag(array $image, string $size = 'thumbnail', array $attributes = [], bool $lazy = true): string
    {
        // Determine paths based on size
        switch ($size) {
            case 'original':
            case 'full':
                $fallbackPath = $image['storage_path'] ?? '';
                $webpPath = $image['webp_path'] ?? null;
                break;
            case 'medium':
                $fallbackPath = $image['medium_path'] ?? $image['storage_path'] ?? '';
                $webpPath = $image['medium_webp_path'] ?? $image['webp_path'] ?? null;
                break;
            case 'thumbnail':
            default:
                $fallbackPath = $image['thumbnail_path'] ?? $image['storage_path'] ?? '';
                $webpPath = $image['thumbnail_webp_path'] ?? null;
                break;
        }

        if (empty($fallbackPath)) {
            return '';
        }

        $fallbackUrl = '/uploads/' . e($fallbackPath);
        $webpUrl = $webpPath ? '/uploads/' . e($webpPath) : null;

        // Build attributes
        $alt = e($attributes['alt'] ?? $image['alt_text'] ?? $image['title'] ?? '');
        $class = $attributes['class'] ?? '';
        $width = $attributes['width'] ?? $image['width'] ?? '';
        $height = $attributes['height'] ?? $image['height'] ?? '';

        // For thumbnails, use thumbnail dimensions
        if ($size === 'thumbnail') {
            $width = $attributes['width'] ?? 300;
            $height = $attributes['height'] ?? 300;
        }

        // Dominant color for placeholder
        $bgColor = $image['dominant_color'] ?? '#f3f4f6';

        // Build img attributes
        $imgAttrs = [];
        if ($lazy) {
            $imgAttrs[] = 'loading="lazy"';
            $imgAttrs[] = 'decoding="async"';
        }
        if ($alt) $imgAttrs[] = 'alt="' . $alt . '"';
        if ($class) $imgAttrs[] = 'class="' . e($class) . '"';
        if ($width) $imgAttrs[] = 'width="' . (int)$width . '"';
        if ($height) $imgAttrs[] = 'height="' . (int)$height . '"';
        $imgAttrs[] = 'style="background-color: ' . e($bgColor) . '"';

        $imgAttrStr = implode(' ', $imgAttrs);

        // If WebP available, use picture element
        if ($webpUrl) {
            return sprintf(
                '<picture><source srcset="%s" type="image/webp"><img src="%s" %s></picture>',
                $webpUrl,
                $fallbackUrl,
                $imgAttrStr
            );
        }

        // Fallback to simple img
        return sprintf('<img src="%s" %s>', $fallbackUrl, $imgAttrStr);
    }
}

if (!function_exists('lazy_picture')) {
    /**
     * Generate lazy-loaded picture element using data-src pattern
     * Works with existing lazy loading JavaScript
     *
     * @param array $image Image array with paths
     * @param string $size Size to use
     * @param array $attributes HTML attributes
     * @return string HTML
     */
    function lazy_picture(array $image, string $size = 'thumbnail', array $attributes = []): string
    {
        switch ($size) {
            case 'original':
            case 'full':
                $fallbackPath = $image['storage_path'] ?? '';
                $webpPath = $image['webp_path'] ?? null;
                break;
            case 'medium':
                $fallbackPath = $image['medium_path'] ?? $image['storage_path'] ?? '';
                $webpPath = $image['medium_webp_path'] ?? null;
                break;
            case 'thumbnail':
            default:
                $fallbackPath = $image['thumbnail_path'] ?? $image['storage_path'] ?? '';
                $webpPath = $image['thumbnail_webp_path'] ?? null;
                break;
        }

        if (empty($fallbackPath)) {
            return '';
        }

        $fallbackUrl = '/uploads/' . e($fallbackPath);
        $webpUrl = $webpPath ? '/uploads/' . e($webpPath) : null;

        $alt = e($attributes['alt'] ?? $image['alt_text'] ?? $image['title'] ?? '');
        $class = trim('lazy ' . ($attributes['class'] ?? ''));
        $bgColor = $image['dominant_color'] ?? '#f3f4f6';

        // If WebP is available and browser supports it, use WebP in data-src
        $dataSrc = ($webpUrl && browser_supports_webp()) ? $webpUrl : $fallbackUrl;

        return sprintf(
            '<img data-src="%s" alt="%s" class="%s" style="background-color: %s" loading="lazy">',
            $dataSrc,
            $alt,
            e($class),
            e($bgColor)
        );
    }
}

if (!function_exists('srcset_webp')) {
    /**
     * Generate srcset with WebP support for responsive images
     *
     * @param array $image Image array with all size paths
     * @return array ['webp' => srcset string, 'fallback' => srcset string]
     */
    function srcset_webp(array $image): array
    {
        $webpSet = [];
        $fallbackSet = [];

        // Thumbnail (300w)
        if (!empty($image['thumbnail_webp_path'])) {
            $webpSet[] = '/uploads/' . $image['thumbnail_webp_path'] . ' 300w';
        }
        if (!empty($image['thumbnail_path'])) {
            $fallbackSet[] = '/uploads/' . $image['thumbnail_path'] . ' 300w';
        }

        // Medium (800w)
        if (!empty($image['medium_webp_path'])) {
            $webpSet[] = '/uploads/' . $image['medium_webp_path'] . ' 800w';
        } elseif (!empty($image['webp_path'])) {
            $webpSet[] = '/uploads/' . $image['webp_path'] . ' 800w';
        }
        if (!empty($image['medium_path'])) {
            $fallbackSet[] = '/uploads/' . $image['medium_path'] . ' 800w';
        }

        // Original/Large
        if (!empty($image['webp_path'])) {
            $width = $image['width'] ?? 1920;
            $webpSet[] = '/uploads/' . $image['webp_path'] . ' ' . $width . 'w';
        }
        if (!empty($image['storage_path'])) {
            $width = $image['width'] ?? 1920;
            $fallbackSet[] = '/uploads/' . $image['storage_path'] . ' ' . $width . 'w';
        }

        return [
            'webp' => implode(', ', $webpSet),
            'fallback' => implode(', ', $fallbackSet),
        ];
    }
}
