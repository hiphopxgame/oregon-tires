-- Migration 032: Add tire_size column to appointments
ALTER TABLE oretir_appointments ADD COLUMN tire_size VARCHAR(30) NULL DEFAULT NULL AFTER vehicle_vin;
