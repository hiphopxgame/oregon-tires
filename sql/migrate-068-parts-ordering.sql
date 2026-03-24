-- Migration 068: Parts ordering system
-- Vendors, parts catalog, parts orders, and order line items

CREATE TABLE IF NOT EXISTS oretir_vendors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    contact_name VARCHAR(200) DEFAULT NULL,
    email VARCHAR(254) DEFAULT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    website VARCHAR(500) DEFAULT NULL,
    account_number VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_parts_catalog (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    part_number VARCHAR(100) NOT NULL,
    name VARCHAR(300) NOT NULL,
    name_es VARCHAR(300) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    default_price DECIMAL(10,2) DEFAULT NULL,
    cost_price DECIMAL(10,2) DEFAULT NULL,
    vendor_id INT UNSIGNED DEFAULT NULL,
    in_stock TINYINT(1) NOT NULL DEFAULT 0,
    min_stock INT UNSIGNED DEFAULT 0,
    notes TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_part_number (part_number),
    INDEX idx_vendor (vendor_id),
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    CONSTRAINT fk_parts_vendor FOREIGN KEY (vendor_id) REFERENCES oretir_vendors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_parts_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    vendor_id INT UNSIGNED NOT NULL,
    ro_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','ordered','shipped','partial','received','cancelled') NOT NULL DEFAULT 'draft',
    tracking_number VARCHAR(200) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    total DECIMAL(10,2) DEFAULT 0.00,
    ordered_at TIMESTAMP NULL DEFAULT NULL,
    expected_at DATE DEFAULT NULL,
    received_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendor (vendor_id),
    INDEX idx_ro (ro_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number),
    CONSTRAINT fk_po_vendor FOREIGN KEY (vendor_id) REFERENCES oretir_vendors(id) ON DELETE RESTRICT,
    CONSTRAINT fk_po_ro FOREIGN KEY (ro_id) REFERENCES oretir_repair_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_parts_order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    part_id INT UNSIGNED DEFAULT NULL,
    description VARCHAR(500) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    received_qty INT UNSIGNED DEFAULT 0,
    ro_id INT UNSIGNED DEFAULT NULL,
    estimate_item_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id),
    INDEX idx_part (part_id),
    INDEX idx_ro (ro_id),
    CONSTRAINT fk_poi_order FOREIGN KEY (order_id) REFERENCES oretir_parts_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_part FOREIGN KEY (part_id) REFERENCES oretir_parts_catalog(id) ON DELETE SET NULL,
    CONSTRAINT fk_poi_ro FOREIGN KEY (ro_id) REFERENCES oretir_repair_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link estimate items to parts catalog
ALTER TABLE oretir_estimate_items
    ADD COLUMN part_id INT UNSIGNED DEFAULT NULL AFTER item_type;
