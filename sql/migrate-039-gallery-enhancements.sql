-- Oregon Tires — Migration 039: Gallery Enhancements
-- Add video support and category classification to gallery

ALTER TABLE oretir_gallery_images
  ADD COLUMN media_type ENUM('image','video') NOT NULL DEFAULT 'image' AFTER image_url,
  ADD COLUMN video_url VARCHAR(500) DEFAULT NULL AFTER media_type,
  ADD COLUMN category ENUM('general','completed-work','facility','promotional') NOT NULL DEFAULT 'general' AFTER display_order;
