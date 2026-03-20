-- Migration 055: Multi-Service Booking
-- Adds JSON `services` column alongside existing `service` for backwards compatibility.
-- Backfills existing appointments with a JSON array containing their single service.

ALTER TABLE oretir_appointments ADD COLUMN services JSON DEFAULT NULL AFTER service;

UPDATE oretir_appointments SET services = JSON_ARRAY(service) WHERE services IS NULL AND service IS NOT NULL;
