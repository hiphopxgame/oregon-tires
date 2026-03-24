-- Migration 067: Google Calendar sync configuration
-- Adds site_settings rows for calendar sync + sync columns on repair_orders

-- Calendar sync configuration in site_settings
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
    ('google_calendar_sync_enabled', '0', '0'),
    ('google_calendar_id', '', ''),
    ('google_calendar_last_sync', '', '')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add calendar sync columns to repair_orders (mirrors appointments pattern)
ALTER TABLE oretir_repair_orders
    ADD COLUMN google_event_id VARCHAR(255) DEFAULT NULL AFTER admin_notes,
    ADD COLUMN calendar_sync_status ENUM('pending','success','failed') DEFAULT NULL AFTER google_event_id,
    ADD COLUMN calendar_synced_at TIMESTAMP NULL DEFAULT NULL AFTER calendar_sync_status,
    ADD COLUMN calendar_sync_error TEXT DEFAULT NULL AFTER calendar_synced_at,
    ADD COLUMN calendar_sync_attempts TINYINT UNSIGNED DEFAULT 0 AFTER calendar_sync_error;

-- Add sync attempts column to appointments (for retry logic)
ALTER TABLE oretir_appointments
    ADD COLUMN calendar_sync_attempts TINYINT UNSIGNED DEFAULT 0 AFTER calendar_sync_error;
