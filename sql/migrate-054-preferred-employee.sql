-- Migration 054: Add preferred_employee_id to appointments + reminder_hours_before setting
-- Date: 2026-03-20

-- Customer can request a preferred technician during booking
ALTER TABLE oretir_appointments
  ADD COLUMN IF NOT EXISTS preferred_employee_id INT UNSIGNED DEFAULT NULL
  AFTER assigned_employee_id;

-- Configurable reminder interval (hours before appointment)
INSERT INTO oretir_site_settings (setting_key, setting_value) VALUES
  ('reminder_hours_before', '18')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
