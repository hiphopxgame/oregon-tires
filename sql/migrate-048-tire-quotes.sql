-- Migration 048: Tire Quote Requests

CREATE TABLE IF NOT EXISTS oretir_tire_quotes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED DEFAULT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  vehicle_year VARCHAR(4) DEFAULT NULL,
  vehicle_make VARCHAR(50) DEFAULT NULL,
  vehicle_model VARCHAR(50) DEFAULT NULL,
  tire_size VARCHAR(50) DEFAULT NULL,
  tire_count INT UNSIGNED NOT NULL DEFAULT 4,
  tire_preference ENUM('new','used','either') NOT NULL DEFAULT 'either',
  budget_range ENUM('economy','mid','premium','no_preference') NOT NULL DEFAULT 'no_preference',
  include_installation TINYINT(1) NOT NULL DEFAULT 1,
  preferred_date DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  status ENUM('new','quoted','accepted','ordered','installed','cancelled') NOT NULL DEFAULT 'new',
  admin_notes TEXT DEFAULT NULL,
  quote_amount DECIMAL(10,2) DEFAULT NULL,
  language VARCHAR(10) NOT NULL DEFAULT 'english',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_email (email),
  INDEX idx_customer (customer_id),
  CONSTRAINT fk_tq_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email templates
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_tire_quote_subject', 'Your Tire Quote Request Has Been Received', 'Su Solicitud de Cotización de Llantas Ha Sido Recibida'),
('email_tpl_tire_quote_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_tire_quote_body', 'Thank you for your tire quote request! We have received your request for <strong>{{tire_count}} tires ({{tire_size}})</strong> for your <strong>{{vehicle}}</strong>.<br><br>Our team will review your request and contact you within 24 hours with pricing options.<br><br>If you need immediate assistance, please call us.', 'Gracias por su solicitud de cotización de llantas. Hemos recibido su solicitud de <strong>{{tire_count}} llantas ({{tire_size}})</strong> para su <strong>{{vehicle}}</strong>.<br><br>Nuestro equipo revisará su solicitud y le contactará dentro de 24 horas con opciones de precios.<br><br>Si necesita asistencia inmediata, por favor llámenos.'),
('email_tpl_tire_quote_button', 'Visit Our Website', 'Visite Nuestro Sitio'),
('email_tpl_tire_quote_footer', 'Oregon Tires Auto Care — Quality tires at competitive prices.', 'Oregon Tires Auto Care — Llantas de calidad a precios competitivos.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
