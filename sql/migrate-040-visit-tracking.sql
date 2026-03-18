-- Oregon Tires — Migration 040: Visit Tracking + Employee Capacity
-- Customer visit log for check-in/check-out tracking + per-employee daily capacity

CREATE TABLE IF NOT EXISTS oretir_visit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT DEFAULT NULL,
  repair_order_id INT DEFAULT NULL,
  customer_id INT NOT NULL,
  check_in_at DATETIME DEFAULT NULL,
  service_start_at DATETIME DEFAULT NULL,
  service_end_at DATETIME DEFAULT NULL,
  check_out_at DATETIME DEFAULT NULL,
  bay_number TINYINT DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_appointment (appointment_id),
  INDEX idx_repair_order (repair_order_id),
  INDEX idx_customer (customer_id),
  INDEX idx_checkin_date (check_in_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE oretir_employees
  ADD COLUMN max_daily_appointments TINYINT NOT NULL DEFAULT 8 AFTER is_active;
