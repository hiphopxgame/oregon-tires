-- Migration 046: Customer Referral Program

CREATE TABLE IF NOT EXISTS oretir_referrals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  referrer_customer_id INT UNSIGNED NOT NULL,
  referral_code VARCHAR(10) NOT NULL UNIQUE,
  referred_customer_id INT UNSIGNED DEFAULT NULL,
  referred_email VARCHAR(255) DEFAULT NULL,
  status ENUM('pending','booked','completed','rewarded','expired') NOT NULL DEFAULT 'pending',
  referrer_points INT UNSIGNED NOT NULL DEFAULT 100,
  referred_points INT UNSIGNED NOT NULL DEFAULT 50,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_referrer (referrer_customer_id),
  INDEX idx_code (referral_code),
  INDEX idx_referred (referred_customer_id),
  INDEX idx_status (status),
  CONSTRAINT fk_ref_referrer FOREIGN KEY (referrer_customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_ref_referred FOREIGN KEY (referred_customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add referral_code to customers
ALTER TABLE oretir_customers ADD COLUMN referral_code VARCHAR(10) UNIQUE DEFAULT NULL AFTER loyalty_balance;

-- Add referral_code to appointments
ALTER TABLE oretir_appointments ADD COLUMN referral_code VARCHAR(10) DEFAULT NULL;
