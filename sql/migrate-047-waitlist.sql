-- Migration 047: Waitlist & Walk-In Queue

CREATE TABLE IF NOT EXISTS oretir_waitlist (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED DEFAULT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL DEFAULT '',
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  service VARCHAR(100) NOT NULL DEFAULT '',
  vehicle_info VARCHAR(200) DEFAULT NULL,
  position INT UNSIGNED NOT NULL DEFAULT 0,
  estimated_wait_minutes INT UNSIGNED DEFAULT NULL,
  status ENUM('waiting','notified','checked_in','serving','completed','cancelled','expired') NOT NULL DEFAULT 'waiting',
  notified_at DATETIME DEFAULT NULL,
  checked_in_at DATETIME DEFAULT NULL,
  language VARCHAR(10) NOT NULL DEFAULT 'english',
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_position (position),
  INDEX idx_customer (customer_id),
  CONSTRAINT fk_wl_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email templates for waitlist
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_waitlist_ready_subject', 'Your turn is coming up!', '¡Su turno se acerca!'),
('email_tpl_waitlist_ready_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_waitlist_ready_body', 'Good news! A bay is opening up at Oregon Tires Auto Care. Please head over soon so we can get started on your <strong>{{service}}</strong> service.<br><br>Estimated wait: <strong>{{wait_time}} minutes</strong>', '¡Buenas noticias! Un espacio se está abriendo en Oregon Tires Auto Care. Por favor venga pronto para que podamos comenzar con su servicio de <strong>{{service}}</strong>.<br><br>Espera estimada: <strong>{{wait_time}} minutos</strong>'),
('email_tpl_waitlist_ready_button', 'Get Directions', 'Obtener Direcciones'),
('email_tpl_waitlist_ready_footer', 'See you soon!', '¡Nos vemos pronto!')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Waitlist settings
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('waitlist_avg_service_minutes', '60', '60'),
('waitlist_max_bays', '4', '4')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
