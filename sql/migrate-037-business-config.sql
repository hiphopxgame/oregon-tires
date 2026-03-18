-- Oregon Tires — Migration 037: Business Hours + Holidays
-- Configurable shop hours per day of week + holiday calendar

CREATE TABLE IF NOT EXISTS oretir_business_hours (
  id INT AUTO_INCREMENT PRIMARY KEY,
  day_of_week TINYINT NOT NULL COMMENT '0=Sun, 1=Mon, ..., 6=Sat',
  open_time TIME NOT NULL DEFAULT '07:00:00',
  close_time TIME NOT NULL DEFAULT '18:00:00',
  is_open TINYINT(1) NOT NULL DEFAULT 1,
  max_concurrent INT NOT NULL DEFAULT 2,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_day (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default hours: Mon-Sat 7AM-6PM open, Sun closed
INSERT INTO oretir_business_hours (day_of_week, open_time, close_time, is_open, max_concurrent) VALUES
  (0, '07:00:00', '18:00:00', 0, 2),
  (1, '07:00:00', '18:00:00', 1, 2),
  (2, '07:00:00', '18:00:00', 1, 2),
  (3, '07:00:00', '18:00:00', 1, 2),
  (4, '07:00:00', '18:00:00', 1, 2),
  (5, '07:00:00', '18:00:00', 1, 2),
  (6, '07:00:00', '18:00:00', 1, 2)
ON DUPLICATE KEY UPDATE day_of_week = VALUES(day_of_week);

CREATE TABLE IF NOT EXISTS oretir_holidays (
  id INT AUTO_INCREMENT PRIMARY KEY,
  holiday_date DATE NOT NULL,
  name_en VARCHAR(100) NOT NULL,
  name_es VARCHAR(100) DEFAULT NULL,
  is_recurring TINYINT(1) DEFAULT 0 COMMENT 'If 1, recurs annually (year ignored)',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_date (holiday_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed US holidays (using 2026 dates, is_recurring=1 for fixed-date holidays)
INSERT INTO oretir_holidays (holiday_date, name_en, name_es, is_recurring) VALUES
  ('2026-01-01', 'New Year''s Day', 'Ano Nuevo', 1),
  ('2026-05-25', 'Memorial Day', 'Dia de los Caidos', 0),
  ('2026-07-04', 'Independence Day', 'Dia de la Independencia', 1),
  ('2026-09-07', 'Labor Day', 'Dia del Trabajo', 0),
  ('2026-11-26', 'Thanksgiving', 'Dia de Accion de Gracias', 0),
  ('2026-12-25', 'Christmas Day', 'Navidad', 1)
ON DUPLICATE KEY UPDATE name_en = VALUES(name_en);
