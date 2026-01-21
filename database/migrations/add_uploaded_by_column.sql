-- Add uploaded_by column to images table
ALTER TABLE images ADD COLUMN uploaded_by INT UNSIGNED NULL AFTER user_id;
ALTER TABLE images ADD INDEX idx_uploaded_by (uploaded_by);
