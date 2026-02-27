-- migrate-018-care-plans.sql
-- Care Plan enrollment with PayPal subscription tracking

CREATE TABLE IF NOT EXISTS oretir_care_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED DEFAULT NULL,
    member_id INT UNSIGNED DEFAULT NULL,
    plan_type ENUM('basic', 'standard', 'premium') NOT NULL,
    status ENUM('pending', 'active', 'paused', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    paypal_subscription_id VARCHAR(100) DEFAULT NULL,
    paypal_plan_id VARCHAR(100) DEFAULT NULL,
    monthly_price DECIMAL(8,2) NOT NULL,
    period_start DATE DEFAULT NULL,
    period_end DATE DEFAULT NULL,
    customer_name VARCHAR(200) DEFAULT NULL,
    customer_email VARCHAR(254) DEFAULT NULL,
    customer_phone VARCHAR(30) DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_member (member_id),
    INDEX idx_status (status),
    INDEX idx_paypal_sub (paypal_subscription_id),
    UNIQUE KEY uk_paypal_sub (paypal_subscription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
