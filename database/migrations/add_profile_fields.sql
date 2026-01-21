-- Add profile fields to users table
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS display_name VARCHAR(100) NULL AFTER username,
    ADD COLUMN IF NOT EXISTS website VARCHAR(255) NULL AFTER bio,
    ADD COLUMN IF NOT EXISTS location VARCHAR(100) NULL AFTER website,
    ADD COLUMN IF NOT EXISTS twitter_handle VARCHAR(50) NULL AFTER location,
    ADD COLUMN IF NOT EXISTS instagram_handle VARCHAR(50) NULL AFTER twitter_handle,
    ADD COLUMN IF NOT EXISTS is_public TINYINT(1) DEFAULT 1 AFTER instagram_handle;

-- Add index for public profiles
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_is_public (is_public);
