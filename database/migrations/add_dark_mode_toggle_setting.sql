-- Add dark mode toggle setting
-- Run this migration to add the setting to the database

INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public)
VALUES ('dark_mode_toggle_enabled', '1', 'bool', 'Show dark mode toggle button in header for visitors', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description);
