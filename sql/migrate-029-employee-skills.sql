-- Migration 029: Employee Service Skills
-- Links employees to the service types they are certified to perform

CREATE TABLE IF NOT EXISTS oretir_employee_skills (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  employee_id INT UNSIGNED NOT NULL,
  service_type VARCHAR(50) NOT NULL,
  certified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_emp_skill (employee_id, service_type),
  CONSTRAINT fk_skill_employee FOREIGN KEY (employee_id)
    REFERENCES oretir_employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: all 9 service types for all active employees
INSERT INTO oretir_employee_skills (employee_id, service_type)
SELECT e.id, s.svc
FROM oretir_employees e
CROSS JOIN (
  SELECT 'tire-installation' AS svc UNION ALL SELECT 'tire-repair'
  UNION ALL SELECT 'wheel-alignment' UNION ALL SELECT 'oil-change'
  UNION ALL SELECT 'brake-service' UNION ALL SELECT 'tuneup'
  UNION ALL SELECT 'mechanical-inspection' UNION ALL SELECT 'mobile-service'
  UNION ALL SELECT 'other'
) s
WHERE e.is_active = 1
ON DUPLICATE KEY UPDATE certified_at = certified_at;
