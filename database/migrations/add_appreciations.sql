-- Add appreciate_count to images table
ALTER TABLE images
    ADD COLUMN IF NOT EXISTS appreciate_count INT UNSIGNED DEFAULT 0 AFTER favorite_count;

-- Create appreciations table
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

-- Add index for top contributors query (likes + appreciations)
ALTER TABLE images ADD INDEX IF NOT EXISTS idx_user_engagement (user_id, favorite_count, appreciate_count);
