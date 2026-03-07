-- Migration 011: Add bilingual columns to gallery_images
-- Applied: 2026-03-07

ALTER TABLE oretir_gallery_images
  ADD COLUMN title_en VARCHAR(200) DEFAULT NULL AFTER image_url,
  ADD COLUMN title_es VARCHAR(200) DEFAULT NULL AFTER title_en,
  ADD COLUMN description_en TEXT DEFAULT NULL AFTER title_es,
  ADD COLUMN description_es TEXT DEFAULT NULL AFTER description_en;

UPDATE oretir_gallery_images SET title_en = title, description_en = description;

ALTER TABLE oretir_gallery_images DROP COLUMN title, DROP COLUMN description;
