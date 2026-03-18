-- Migration 045: Technician Labor Time Tracking

CREATE TABLE IF NOT EXISTS oretir_labor_entries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repair_order_id INT UNSIGNED NOT NULL,
  employee_id INT UNSIGNED NOT NULL,
  clock_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  clock_out_at DATETIME DEFAULT NULL,
  duration_minutes INT UNSIGNED GENERATED ALWAYS AS (
    CASE WHEN clock_out_at IS NOT NULL
      THEN TIMESTAMPDIFF(MINUTE, clock_in_at, clock_out_at)
      ELSE NULL
    END
  ) STORED,
  is_billable TINYINT(1) NOT NULL DEFAULT 1,
  task_description VARCHAR(500) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ro (repair_order_id),
  INDEX idx_employee (employee_id),
  INDEX idx_clock_in (clock_in_at),
  CONSTRAINT fk_labor_ro FOREIGN KEY (repair_order_id) REFERENCES oretir_repair_orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_labor_employee FOREIGN KEY (employee_id) REFERENCES oretir_employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
