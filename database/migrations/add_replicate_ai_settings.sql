-- Add Replicate AI API key and AI provider settings
-- Run this migration to enable Replicate/LLaVA for image analysis

-- AI Provider setting (claude or replicate)
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public)
VALUES ('ai_provider', 'replicate', 'select', 'AI provider for image analysis (claude or replicate)', 0)
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Replicate API key
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public)
VALUES ('replicate_api_key', '', 'encrypted', 'Replicate API key for LLaVA image analysis (NSFW-friendly)', 0)
ON DUPLICATE KEY UPDATE description = VALUES(description);
