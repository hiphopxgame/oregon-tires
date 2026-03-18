-- Oregon Tires — Migration 041: Promotions Polish
-- Add bilingual badge text + expand placement options

-- Rename badge_text to badge_text_en and add badge_text_es
ALTER TABLE oretir_promotions
  CHANGE COLUMN badge_text badge_text_en VARCHAR(50) DEFAULT NULL,
  ADD COLUMN badge_text_es VARCHAR(50) DEFAULT NULL AFTER badge_text_en;

-- Expand placement to include sidebar and inline
-- (placement is VARCHAR(30) so no ALTER needed, just update validation in PHP)
