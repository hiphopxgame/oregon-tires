-- Migration 060: Employee User Groups & Permissions
-- Creates employee_groups table and links employees to groups

CREATE TABLE IF NOT EXISTS oretir_employee_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(100) NOT NULL,
  name_es VARCHAR(100) NOT NULL DEFAULT '',
  description_en VARCHAR(255) DEFAULT '',
  description_es VARCHAR(255) DEFAULT '',
  permissions JSON NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add group_id to employees
ALTER TABLE oretir_employees
  ADD COLUMN group_id INT UNSIGNED DEFAULT NULL AFTER role,
  ADD CONSTRAINT fk_employee_group FOREIGN KEY (group_id)
    REFERENCES oretir_employee_groups(id) ON DELETE SET NULL;

-- Seed 4 system groups
INSERT INTO oretir_employee_groups (name_en, name_es, description_en, description_es, permissions, is_default, is_system) VALUES
('Mechanic', 'Mecánico', 'Shop floor technicians', 'Técnicos de taller', '["my_work","shop_ops"]', 1, 1),
('Front Desk', 'Recepción', 'Customer-facing staff', 'Personal de atención al cliente', '["my_work","shop_ops","customers","messaging"]', 0, 1),
('Manager', 'Gerente', 'Full access except settings', 'Acceso completo excepto configuración', '["my_work","shop_ops","customers","messaging","team","marketing"]', 0, 1),
('Marketer', 'Marketing', 'Marketing and content management', 'Gestión de marketing y contenido', '["my_work","marketing"]', 0, 1);

-- Migrate existing employees: Manager role → Manager group (id=3), Employee role → Mechanic group (id=1, default)
UPDATE oretir_employees SET group_id = 3 WHERE role = 'Manager';
UPDATE oretir_employees SET group_id = 1 WHERE role = 'Employee' OR group_id IS NULL;
