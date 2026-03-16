-- Migration 035: License plate lookup cache
-- Permanent cache for Auto.dev plate → VIN lookups

CREATE TABLE IF NOT EXISTS oretir_plate_cache (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  license_plate VARCHAR(20) NOT NULL,
  state VARCHAR(5) NOT NULL,
  vin VARCHAR(17) DEFAULT NULL,
  raw_json TEXT DEFAULT NULL,
  cached_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_plate_state (license_plate, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
