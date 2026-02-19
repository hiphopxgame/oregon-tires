-- Migration 004: Add reminder_sent column to appointments table
-- Run: mysql -u USER -p DB_NAME < migrate-004-reminder-sent.sql

ALTER TABLE oretir_appointments
  ADD COLUMN reminder_sent TINYINT(1) NOT NULL DEFAULT 0
  AFTER status;

-- Index for efficient reminder queries
CREATE INDEX idx_appointments_reminder
  ON oretir_appointments (preferred_date, status, reminder_sent);
