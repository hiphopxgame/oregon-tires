-- Commerce Kit — Migration 001
-- Core tables for orders, line items, and transactions.

CREATE TABLE IF NOT EXISTS commerce_orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_ref VARCHAR(32) NOT NULL UNIQUE,
    site_key VARCHAR(64) NOT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('pending','processing','completed','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
    payment_provider VARCHAR(50) DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    customer_name VARCHAR(128) DEFAULT NULL,
    customer_email VARCHAR(255) DEFAULT NULL,
    customer_phone VARCHAR(32) DEFAULT NULL,
    metadata JSON,
    notes TEXT,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_site_status (site_key, status),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_expires (expires_at)
);

CREATE TABLE IF NOT EXISTS commerce_line_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    sku VARCHAR(64) DEFAULT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    metadata JSON,
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
);

CREATE TABLE IF NOT EXISTS commerce_transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    type ENUM('payment','refund','adjustment') NOT NULL DEFAULT 'payment',
    provider VARCHAR(50) NOT NULL,
    provider_transaction_id VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    provider_metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_provider_tx (provider_transaction_id)
);

CREATE TABLE IF NOT EXISTS commerce_webhooks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON,
    processed TINYINT(1) NOT NULL DEFAULT 0,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_provider_event (provider, event_type),
    INDEX idx_processed (processed)
);
