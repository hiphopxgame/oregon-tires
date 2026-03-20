-- Migration 056: Add admin settings for promotional features
-- All promotional UI is hidden by default until admin explicitly enables it.

-- Scarcity messaging ("Only X slots left today") - off by default
INSERT INTO oretir_site_settings (setting_key, value_en, value_es)
VALUES ('show_scarcity', '0', '0')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Deactivate any seeded promotions that may have been auto-activated
UPDATE oretir_promotions SET is_active = 0 WHERE is_active = 1;
