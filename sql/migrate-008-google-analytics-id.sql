-- Migration 008: Add google_analytics_id to site settings
-- Run: ssh hiphopworld then mysql hiphopwo_oregon_tires < this file
-- Or run via phpMyAdmin

INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  ('google_analytics_id', 'G-CHYMTNB6LH', 'G-CHYMTNB6LH')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
