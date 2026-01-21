-- Migration: Add WebP image path columns
-- Run this to add WebP support columns to existing databases
-- Date: 2026-01-19

-- Add thumbnail WebP path column
ALTER TABLE images ADD COLUMN IF NOT EXISTS thumbnail_webp_path VARCHAR(500) NULL AFTER thumbnail_path;

-- Add medium size columns
ALTER TABLE images ADD COLUMN IF NOT EXISTS medium_path VARCHAR(500) NULL AFTER thumbnail_webp_path;
ALTER TABLE images ADD COLUMN IF NOT EXISTS medium_webp_path VARCHAR(500) NULL AFTER medium_path;

-- Note: webp_path should already exist, but ensure it does
-- ALTER TABLE images ADD COLUMN IF NOT EXISTS webp_path VARCHAR(500) NULL AFTER medium_webp_path;

-- For MySQL versions that don't support IF NOT EXISTS on ALTER TABLE,
-- use these instead (will error if column exists, which is fine):
--
-- ALTER TABLE images ADD COLUMN thumbnail_webp_path VARCHAR(500) NULL AFTER thumbnail_path;
-- ALTER TABLE images ADD COLUMN medium_path VARCHAR(500) NULL AFTER thumbnail_webp_path;
-- ALTER TABLE images ADD COLUMN medium_webp_path VARCHAR(500) NULL AFTER medium_path;
