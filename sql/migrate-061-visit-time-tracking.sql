-- Migration 061: Visit Time Tracking Alignment
-- Adds time-tracking to appointments, links waitlist → visit_log,
-- and adds duration columns for optimization analytics.

-- ═══════════════════════════════════════════════════════════════
-- 1. Add time-tracking columns to appointments
-- ═══════════════════════════════════════════════════════════════
ALTER TABLE oretir_appointments
  ADD COLUMN check_in_at DATETIME DEFAULT NULL AFTER admin_notes,
  ADD COLUMN service_start_at DATETIME DEFAULT NULL AFTER check_in_at,
  ADD COLUMN service_end_at DATETIME DEFAULT NULL AFTER service_start_at,
  ADD COLUMN check_out_at DATETIME DEFAULT NULL AFTER service_end_at;

-- ═══════════════════════════════════════════════════════════════
-- 2. Add waitlist_id FK to visit_log for proper linking
-- ═══════════════════════════════════════════════════════════════
ALTER TABLE oretir_visit_log
  ADD COLUMN waitlist_id INT UNSIGNED DEFAULT NULL AFTER repair_order_id,
  ADD INDEX idx_waitlist (waitlist_id);

-- ═══════════════════════════════════════════════════════════════
-- 3. Add computed duration columns to visit_log for analytics
-- ═══════════════════════════════════════════════════════════════
ALTER TABLE oretir_visit_log
  ADD COLUMN wait_minutes INT UNSIGNED DEFAULT NULL AFTER check_out_at,
  ADD COLUMN service_minutes INT UNSIGNED DEFAULT NULL AFTER wait_minutes,
  ADD COLUMN total_minutes INT UNSIGNED DEFAULT NULL AFTER service_minutes;

-- ═══════════════════════════════════════════════════════════════
-- 4. Add assigned_employee_id to visit_log for tech tracking
-- ═══════════════════════════════════════════════════════════════
ALTER TABLE oretir_visit_log
  ADD COLUMN assigned_employee_id INT UNSIGNED DEFAULT NULL AFTER bay_number,
  ADD INDEX idx_employee (assigned_employee_id);

-- ═══════════════════════════════════════════════════════════════
-- 5. Add service type to visit_log
-- ═══════════════════════════════════════════════════════════════
ALTER TABLE oretir_visit_log
  ADD COLUMN service VARCHAR(100) DEFAULT NULL AFTER assigned_employee_id;
