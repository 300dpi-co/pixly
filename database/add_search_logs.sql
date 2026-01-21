CREATE TABLE IF NOT EXISTS search_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(255) NOT NULL,
    result_count INT UNSIGNED DEFAULT 0,
    search_count INT UNSIGNED DEFAULT 1,
    user_id INT UNSIGNED NULL,
    last_searched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_query (query),
    INDEX idx_count (search_count DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
