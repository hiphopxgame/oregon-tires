-- Migration 033: Add member_id to vehicles for member dashboard lookup
ALTER TABLE oretir_vehicles
  ADD COLUMN member_id INT UNSIGNED DEFAULT NULL,
  ADD INDEX idx_veh_member_id (member_id);
