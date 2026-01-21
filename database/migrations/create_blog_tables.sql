-- Blog System Database Schema
-- Run this migration to create all blog-related tables

-- Blog Categories
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    meta_title VARCHAR(70),
    meta_description VARCHAR(160),
    parent_id INT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    post_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Tags
CREATE TABLE IF NOT EXISTS blog_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    post_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255),
    featured_image_alt VARCHAR(255),

    -- SEO Fields
    meta_title VARCHAR(70),
    meta_description VARCHAR(160),
    meta_keywords VARCHAR(255),
    canonical_url VARCHAR(255),
    og_image VARCHAR(255),

    -- Organization
    category_id INT UNSIGNED,
    author_id INT UNSIGNED NOT NULL,

    -- Status & Visibility
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    visibility ENUM('public', 'private', 'password') DEFAULT 'public',
    password_hash VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    allow_comments BOOLEAN DEFAULT TRUE,

    -- AI Generation
    ai_generated BOOLEAN DEFAULT FALSE,
    ai_prompt TEXT,
    ai_model VARCHAR(50),

    -- Stats
    view_count INT UNSIGNED DEFAULT 0,
    comment_count INT UNSIGNED DEFAULT 0,
    read_time_minutes TINYINT UNSIGNED DEFAULT 1,

    -- Timestamps
    published_at TIMESTAMP NULL,
    scheduled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published (published_at),
    INDEX idx_featured (is_featured),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    FULLTEXT idx_search (title, excerpt, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Tags (Many-to-Many)
CREATE TABLE IF NOT EXISTS blog_post_tags (
    post_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Comments
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    parent_id INT UNSIGNED,

    -- Guest info (if not logged in)
    guest_name VARCHAR(100),
    guest_email VARCHAR(255),
    guest_website VARCHAR(255),

    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),

    likes_count INT UNSIGNED DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_status (status),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Related Posts (for manual curation)
CREATE TABLE IF NOT EXISTS blog_related_posts (
    post_id INT UNSIGNED NOT NULL,
    related_post_id INT UNSIGNED NOT NULL,
    sort_order TINYINT DEFAULT 0,
    PRIMARY KEY (post_id, related_post_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (related_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Revisions (for version history)
CREATE TABLE IF NOT EXISTS blog_post_revisions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NOT NULL,
    revision_note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default blog categories
INSERT INTO blog_categories (name, slug, description, sort_order) VALUES
('Uncategorized', 'uncategorized', 'Default category for posts', 0),
('News', 'news', 'Latest news and announcements', 1),
('Tutorials', 'tutorials', 'How-to guides and tutorials', 2),
('Tips & Tricks', 'tips-tricks', 'Helpful tips and tricks', 3),
('Inspiration', 'inspiration', 'Creative inspiration and ideas', 4)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Add blog settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('blog_posts_per_page', '12', 'int', 'Number of blog posts per page', 0),
('blog_excerpt_length', '200', 'int', 'Character length for auto-generated excerpts', 0),
('blog_comments_enabled', '1', 'bool', 'Enable comments on blog posts', 0),
('blog_comments_moderation', '1', 'bool', 'Require approval for new comments', 0),
('blog_ai_enabled', '1', 'bool', 'Enable AI content generation for blog', 0),
('blog_related_posts_count', '4', 'int', 'Number of related posts to show', 0)
ON DUPLICATE KEY UPDATE description = VALUES(description);
