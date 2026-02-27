-- Oregon Tires — Email Subscribers Table
-- Phase 1: Email capture for newsletter and deals

CREATE TABLE IF NOT EXISTS oretir_subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  language ENUM('en','es') DEFAULT 'en',
  source VARCHAR(50) DEFAULT 'website',
  subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  unsubscribed_at TIMESTAMP NULL,
  INDEX idx_subscribed (unsubscribed_at, subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
