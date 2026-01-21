-- Add Claude AI API key setting
-- Run this migration to add the Claude API key setting

INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public)
VALUES ('claude_api_key', '', 'encrypted', 'Claude AI API key for image analysis', 0)
ON DUPLICATE KEY UPDATE description = VALUES(description);
