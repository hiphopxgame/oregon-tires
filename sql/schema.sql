-- Oregon Tires Auto Care — MySQL Schema
-- Run this in cPanel → phpMyAdmin or MySQL CLI
-- Database: hiphopwo_oregon_tires

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ─── Admin Users ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100) DEFAULT '',
  role ENUM('admin', 'superadmin') NOT NULL DEFAULT 'admin',
  language ENUM('en', 'es', 'both') NOT NULL DEFAULT 'both',
  notification_email VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME DEFAULT NULL,
  password_reset_token VARCHAR(64) DEFAULT NULL,
  password_reset_expires DATETIME DEFAULT NULL,
  setup_completed_at TIMESTAMP NULL DEFAULT NULL,
  last_login_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Employees ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_employees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  role ENUM('Employee', 'Manager') NOT NULL DEFAULT 'Employee',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Appointments ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_appointments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service VARCHAR(50) NOT NULL,
  preferred_date DATE NOT NULL,
  preferred_time VARCHAR(10) NOT NULL,
  vehicle_year VARCHAR(4) DEFAULT NULL,
  vehicle_make VARCHAR(50) DEFAULT NULL,
  vehicle_model VARCHAR(50) DEFAULT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(255) NOT NULL,
  notes TEXT DEFAULT NULL,
  status ENUM('new', 'pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
  language ENUM('english', 'spanish') NOT NULL DEFAULT 'english',
  assigned_employee_id INT UNSIGNED DEFAULT NULL,
  admin_notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_date (preferred_date),
  INDEX idx_employee (assigned_employee_id),
  CONSTRAINT fk_appt_employee FOREIGN KEY (assigned_employee_id)
    REFERENCES oretir_employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Contact Messages ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  message TEXT NOT NULL,
  status ENUM('new', 'priority', 'completed') NOT NULL DEFAULT 'new',
  language ENUM('english', 'spanish') NOT NULL DEFAULT 'english',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Gallery Images ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_gallery_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  image_url VARCHAR(500) NOT NULL,
  title VARCHAR(200) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active_order (is_active, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Service Images (hero + 7 feature cards) ────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_service_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_key VARCHAR(50) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  position_x INT NOT NULL DEFAULT 50,
  position_y INT NOT NULL DEFAULT 50,
  scale DECIMAL(4,2) NOT NULL DEFAULT 1.00,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_current (service_key, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Site Settings (editable content) ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_site_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(50) NOT NULL UNIQUE,
  value_en TEXT NOT NULL DEFAULT (''),
  value_es TEXT NOT NULL DEFAULT (''),
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Email / Change Logs ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_email_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  log_type VARCHAR(50) NOT NULL,
  description TEXT DEFAULT NULL,
  admin_email VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_type (log_type),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Rate Limiting ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS oretir_rate_limits (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  action VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_action (ip_address, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Seed: Default Site Settings ─────────────────────────────────────────────
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  ('phone', '(503) 367-9714', '(503) 367-9714'),
  ('email', 'oregontirespdx@gmail.com', 'oregontirespdx@gmail.com'),
  ('address', '8536 SE 82nd Ave, Portland, OR 97266', '8536 SE 82nd Ave, Portland, OR 97266'),
  ('hours_weekday', 'Mon-Sat 7AM-7PM', 'Lun-Sab 7AM-7PM'),
  ('hours_sunday', 'Sunday: Closed', 'Domingo: Cerrado'),
  ('rating_value', '4.8', '4.8'),
  ('review_count', '150+', '150+')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ─── Seed: Email Templates (bilingual, editable from admin panel) ──────────
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
  -- Welcome / Invite Email
  ('email_tpl_welcome_subject',  'Set Up Your Password — Oregon Tires Admin', 'Configura tu Contraseña — Oregon Tires Admin'),
  ('email_tpl_welcome_greeting', 'Welcome, {{name}}!', '¡Bienvenido/a, {{name}}!'),
  ('email_tpl_welcome_body',     'You''ve been invited to the <strong style="color:#15803d;">Oregon Tires Auto Care Admin Panel</strong> as <strong>{{role}}</strong>. To activate your account, set up your password by clicking the button below.', 'Has sido invitado/a al <strong style="color:#15803d;">Panel de Administración de Oregon Tires Auto Care</strong> como <strong>{{role}}</strong>. Para activar tu cuenta, configura tu contraseña haciendo clic en el botón de abajo.'),
  ('email_tpl_welcome_button',   'Set Up My Password', 'Configurar Mi Contraseña'),
  ('email_tpl_welcome_footer',   'This link expires in <strong>{{expiry_days}} days</strong>. If you didn''t request this account, you can safely ignore this email.', 'Este enlace expira en <strong>{{expiry_days}} días</strong>. Si no solicitaste esta cuenta, puedes ignorar este correo de forma segura.'),
  -- Password Reset Email
  ('email_tpl_reset_subject',    'Reset Your Password — Oregon Tires Admin', 'Restablece tu Contraseña — Oregon Tires Admin'),
  ('email_tpl_reset_greeting',   'Hello, {{name}}', 'Hola, {{name}}'),
  ('email_tpl_reset_body',       'We received a request to reset your password for the <strong style="color:#15803d;">Oregon Tires Auto Care Admin Panel</strong>. Click the button below to choose a new password.', 'Recibimos una solicitud para restablecer tu contraseña del <strong style="color:#15803d;">Panel de Administración de Oregon Tires Auto Care</strong>. Haz clic en el botón de abajo para elegir una nueva contraseña.'),
  ('email_tpl_reset_button',     'Reset My Password', 'Restablecer Mi Contraseña'),
  ('email_tpl_reset_footer',     'This link expires in <strong>1 hour</strong>. If you didn''t request this, you can safely ignore this email.', 'Este enlace expira en <strong>1 hora</strong>. Si no solicitaste esto, puedes ignorar este correo de forma segura.'),
  -- Contact Notification Email
  ('email_tpl_contact_subject',  'New Contact Message from {{name}}', 'Nuevo Mensaje de Contacto de {{name}}'),
  ('email_tpl_contact_greeting', 'New message received', 'Nuevo mensaje recibido'),
  ('email_tpl_contact_body',     'You have a new contact message from <strong>{{name}}</strong> ({{email}}):<br><br><em>"{{message}}"</em>', 'Tienes un nuevo mensaje de contacto de <strong>{{name}}</strong> ({{email}}):<br><br><em>"{{message}}"</em>'),
  ('email_tpl_contact_button',   'View in Admin Panel', 'Ver en el Panel'),
  ('email_tpl_contact_footer',   'This is an automated notification from Oregon Tires Auto Care.', 'Esta es una notificación automática de Oregon Tires Auto Care.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ─── Seed: Super Admin Account ───────────────────────────────────────────────
-- Password: Will be set via setup script (uses password_hash with BCRYPT cost 12)
-- Placeholder hash for 'ChangeMe123!' — MUST be changed on first login
INSERT INTO oretir_admins (email, password_hash, display_name, role) VALUES
  ('tyronenorris@gmail.com', '$2y$12$placeholder.needs.to.be.set.via.setup.script.000000000000', 'Tyrone Norris', 'superadmin')
ON DUPLICATE KEY UPDATE email = email;
