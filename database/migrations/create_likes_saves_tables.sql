-- Likes table (for both images and blog posts) - supports guests and logged-in users
CREATE TABLE IF NOT EXISTS likes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    guest_id VARCHAR(64) NULL,
    likeable_type ENUM('image', 'blog_post') NOT NULL,
    likeable_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_like (user_id, likeable_type, likeable_id),
    UNIQUE KEY unique_guest_like (guest_id, likeable_type, likeable_id),
    INDEX idx_likeable (likeable_type, likeable_id),
    INDEX idx_user (user_id),
    INDEX idx_guest (guest_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration for existing likes table (run if table already exists)
-- ALTER TABLE likes MODIFY COLUMN user_id INT UNSIGNED NULL;
-- ALTER TABLE likes ADD COLUMN guest_id VARCHAR(64) NULL AFTER user_id;
-- ALTER TABLE likes ADD COLUMN ip_address VARCHAR(45) NULL AFTER likeable_id;
-- ALTER TABLE likes DROP INDEX unique_like;
-- ALTER TABLE likes ADD UNIQUE KEY unique_user_like (user_id, likeable_type, likeable_id);
-- ALTER TABLE likes ADD UNIQUE KEY unique_guest_like (guest_id, likeable_type, likeable_id);
-- ALTER TABLE likes ADD INDEX idx_guest (guest_id);

-- Saves/Bookmarks table (for blog posts)
CREATE TABLE IF NOT EXISTS saves (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    saveable_type ENUM('image', 'blog_post') NOT NULL,
    saveable_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, saveable_type, saveable_id),
    INDEX idx_saveable (saveable_type, saveable_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add like_count columns
ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS like_count INT UNSIGNED DEFAULT 0;
ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS save_count INT UNSIGNED DEFAULT 0;
ALTER TABLE images ADD COLUMN IF NOT EXISTS like_count INT UNSIGNED DEFAULT 0;
