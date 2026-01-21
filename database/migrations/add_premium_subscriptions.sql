-- Premium Subscriptions Table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    plan_type ENUM('monthly', 'yearly') NOT NULL DEFAULT 'yearly',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'INR',
    status ENUM('active', 'cancelled', 'expired', 'pending') NOT NULL DEFAULT 'pending',
    payment_id VARCHAR(100) NULL,
    payment_method VARCHAR(50) NULL,
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add premium column to users table
ALTER TABLE users ADD COLUMN is_premium TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
ALTER TABLE users ADD COLUMN premium_until DATETIME NULL AFTER is_premium;

-- Payment transactions log
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subscription_id INT UNSIGNED NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_gateway VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'INR',
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    gateway_response JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    INDEX idx_transaction (transaction_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans into settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('subscription_yearly_price', '99', 'string', 'Yearly subscription price'),
('subscription_monthly_price', '15', 'string', 'Monthly subscription price'),
('subscription_currency', 'INR', 'string', 'Subscription currency'),
('premium_features', '["ad_free","unlimited_downloads","priority_support","early_access"]', 'json', 'Premium features list'),
('adblock_detection_enabled', '1', 'bool', 'Enable ad blocker detection')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
