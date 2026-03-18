-- Migration 044: Customer Loyalty & Rewards System

-- Loyalty points ledger
CREATE TABLE IF NOT EXISTS oretir_loyalty_points (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  points INT NOT NULL,
  balance_after INT NOT NULL DEFAULT 0,
  type ENUM('earn_visit','earn_referral','earn_review','earn_bonus','redeem','expire','adjust') NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  reference_type VARCHAR(50) DEFAULT NULL,
  reference_id INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_customer (customer_id),
  INDEX idx_type (type),
  INDEX idx_created (created_at),
  CONSTRAINT fk_lp_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add loyalty_balance to customers
ALTER TABLE oretir_customers ADD COLUMN loyalty_balance INT NOT NULL DEFAULT 0 AFTER last_visit_at;

-- Reward catalog
CREATE TABLE IF NOT EXISTS oretir_loyalty_rewards (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_en VARCHAR(100) NOT NULL,
  name_es VARCHAR(100) NOT NULL DEFAULT '',
  description_en VARCHAR(500) NOT NULL DEFAULT '',
  description_es VARCHAR(500) NOT NULL DEFAULT '',
  points_cost INT UNSIGNED NOT NULL,
  reward_type ENUM('discount_pct','discount_flat','free_service','custom') NOT NULL DEFAULT 'discount_flat',
  reward_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default rewards
INSERT INTO oretir_loyalty_rewards (name_en, name_es, description_en, description_es, points_cost, reward_type, reward_value) VALUES
('$10 Off Any Service', '$10 de Descuento en Cualquier Servicio', 'Get $10 off your next service visit.', 'Obtenga $10 de descuento en su próxima visita de servicio.', 200, 'discount_flat', 10.00),
('Free Oil Change', 'Cambio de Aceite Gratis', 'Get a free standard oil change.', 'Obtenga un cambio de aceite estándar gratis.', 500, 'free_service', 0.00),
('Free Tire Rotation', 'Rotación de Llantas Gratis', 'Get a free tire rotation service.', 'Obtenga un servicio de rotación de llantas gratis.', 300, 'free_service', 0.00),
('15% Off Brake Service', '15% de Descuento en Servicio de Frenos', 'Get 15% off your next brake service.', 'Obtenga 15% de descuento en su próximo servicio de frenos.', 400, 'discount_pct', 15.00);

-- Loyalty settings
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('loyalty_points_per_dollar', '1', '1'),
('loyalty_min_points_per_visit', '50', '50'),
('loyalty_enabled', '1', '1')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
