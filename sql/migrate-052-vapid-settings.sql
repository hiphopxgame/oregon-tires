-- Migration 052: Seed VAPID key placeholders into site settings
-- Keys are generated via cli/generate-vapid-keys.php

INSERT INTO `oretir_site_settings` (`setting_key`, `value_en`, `updated_at`)
VALUES
    ('vapid_public_key', '', NOW()),
    ('vapid_private_key', '', NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
