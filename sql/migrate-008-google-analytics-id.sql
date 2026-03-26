-- Migration 008: Add google_analytics_id to site settings
-- Run: ssh hiphopworld then mysql hiphopwo_oregon_tires < this file
-- Or run via phpMyAdmin

INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  ('google_analytics_id', 'G-PCK6ZYFHQ0', 'G-PCK6ZYFHQ0')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
