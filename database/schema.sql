-- FWP Image Gallery Database Schema
-- Run this file to create all tables
-- Note: Database is created by the installation wizard

-- =====================================================
-- Users & Authentication
-- =====================================================

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'moderator', 'admin', 'superadmin') DEFAULT 'user',
    status ENUM('pending', 'active', 'suspended', 'banned') DEFAULT 'pending',
    email_verified_at DATETIME NULL,
    verification_token VARCHAR(64) NULL,
    avatar_path VARCHAR(255) NULL,
    bio TEXT NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(100) NULL,
    twitter_handle VARCHAR(50) NULL,
    instagram_handle VARCHAR(50) NULL,
    is_public TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table (for database sessions)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auth logs table
CREATE TABLE IF NOT EXISTS auth_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    event_type ENUM('login', 'logout', 'failed_login', 'password_reset', 'register') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    details JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_event (event_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Content Tables (for later phases)
-- =====================================================

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    meta_title VARCHAR(70) NULL,
    meta_description VARCHAR(160) NULL,
    cover_image_id INT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    image_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags table
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    usage_count INT UNSIGNED DEFAULT 0,
    trend_score DECIMAL(5,2) DEFAULT 0.00,
    is_trending BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_usage (usage_count),
    INDEX idx_trending (is_trending, trend_score DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images table
CREATE TABLE IF NOT EXISTS images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL,

    -- File information
    original_filename VARCHAR(255) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    thumbnail_path VARCHAR(500) NULL,
    thumbnail_webp_path VARCHAR(500) NULL,
    medium_path VARCHAR(500) NULL,
    medium_webp_path VARCHAR(500) NULL,
    webp_path VARCHAR(500) NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(50) NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,

    -- SEO & Display metadata
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) NOT NULL UNIQUE,
    alt_text VARCHAR(255) NOT NULL,
    description TEXT NULL,
    caption VARCHAR(500) NULL,

    -- Content source
    source ENUM('upload', 'unsplash', 'pexels', 'ai_generated', 'other') DEFAULT 'upload',
    source_url VARCHAR(500) NULL,
    source_id VARCHAR(100) NULL,
    photographer_name VARCHAR(200) NULL,
    photographer_url VARCHAR(500) NULL,
    license_type VARCHAR(100) DEFAULT 'all-rights-reserved',

    -- AI-generated metadata
    ai_tags JSON NULL,
    ai_description TEXT NULL,
    ai_category_suggestions JSON NULL,
    ai_processed_at DATETIME NULL,

    -- Moderation
    moderation_status ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    moderation_score DECIMAL(4,3) NULL,
    moderation_labels JSON NULL,
    moderated_by INT UNSIGNED NULL,
    moderated_at DATETIME NULL,
    rejection_reason TEXT NULL,

    -- Publishing
    status ENUM('draft', 'published', 'archived', 'deleted') DEFAULT 'draft',
    published_at DATETIME NULL,
    featured BOOLEAN DEFAULT FALSE,
    featured_order INT NULL,

    -- Statistics
    view_count INT UNSIGNED DEFAULT 0,
    download_count INT UNSIGNED DEFAULT 0,
    favorite_count INT UNSIGNED DEFAULT 0,
    appreciate_count INT UNSIGNED DEFAULT 0,
    comment_count INT UNSIGNED DEFAULT 0,

    -- Extra data
    exif_data JSON NULL,
    color_palette JSON NULL,
    dominant_color VARCHAR(7) NULL,
    is_animated TINYINT(1) DEFAULT 0,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_uuid (uuid),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_moderation (moderation_status),
    INDEX idx_source (source),
    INDEX idx_featured (featured, featured_order),
    INDEX idx_published (status, published_at DESC),
    INDEX idx_views (view_count DESC),
    FULLTEXT idx_search (title, description, alt_text, caption)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Image-Category pivot table
CREATE TABLE IF NOT EXISTS image_categories (
    image_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (image_id, category_id),
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Image-Tag pivot table
CREATE TABLE IF NOT EXISTS image_tags (
    image_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    source ENUM('manual', 'ai', 'trend') DEFAULT 'manual',
    relevance_score DECIMAL(4,3) DEFAULT 1.000,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (image_id, tag_id),
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    image_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, image_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_image (image_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appreciations table (for Pexels theme Top Contributors)
CREATE TABLE IF NOT EXISTS appreciations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    image_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_appreciation (user_id, image_id),
    INDEX idx_image_id (image_id),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'deleted') DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_image (image_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- AI & Trends
-- =====================================================

-- AI processing queue
CREATE TABLE IF NOT EXISTS ai_processing_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id INT UNSIGNED NOT NULL,
    task_type ENUM('metadata', 'moderation', 'tags', 'description', 'all') NOT NULL,
    priority TINYINT DEFAULT 5,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    attempts TINYINT DEFAULT 0,
    max_attempts TINYINT DEFAULT 3,
    error_message TEXT NULL,
    scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_priority (priority DESC, scheduled_at ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trending keywords
CREATE TABLE IF NOT EXISTS trending_keywords (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    source ENUM('google', 'pinterest', 'internal') NOT NULL,
    region VARCHAR(5) DEFAULT 'US',
    category VARCHAR(100) NULL,
    trend_score INT DEFAULT 0,
    search_volume INT UNSIGNED NULL,
    growth_rate DECIMAL(6,2) NULL,
    related_keywords JSON NULL,
    fetched_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_keyword (keyword),
    INDEX idx_source (source),
    INDEX idx_score (trend_score DESC),
    UNIQUE KEY unique_trend (keyword, source, region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- System & Analytics
-- =====================================================

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'int', 'bool', 'json', 'encrypted') DEFAULT 'string',
    description VARCHAR(500) NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page views / Analytics
CREATE TABLE IF NOT EXISTS page_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id INT UNSIGNED NULL,
    page_type ENUM('home', 'image', 'category', 'tag', 'search', 'other') NOT NULL,
    page_identifier VARCHAR(255) NULL,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(128) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    referrer VARCHAR(500) NULL,
    country_code CHAR(2) NULL,
    device_type ENUM('desktop', 'mobile', 'tablet', 'bot') NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_image (image_id),
    INDEX idx_page (page_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad placements table is created by create_marketing_tables.sql migration
-- with a more complete structure including slug, location, description, etc.

-- Search logs
CREATE TABLE IF NOT EXISTS search_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(255) NOT NULL,
    result_count INT UNSIGNED DEFAULT 0,
    search_count INT UNSIGNED DEFAULT 1,
    user_id INT UNSIGNED NULL,
    last_searched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_query (query),
    INDEX idx_count (search_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API logs
CREATE TABLE IF NOT EXISTS api_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_name ENUM('deepseek', 'google_trends', 'pinterest', 'unsplash', 'pexels') NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_data JSON NULL,
    response_code INT NULL,
    response_data JSON NULL,
    tokens_used INT NULL,
    cost_estimate DECIMAL(10,6) NULL,
    duration_ms INT UNSIGNED NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api (api_name),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Image reports (copyright, DMCA, flags)
CREATE TABLE IF NOT EXISTS image_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    reason ENUM('copyright', 'dmca', 'inappropriate', 'spam', 'other') NOT NULL,
    details TEXT NULL,
    reporter_email VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT NULL,
    resolved_by INT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_image (image_id),
    INDEX idx_status (status),
    INDEX idx_reason (reason),
    INDEX idx_created (created_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insert default data
-- =====================================================

-- Create admin user (password: changeme123)
INSERT INTO users (username, email, password_hash, role, status, email_verified_at) VALUES
('admin', 'admin@example.com', '$argon2id$v=19$m=65536,t=4,p=1$dGVzdHNhbHQ$YWRtaW5wYXNzd29yZA', 'admin', 'active', NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'FWP Image Gallery', 'string', 'Site name', TRUE),
('site_description', 'Discover trending images and inspiring content', 'string', 'Site description for SEO', TRUE),
('images_per_page', '24', 'int', 'Images per page in gallery', FALSE),
('auto_approve_images', '0', 'bool', 'Auto approve uploaded images', FALSE),
('enable_comments', '1', 'bool', 'Enable comments on images', FALSE),
('enable_registration', '1', 'bool', 'Allow new user registration', FALSE),
('dark_mode_toggle_enabled', '1', 'bool', 'Show dark mode toggle button in header for visitors', TRUE),
('layout_preset', 'clean-minimal', 'string', 'Homepage layout preset theme', TRUE),
('adult_mode_enabled', '0', 'bool', 'Enable adult content features', FALSE),
('age_gate_enabled', '0', 'bool', 'Show age verification gate', FALSE),
('age_gate_style', 'modal', 'string', 'Age gate display style', FALSE),
('age_gate_title', 'Age Verification Required', 'string', 'Age gate title text', FALSE),
('age_gate_message', 'This website contains age-restricted content. By entering, you confirm that you are at least 18 years old.', 'string', 'Age gate message', FALSE),
('age_gate_min_age', '18', 'string', 'Minimum age required', FALSE),
('age_gate_remember', '7d', 'string', 'How long to remember verification', FALSE),
('nsfw_blur_enabled', '0', 'bool', 'Blur NSFW thumbnails', FALSE),
('nsfw_blur_strength', '20', 'string', 'Blur strength in pixels', FALSE),
('nsfw_reveal_on', 'click', 'string', 'Reveal on click or hover', FALSE),
('quick_exit_enabled', '0', 'bool', 'Show quick exit button', FALSE),
('quick_exit_url', 'https://www.google.com', 'string', 'Quick exit redirect URL', FALSE),
('quick_exit_text', 'Exit', 'string', 'Quick exit button text', FALSE),
('content_warning_enabled', '0', 'bool', 'Show content warnings', FALSE),
('content_warning_text', 'This content may contain adult material.', 'string', 'Content warning text', FALSE),
('right_click_disabled', '0', 'bool', 'Disable right-click', FALSE),
('private_browsing_notice', '0', 'bool', 'Suggest private browsing', FALSE),
('disclaimer_enabled', '0', 'bool', 'Show legal disclaimer', FALSE),
('disclaimer_text', 'All models are 18 years of age or older.', 'string', 'Disclaimer text', FALSE),
('compliance_2257_url', '', 'string', '2257 compliance page URL', FALSE)
ON DUPLICATE KEY UPDATE id=id;
