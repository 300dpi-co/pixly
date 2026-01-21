-- Add Site Mode Settings
-- These settings control layout presets and adult content features

INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('layout_preset', 'clean-minimal', 'string', 'Homepage layout preset theme', 1),
('adult_mode_enabled', '0', 'bool', 'Enable adult content features', 0),
('age_gate_enabled', '0', 'bool', 'Show age verification gate', 0),
('age_gate_style', 'modal', 'string', 'Age gate display style (modal or fullpage)', 0),
('age_gate_title', 'Age Verification Required', 'string', 'Age gate title text', 0),
('age_gate_message', 'This website contains age-restricted content. By entering, you confirm that you are at least 18 years old.', 'string', 'Age gate message text', 0),
('age_gate_min_age', '18', 'string', 'Minimum age required', 0),
('age_gate_remember', '7d', 'string', 'How long to remember verification', 0),
('nsfw_blur_enabled', '0', 'bool', 'Blur NSFW thumbnails until interaction', 0),
('nsfw_blur_strength', '20', 'string', 'Blur strength in pixels', 0),
('nsfw_reveal_on', 'click', 'string', 'Reveal NSFW images on click or hover', 0),
('quick_exit_enabled', '0', 'bool', 'Show quick exit button', 0),
('quick_exit_url', 'https://www.google.com', 'string', 'URL to redirect on quick exit', 0),
('quick_exit_text', 'Exit', 'string', 'Quick exit button text', 0),
('content_warning_enabled', '0', 'bool', 'Show content warnings', 0),
('content_warning_text', 'This content may contain adult material.', 'string', 'Content warning text', 0),
('right_click_disabled', '0', 'bool', 'Disable right-click context menu', 0),
('private_browsing_notice', '0', 'bool', 'Suggest private browsing mode', 0),
('disclaimer_enabled', '0', 'bool', 'Show legal disclaimer in footer', 0),
('disclaimer_text', 'All models are 18 years of age or older. All content complies with applicable laws.', 'string', 'Disclaimer text', 0),
('compliance_2257_url', '', 'string', '18 U.S.C. 2257 compliance page URL', 0)
ON DUPLICATE KEY UPDATE setting_key = setting_key;
