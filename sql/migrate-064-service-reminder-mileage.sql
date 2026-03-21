-- Migration 064: Add due_mileage column to service_reminders for mileage-based reminders

ALTER TABLE oretir_service_reminders
  ADD COLUMN due_mileage INT UNSIGNED DEFAULT NULL AFTER next_due_date;
