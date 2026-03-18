-- Migration 042: Digital Invoices & Receipts

CREATE TABLE IF NOT EXISTS oretir_invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repair_order_id INT UNSIGNED NOT NULL,
  invoice_number VARCHAR(20) NOT NULL UNIQUE,
  estimate_id INT UNSIGNED DEFAULT NULL,
  customer_id INT UNSIGNED NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  tax_rate DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('draft','sent','viewed','paid','overdue','void') NOT NULL DEFAULT 'draft',
  payment_method ENUM('cash','card','check','paypal','other') DEFAULT NULL,
  payment_reference VARCHAR(100) DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  due_date DATE DEFAULT NULL,
  customer_view_token VARCHAR(64) NOT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ro (repair_order_id),
  INDEX idx_customer (customer_id),
  INDEX idx_status (status),
  INDEX idx_token (customer_view_token),
  CONSTRAINT fk_inv_ro FOREIGN KEY (repair_order_id) REFERENCES oretir_repair_orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_estimate FOREIGN KEY (estimate_id) REFERENCES oretir_estimates(id) ON DELETE SET NULL,
  CONSTRAINT fk_inv_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS oretir_invoice_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  estimate_item_id INT UNSIGNED DEFAULT NULL,
  item_type ENUM('labor','parts','tire','fee','discount','sublet') NOT NULL DEFAULT 'labor',
  description VARCHAR(500) NOT NULL,
  quantity DECIMAL(8,2) NOT NULL DEFAULT 1.00,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  sort_order INT NOT NULL DEFAULT 0,
  INDEX idx_invoice (invoice_id),
  CONSTRAINT fk_invitem_invoice FOREIGN KEY (invoice_id) REFERENCES oretir_invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email templates for invoice
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_invoice_subject', 'Invoice {{invoice_number}} for your service', 'Factura {{invoice_number}} por su servicio'),
('email_tpl_invoice_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_invoice_body', 'Your invoice <strong>{{invoice_number}}</strong> for your <strong>{{vehicle}}</strong> (RO: {{ro_number}}) is ready.<br><br>Total: <strong>{{total}}</strong><br><br>Click below to view your detailed invoice.', 'Su factura <strong>{{invoice_number}}</strong> para su <strong>{{vehicle}}</strong> (OT: {{ro_number}}) está lista.<br><br>Total: <strong>{{total}}</strong><br><br>Haga clic abajo para ver su factura detallada.'),
('email_tpl_invoice_button', 'View Invoice', 'Ver Factura'),
('email_tpl_invoice_footer', 'Thank you for choosing Oregon Tires Auto Care!', '¡Gracias por elegir Oregon Tires Auto Care!')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
