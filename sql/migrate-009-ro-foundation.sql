-- Migration 009: RO Foundation — Customers, Vehicles, VIN Cache, Tire Fitment, Repair Orders, Inspections, Estimates
-- Run in cPanel phpMyAdmin or MySQL CLI
-- Database: hiphopwo_oregon_tires

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ─── Customers ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_customers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  language ENUM('english', 'spanish') NOT NULL DEFAULT 'english',
  notes TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_email (email),
  INDEX idx_phone (phone),
  INDEX idx_name (last_name, first_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Vehicles ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_vehicles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  vin VARCHAR(17) DEFAULT NULL,
  year VARCHAR(4) DEFAULT NULL,
  make VARCHAR(50) DEFAULT NULL,
  model VARCHAR(50) DEFAULT NULL,
  trim_level VARCHAR(100) DEFAULT NULL,
  engine VARCHAR(100) DEFAULT NULL,
  transmission VARCHAR(50) DEFAULT NULL,
  drive_type VARCHAR(50) DEFAULT NULL,
  body_class VARCHAR(50) DEFAULT NULL,
  doors VARCHAR(10) DEFAULT NULL,
  tire_size_front VARCHAR(30) DEFAULT NULL,
  tire_size_rear VARCHAR(30) DEFAULT NULL,
  tire_pressure_front SMALLINT UNSIGNED DEFAULT NULL,
  tire_pressure_rear SMALLINT UNSIGNED DEFAULT NULL,
  mileage INT UNSIGNED DEFAULT NULL,
  license_plate VARCHAR(20) DEFAULT NULL,
  color VARCHAR(30) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_vin (vin),
  INDEX idx_plate (license_plate),
  CONSTRAINT fk_vehicle_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── VIN Decode Cache ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_vin_cache (
  vin VARCHAR(17) NOT NULL PRIMARY KEY,
  raw_json MEDIUMTEXT NOT NULL,
  year VARCHAR(4) DEFAULT NULL,
  make VARCHAR(50) DEFAULT NULL,
  model VARCHAR(50) DEFAULT NULL,
  trim_level VARCHAR(100) DEFAULT NULL,
  engine VARCHAR(200) DEFAULT NULL,
  transmission VARCHAR(50) DEFAULT NULL,
  drive_type VARCHAR(50) DEFAULT NULL,
  body_class VARCHAR(50) DEFAULT NULL,
  doors VARCHAR(10) DEFAULT NULL,
  fuel_type VARCHAR(50) DEFAULT NULL,
  plant_country VARCHAR(50) DEFAULT NULL,
  vehicle_type VARCHAR(50) DEFAULT NULL,
  cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Tire Fitment Cache ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_tire_fitment_cache (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lookup_key VARCHAR(200) NOT NULL,
  raw_json MEDIUMTEXT DEFAULT NULL,
  tire_sizes TEXT DEFAULT NULL,
  rim_diameter VARCHAR(10) DEFAULT NULL,
  bolt_pattern VARCHAR(20) DEFAULT NULL,
  cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_lookup (lookup_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Repair Orders ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_repair_orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ro_number VARCHAR(12) NOT NULL,
  customer_id INT UNSIGNED NOT NULL,
  vehicle_id INT UNSIGNED DEFAULT NULL,
  appointment_id INT UNSIGNED DEFAULT NULL,
  assigned_employee_id INT UNSIGNED DEFAULT NULL,
  status ENUM('intake','diagnosis','estimate_pending','pending_approval','approved','in_progress','waiting_parts','ready','completed','invoiced','cancelled') NOT NULL DEFAULT 'intake',
  mileage_in INT UNSIGNED DEFAULT NULL,
  mileage_out INT UNSIGNED DEFAULT NULL,
  customer_concern TEXT DEFAULT NULL,
  technician_notes TEXT DEFAULT NULL,
  admin_notes TEXT DEFAULT NULL,
  promised_date DATE DEFAULT NULL,
  promised_time VARCHAR(10) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_ro_number (ro_number),
  INDEX idx_customer (customer_id),
  INDEX idx_vehicle (vehicle_id),
  INDEX idx_appointment (appointment_id),
  INDEX idx_status (status),
  INDEX idx_employee (assigned_employee_id),
  CONSTRAINT fk_ro_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE RESTRICT,
  CONSTRAINT fk_ro_vehicle FOREIGN KEY (vehicle_id) REFERENCES oretir_vehicles(id) ON DELETE SET NULL,
  CONSTRAINT fk_ro_appointment FOREIGN KEY (appointment_id) REFERENCES oretir_appointments(id) ON DELETE SET NULL,
  CONSTRAINT fk_ro_employee FOREIGN KEY (assigned_employee_id) REFERENCES oretir_employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inspections ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_inspections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repair_order_id INT UNSIGNED NOT NULL,
  inspector_employee_id INT UNSIGNED DEFAULT NULL,
  status ENUM('draft','in_progress','completed','sent') NOT NULL DEFAULT 'draft',
  overall_condition ENUM('green','yellow','red') DEFAULT NULL,
  customer_view_token VARCHAR(64) DEFAULT NULL,
  customer_viewed_at TIMESTAMP NULL DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ro (repair_order_id),
  UNIQUE INDEX idx_token (customer_view_token),
  CONSTRAINT fk_insp_ro FOREIGN KEY (repair_order_id) REFERENCES oretir_repair_orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_insp_employee FOREIGN KEY (inspector_employee_id) REFERENCES oretir_employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inspection Items ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_inspection_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_id INT UNSIGNED NOT NULL,
  category ENUM('tires','brakes','suspension','fluids','lights','engine','exhaust','hoses','belts','battery','wipers','other') NOT NULL,
  label VARCHAR(200) NOT NULL,
  position VARCHAR(20) DEFAULT NULL,
  condition_rating ENUM('green','yellow','red') NOT NULL DEFAULT 'green',
  measurement VARCHAR(50) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_inspection (inspection_id),
  INDEX idx_category (category),
  CONSTRAINT fk_item_inspection FOREIGN KEY (inspection_id) REFERENCES oretir_inspections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Inspection Photos ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_inspection_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inspection_item_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  caption VARCHAR(200) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_item (inspection_item_id),
  CONSTRAINT fk_photo_item FOREIGN KEY (inspection_item_id) REFERENCES oretir_inspection_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Estimates ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_estimates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repair_order_id INT UNSIGNED NOT NULL,
  estimate_number VARCHAR(12) NOT NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('draft','sent','viewed','approved','partial','declined','expired','superseded') NOT NULL DEFAULT 'draft',
  approval_token VARCHAR(64) DEFAULT NULL,
  customer_viewed_at TIMESTAMP NULL DEFAULT NULL,
  customer_responded_at TIMESTAMP NULL DEFAULT NULL,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  tax_rate DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes TEXT DEFAULT NULL,
  valid_until DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_estimate_number (estimate_number),
  UNIQUE INDEX idx_approval_token (approval_token),
  INDEX idx_ro (repair_order_id),
  INDEX idx_status (status),
  CONSTRAINT fk_est_ro FOREIGN KEY (repair_order_id) REFERENCES oretir_repair_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Estimate Items ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_estimate_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estimate_id INT UNSIGNED NOT NULL,
  inspection_item_id INT UNSIGNED DEFAULT NULL,
  item_type ENUM('labor','parts','tire','fee','discount','sublet') NOT NULL DEFAULT 'labor',
  description VARCHAR(500) NOT NULL,
  quantity DECIMAL(8,2) NOT NULL DEFAULT 1.00,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_approved TINYINT(1) DEFAULT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_estimate (estimate_id),
  CONSTRAINT fk_ei_estimate FOREIGN KEY (estimate_id) REFERENCES oretir_estimates(id) ON DELETE CASCADE,
  CONSTRAINT fk_ei_inspection_item FOREIGN KEY (inspection_item_id) REFERENCES oretir_inspection_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Modify Appointments: Add customer_id, vehicle_id, vehicle_vin ──────
ALTER TABLE oretir_appointments
  ADD COLUMN vehicle_vin VARCHAR(17) DEFAULT NULL AFTER vehicle_model,
  ADD COLUMN customer_id INT UNSIGNED DEFAULT NULL AFTER admin_notes,
  ADD COLUMN vehicle_id INT UNSIGNED DEFAULT NULL AFTER customer_id;

ALTER TABLE oretir_appointments
  ADD INDEX idx_customer (customer_id),
  ADD INDEX idx_vehicle (vehicle_id);

-- Note: FKs will be added after cli/migrate-customers-vehicles.php populates data
-- ALTER TABLE oretir_appointments ADD CONSTRAINT fk_appt_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL;
-- ALTER TABLE oretir_appointments ADD CONSTRAINT fk_appt_vehicle FOREIGN KEY (vehicle_id) REFERENCES oretir_vehicles(id) ON DELETE SET NULL;
