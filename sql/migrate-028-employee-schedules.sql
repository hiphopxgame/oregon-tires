-- Migration 028: Employee Work Schedules + Schedule Overrides
-- Creates tables for dynamic booking capacity based on employee schedules

-- Recurring weekly schedule per employee
CREATE TABLE IF NOT EXISTS `oretir_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT NOT NULL,
  `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=Sun, 1=Mon ... 6=Sat',
  `start_time` TIME NOT NULL DEFAULT '08:00:00',
  `end_time` TIME NOT NULL DEFAULT '17:00:00',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employee_day` (`employee_id`, `day_of_week`),
  CONSTRAINT `fk_schedule_employee` FOREIGN KEY (`employee_id`) REFERENCES `oretir_employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Date-specific overrides (closures, special hours)
-- employee_id NULL = shop-wide override
CREATE TABLE IF NOT EXISTS `oretir_schedule_overrides` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` INT DEFAULT NULL,
  `override_date` DATE NOT NULL,
  `is_closed` TINYINT(1) NOT NULL DEFAULT 0,
  `start_time` TIME DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_override_employee_date` (`employee_id`, `override_date`),
  KEY `idx_override_date` (`override_date`),
  CONSTRAINT `fk_override_employee` FOREIGN KEY (`employee_id`) REFERENCES `oretir_employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default schedules for all active employees
-- Mon-Sat 08:00-17:00 available, Sunday off
INSERT INTO `oretir_schedules` (`employee_id`, `day_of_week`, `start_time`, `end_time`, `is_available`)
SELECT e.id, d.day, '08:00:00', '17:00:00', IF(d.day = 0, 0, 1)
FROM `oretir_employees` e
CROSS JOIN (
  SELECT 0 AS day UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
  UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
) d
WHERE e.is_active = 1
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
