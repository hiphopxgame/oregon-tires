-- Migration 043: Automated Service Reminders

CREATE TABLE IF NOT EXISTS oretir_service_reminders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  vehicle_id INT UNSIGNED DEFAULT NULL,
  service_type VARCHAR(50) NOT NULL,
  last_service_date DATE NOT NULL,
  next_due_date DATE NOT NULL,
  mileage_at_service INT UNSIGNED DEFAULT NULL,
  reminder_sent_at DATETIME DEFAULT NULL,
  status ENUM('pending','sent','booked','dismissed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_vehicle (vehicle_id),
  INDEX idx_next_due (next_due_date),
  INDEX idx_status (status),
  CONSTRAINT fk_svcrem_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_svcrem_vehicle FOREIGN KEY (vehicle_id) REFERENCES oretir_vehicles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default service intervals (in days, stored in site_settings)
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('service_interval_oil_change', '180', '180'),
('service_interval_tire_rotation', '180', '180'),
('service_interval_brake_inspection', '365', '365'),
('service_interval_wheel_alignment', '365', '365'),
('service_interval_seasonal_swap', '180', '180')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Email templates for service reminders
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_service_reminder_subject', 'Your {{service_type}} is due soon!', '¡Su {{service_type}} vence pronto!'),
('email_tpl_service_reminder_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_service_reminder_body', 'It''s been a while since your last <strong>{{service_type}}</strong> service on your <strong>{{vehicle}}</strong>.<br><br>Your last service was on <strong>{{last_service_date}}</strong>. We recommend scheduling your next appointment soon to keep your vehicle in top condition.<br><br>Click below to book your appointment.', 'Ha pasado un tiempo desde su último servicio de <strong>{{service_type}}</strong> en su <strong>{{vehicle}}</strong>.<br><br>Su último servicio fue el <strong>{{last_service_date}}</strong>. Le recomendamos programar su próxima cita pronto para mantener su vehículo en óptimas condiciones.<br><br>Haga clic abajo para reservar su cita.'),
('email_tpl_service_reminder_button', 'Book Appointment', 'Reservar Cita'),
('email_tpl_service_reminder_footer', 'Oregon Tires Auto Care — Your trusted partner for all your tire and auto needs.', 'Oregon Tires Auto Care — Su socio de confianza para todas sus necesidades de llantas y autos.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
