-- Migration: Add image_reports table
-- Run this SQL to add the image reports table to an existing database

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
