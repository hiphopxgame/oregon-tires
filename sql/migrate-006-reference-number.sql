-- Migration 006: Add reference_number column to oretir_appointments
-- Run in cPanel phpMyAdmin or MySQL CLI
-- Reference numbers are generated as OT-XXXXXXXX (8 uppercase alphanumeric chars)

ALTER TABLE oretir_appointments
  ADD COLUMN reference_number VARCHAR(12) DEFAULT NULL AFTER id;

ALTER TABLE oretir_appointments
  ADD UNIQUE INDEX idx_reference (reference_number);
