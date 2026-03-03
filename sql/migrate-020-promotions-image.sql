-- Migration 020: Add image_url column to promotions
ALTER TABLE oretir_promotions
    ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER badge_text;
