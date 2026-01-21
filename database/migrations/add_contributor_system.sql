-- Add contributor role to users table
ALTER TABLE users
    MODIFY COLUMN role ENUM('user', 'contributor', 'moderator', 'admin', 'superadmin') DEFAULT 'user';

-- Create contributor_requests table
CREATE TABLE IF NOT EXISTS contributor_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_reason TEXT NULL,
    admin_note TEXT NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert new feature settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('premium_enabled', '1', 'bool', 'Enable premium subscription feature', 0),
('registration_enabled', '1', 'bool', 'Enable new user registration', 0),
('contributor_system_enabled', '0', 'bool', 'Enable contributor role system', 0),
('appreciate_system_enabled', '1', 'bool', 'Enable appreciate button site-wide', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;
