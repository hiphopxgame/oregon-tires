-- Migration 030: Add task_summary column to appointments
-- Run: mysql -u USER -p DB < sql/migrate-030-task-summary.sql

ALTER TABLE oretir_appointments
  ADD COLUMN task_summary VARCHAR(500) DEFAULT NULL AFTER admin_notes;
