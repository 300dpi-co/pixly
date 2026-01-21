-- Add is_animated column to images table

ALTER TABLE images ADD COLUMN is_animated TINYINT(1) DEFAULT 0 AFTER dominant_color;
