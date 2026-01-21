# Configuration Guide

Comprehensive guide to configuring Pixly.

## Configuration Files

### app/Config/config.php

Main application configuration file.

```php
return [
    'app' => [
        'name' => 'Pixly',
        'url' => 'https://yourdomain.com',
        'env' => 'production',      // development, staging, production
        'debug' => false,           // Show detailed errors
        'timezone' => 'UTC',
        'locale' => 'en',
    ],

    'session' => [
        'lifetime' => 7200,         // 2 hours in seconds
        'secure' => true,           // Requires HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'security' => [
        'csrf_token_name' => '_token',
        'password_min_length' => 12,
        'login_attempts_limit' => 5,
        'login_lockout_minutes' => 15,
    ],

    'images' => [
        'max_size' => 10485760,     // 10MB in bytes
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'max_dimension' => 8000,
        'quality' => 85,
    ],

    'cache' => [
        'enabled' => true,
        'driver' => 'file',
        'default_ttl' => 3600,      // 1 hour
    ],

    'pagination' => [
        'per_page' => 24,
        'max_per_page' => 100,
    ],
];
```

### app/Config/database.php

Database connection settings.

```php
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'fwp_gallery',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

## Admin Settings

Access via Admin Panel > Settings.

### General Settings

| Setting | Description |
|---------|-------------|
| `site_name` | Your site's name |
| `site_description` | Site description for SEO |
| `site_url` | Full URL of your site |
| `timezone` | Server timezone |
| `date_format` | Date display format |

### Feature Toggles

| Setting | Default | Description |
|---------|---------|-------------|
| `premium_enabled` | ON | Enable premium subscriptions |
| `registration_enabled` | ON | Allow new user registration |
| `contributor_system_enabled` | OFF | Enable contributor applications |
| `appreciate_system_enabled` | ON | Enable appreciation feature |

### Branding

| Setting | Description |
|---------|-------------|
| `site_logo` | Upload site logo |
| `logo_height` | Logo height in pixels |
| `primary_color` | Primary brand color |
| `secondary_color` | Secondary brand color |
| `accent_color` | Accent color |
| `dark_mode_toggle_enabled` | Show dark mode toggle |

### Site Mode (Adult Content)

| Setting | Description |
|---------|-------------|
| `adult_mode_enabled` | Enable adult content features |
| `age_gate_enabled` | Show age verification |
| `age_gate_min_age` | Minimum age required |
| `nsfw_blur_enabled` | Blur NSFW content |
| `nsfw_blur_strength` | Blur intensity |
| `quick_exit_enabled` | Show quick exit button |
| `right_click_disabled` | Disable right-click |

### Images

| Setting | Description |
|---------|-------------|
| `images_per_page` | Images per page |
| `max_upload_size` | Maximum upload size |
| `allowed_extensions` | Allowed file types |
| `auto_approve_images` | Auto-approve uploads |
| `enable_watermark` | Add watermark to images |

### SEO

| Setting | Description |
|---------|-------------|
| `meta_title_suffix` | Append to all titles |
| `default_meta_description` | Default description |
| `enable_sitemap` | Generate sitemap |
| `google_analytics_id` | GA tracking ID |

### API Keys

| Setting | Description |
|---------|-------------|
| `deepseek_api_key` | DeepSeek AI API key |
| `deepinfra_api_key` | DeepInfra API key |
| `unsplash_api_key` | Unsplash API key |
| `pexels_api_key` | Pexels API key |

## Marketing Settings

Access via Admin Panel > Marketing.

### Ad Settings

| Setting | Description |
|---------|-------------|
| `ads_enabled` | Enable ads globally |
| `ads_logged_in_users` | Show ads to logged-in users |
| `gallery_ad_frequency` | Show ad every N images |
| `blog_list_ad_frequency` | Show ad every N posts |

### Ad Networks

| Setting | Description |
|---------|-------------|
| `adsense_publisher_id` | Google AdSense publisher ID |
| `adsense_auto_ads` | Enable auto ads |
| `juicyads_publisher_id` | JuicyAds publisher ID |

### Tracking

| Setting | Description |
|---------|-------------|
| `google_analytics_id` | GA4 measurement ID |
| `gtm_id` | Google Tag Manager ID |
| `facebook_pixel_id` | Facebook Pixel ID |
| `custom_head_scripts` | Scripts in <head> |
| `custom_body_scripts` | Scripts before </body> |

### Social Media

| Setting | Description |
|---------|-------------|
| `social_facebook` | Facebook page URL |
| `social_twitter` | Twitter/X profile URL |
| `social_instagram` | Instagram profile URL |
| `social_pinterest` | Pinterest profile URL |
| `social_youtube` | YouTube channel URL |
| `social_tiktok` | TikTok profile URL |

## Environment-Specific Configuration

### Development

```php
'app' => [
    'env' => 'development',
    'debug' => true,
],
```

Features in development:
- Detailed error messages
- Ad placeholders shown
- No caching

### Production

```php
'app' => [
    'env' => 'production',
    'debug' => false,
],

'session' => [
    'secure' => true,
],

'cache' => [
    'enabled' => true,
],
```

Features in production:
- Errors logged, not displayed
- Full caching enabled
- Secure session cookies

## Caching

### Clear Cache

```php
// Clear settings cache
clear_settings_cache();

// Clear all cache
// Delete files in public_html/cache/
```

### Cache Locations

| Type | Location |
|------|----------|
| Page cache | `public_html/cache/` |
| Settings cache | `storage/cache/` |
| Session cache | `storage/sessions/` |

## Security Recommendations

### Production Checklist

- [ ] Set `debug` to `false`
- [ ] Set `env` to `production`
- [ ] Enable HTTPS and set `secure` to `true`
- [ ] Use strong database password
- [ ] Remove installation files
- [ ] Set proper file permissions
- [ ] Configure firewall
- [ ] Set up regular backups
- [ ] Monitor error logs
