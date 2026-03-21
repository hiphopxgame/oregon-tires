-- Migration 065: Add service location fields for roadside/mobile services
-- Tracks customer address + distance from shop for on-site services

ALTER TABLE oretir_appointments
  ADD COLUMN service_location VARCHAR(500) DEFAULT NULL AFTER notes,
  ADD COLUMN service_distance_miles DECIMAL(5,1) DEFAULT NULL AFTER service_location;

ALTER TABLE oretir_repair_orders
  ADD COLUMN service_location VARCHAR(500) DEFAULT NULL AFTER customer_concern,
  ADD COLUMN service_distance_miles DECIMAL(5,1) DEFAULT NULL AFTER service_location;
