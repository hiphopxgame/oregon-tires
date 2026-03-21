-- Migration 062: Add check_in status to repair orders + timer columns
-- Adds the check_in step between intake and diagnosis for customer check-in tracking
-- Also adds denormalized timer columns for fast query access

-- Add check_in to the status ENUM
ALTER TABLE oretir_repair_orders
  MODIFY COLUMN status ENUM(
    'intake','check_in','diagnosis','estimate_pending','pending_approval',
    'approved','in_progress','on_hold','waiting_parts',
    'ready','completed','invoiced','cancelled'
  ) NOT NULL DEFAULT 'intake';

-- Timer columns on RO for fast queries (mirrors visit_log)
ALTER TABLE oretir_repair_orders
  ADD COLUMN checked_in_at DATETIME DEFAULT NULL AFTER promised_time,
  ADD COLUMN service_started_at DATETIME DEFAULT NULL AFTER checked_in_at,
  ADD COLUMN service_ended_at DATETIME DEFAULT NULL AFTER service_started_at,
  ADD COLUMN checked_out_at DATETIME DEFAULT NULL AFTER service_ended_at,
  ADD COLUMN visit_log_id INT UNSIGNED DEFAULT NULL AFTER appointment_id;
