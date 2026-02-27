-- Migration 014: Add UTM tracking fields to appointments
-- Captures campaign attribution data from booking form

ALTER TABLE oretir_appointments
  ADD COLUMN utm_source VARCHAR(100) DEFAULT NULL,
  ADD COLUMN utm_medium VARCHAR(100) DEFAULT NULL,
  ADD COLUMN utm_campaign VARCHAR(100) DEFAULT NULL,
  ADD COLUMN utm_content VARCHAR(100) DEFAULT NULL;
