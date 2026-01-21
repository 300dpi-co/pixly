-- Marketing Tables Migration

-- Drop old ad_placements table if exists (schema.sql has different structure)
DROP TABLE IF EXISTS ad_placements;

-- Ad Placements (defines where ads can appear)
CREATE TABLE IF NOT EXISTS ad_placements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    location ENUM('header', 'footer', 'sidebar', 'in_content', 'popup', 'gallery', 'interstitial') NOT NULL,
    default_size VARCHAR(50) NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (location),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ads (individual ad units)
CREATE TABLE IF NOT EXISTS ads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    placement_id INT UNSIGNED NULL,
    ad_type ENUM('custom_html', 'image', 'adsense', 'juicyads', 'native') DEFAULT 'custom_html',
    content TEXT NULL,
    image_path VARCHAR(255) NULL,
    destination_url VARCHAR(500) NULL,
    adsense_slot VARCHAR(100) NULL,
    juicyads_zone VARCHAR(100) NULL,

    -- Targeting
    device_target ENUM('all', 'desktop', 'mobile') DEFAULT 'all',
    pages_target JSON NULL,
    pages_exclude JSON NULL,
    geo_target JSON NULL,

    -- Scheduling
    start_date DATETIME NULL,
    end_date DATETIME NULL,

    -- Stats
    impressions INT UNSIGNED DEFAULT 0,
    clicks INT UNSIGNED DEFAULT 0,

    -- Status
    is_active TINYINT(1) DEFAULT 1,
    priority INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_placement (placement_id),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date),
    FOREIGN KEY (placement_id) REFERENCES ad_placements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pop-up Ads Configuration
CREATE TABLE IF NOT EXISTS popup_ads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,

    -- Trigger settings
    trigger_type ENUM('page_load', 'exit_intent', 'scroll', 'timed', 'click') DEFAULT 'page_load',
    trigger_delay INT DEFAULT 0,
    trigger_scroll_percent INT DEFAULT 50,

    -- Display settings
    show_on_mobile TINYINT(1) DEFAULT 1,
    overlay_opacity INT DEFAULT 50,
    position ENUM('center', 'bottom', 'top', 'bottom_right', 'bottom_left') DEFAULT 'center',
    animation ENUM('fade', 'slide', 'zoom', 'none') DEFAULT 'fade',
    width VARCHAR(20) DEFAULT '500px',

    -- Frequency
    frequency ENUM('every_visit', 'once_session', 'once_day', 'once_week', 'once_ever') DEFAULT 'once_session',
    cookie_days INT DEFAULT 7,

    -- Targeting
    pages_include JSON NULL,
    pages_exclude JSON NULL,
    device_target ENUM('all', 'desktop', 'mobile') DEFAULT 'all',

    -- Scheduling
    start_date DATETIME NULL,
    end_date DATETIME NULL,

    -- Stats
    impressions INT UNSIGNED DEFAULT 0,
    closes INT UNSIGNED DEFAULT 0,
    clicks INT UNSIGNED DEFAULT 0,

    is_active TINYINT(1) DEFAULT 0,
    priority INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active),
    INDEX idx_trigger (trigger_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Announcement Bar
CREATE TABLE IF NOT EXISTS announcement_bars (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    link_url VARCHAR(500) NULL,
    link_text VARCHAR(100) NULL,

    -- Styling
    bg_color VARCHAR(20) DEFAULT '#3b82f6',
    text_color VARCHAR(20) DEFAULT '#ffffff',

    -- Settings
    is_dismissible TINYINT(1) DEFAULT 1,
    cookie_days INT DEFAULT 1,
    position ENUM('top', 'bottom') DEFAULT 'top',
    is_sticky TINYINT(1) DEFAULT 0,

    -- Targeting
    pages_include JSON NULL,
    pages_exclude JSON NULL,

    -- Scheduling
    start_date DATETIME NULL,
    end_date DATETIME NULL,

    is_active TINYINT(1) DEFAULT 0,
    priority INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Newsletter Subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(150) NULL,

    status ENUM('pending', 'confirmed', 'unsubscribed') DEFAULT 'pending',
    confirmation_token VARCHAR(64) NULL,
    confirmed_at DATETIME NULL,
    unsubscribed_at DATETIME NULL,

    source VARCHAR(50) DEFAULT 'website',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marketing Settings (tracking codes, social links, etc.)
CREATE TABLE IF NOT EXISTS marketing_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default ad placements
INSERT INTO ad_placements (name, slug, description, location, default_size, sort_order) VALUES
('Header Banner', 'header_banner', 'Top of every page (728x90 desktop, 320x50 mobile)', 'header', '728x90', 1),
('Footer Banner', 'footer_banner', 'Bottom of every page', 'footer', '728x90', 2),
('Sticky Mobile Footer', 'sticky_mobile_footer', 'Fixed bottom ad on mobile', 'footer', '320x50', 3),
('Blog Sidebar', 'blog_sidebar', 'Right sidebar on blog pages', 'sidebar', '300x250', 10),
('Gallery Sidebar', 'gallery_sidebar', 'Sidebar on image pages', 'sidebar', '300x250', 11),
('Blog In-Article Top', 'blog_article_top', 'After first paragraph in blog posts', 'in_content', '728x90', 20),
('Blog In-Article Middle', 'blog_article_middle', 'Middle of blog post content', 'in_content', '300x250', 21),
('Blog In-Article Bottom', 'blog_article_bottom', 'Before related posts', 'in_content', '728x90', 22),
('Between Blog Posts', 'blog_list_between', 'Between posts in blog listing', 'in_content', '728x90', 23),
('Gallery Grid Ad', 'gallery_grid', 'Between images in gallery grid', 'gallery', '300x250', 30),
('Below Image', 'below_image', 'Below main image on image page', 'gallery', '728x90', 31),
('Between Related Images', 'related_images_between', 'Middle of related images', 'gallery', '300x250', 32)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert default marketing settings
INSERT INTO marketing_settings (setting_key, setting_value, setting_group) VALUES
-- Tracking
('google_analytics_id', '', 'tracking'),
('gtm_id', '', 'tracking'),
('facebook_pixel_id', '', 'tracking'),
('custom_head_scripts', '', 'tracking'),
('custom_body_scripts', '', 'tracking'),

-- AdSense
('adsense_publisher_id', '', 'adsense'),
('adsense_auto_ads', '0', 'adsense'),

-- JuicyAds
('juicyads_publisher_id', '', 'juicyads'),

-- Newsletter
('newsletter_enabled', '1', 'newsletter'),
('newsletter_double_optin', '1', 'newsletter'),
('newsletter_popup_enabled', '0', 'newsletter'),
('newsletter_popup_delay', '5', 'newsletter'),
('newsletter_welcome_email', '1', 'newsletter'),

-- Social
('social_facebook', '', 'social'),
('social_twitter', '', 'social'),
('social_instagram', '', 'social'),
('social_pinterest', '', 'social'),
('social_youtube', '', 'social'),
('social_tiktok', '', 'social'),
('share_buttons_enabled', '1', 'social'),
('pinterest_pin_button', '1', 'social'),

-- Ad Settings
('ads_enabled', '1', 'ads'),
('ads_logged_in_users', '1', 'ads'),
('gallery_ad_frequency', '6', 'ads'),
('blog_list_ad_frequency', '4', 'ads')
ON DUPLICATE KEY UPDATE setting_key=VALUES(setting_key);
