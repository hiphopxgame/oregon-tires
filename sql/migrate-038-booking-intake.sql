-- Oregon Tires — Migration 038: Booking Intake Enhancements
-- Add tire preference and count fields to appointments

ALTER TABLE oretir_appointments
  ADD COLUMN tire_preference ENUM('new','used','either') DEFAULT NULL AFTER tire_size,
  ADD COLUMN tire_count TINYINT DEFAULT NULL AFTER tire_preference;
