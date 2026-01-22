-- Add is_trusted column to users table for trusted uploader feature
ALTER TABLE users ADD COLUMN is_trusted TINYINT(1) DEFAULT 0 AFTER is_public;
ALTER TABLE users ADD INDEX idx_is_trusted (is_trusted);
