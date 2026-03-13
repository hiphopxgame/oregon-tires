-- Migration 031: Customer portal messaging & loyalty
-- Adds member_id + loyalty fields to customers, creates conversations tables

-- 1. Add member_id, visit_count, last_visit_at to oretir_customers
ALTER TABLE oretir_customers
  ADD COLUMN member_id INT UNSIGNED DEFAULT NULL AFTER language,
  ADD COLUMN visit_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER member_id,
  ADD COLUMN last_visit_at DATETIME DEFAULT NULL AFTER visit_count,
  ADD INDEX idx_member_id (member_id);

-- 2. Conversations table
CREATE TABLE IF NOT EXISTS oretir_conversations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id INT UNSIGNED NOT NULL,
  appointment_id INT UNSIGNED DEFAULT NULL,
  repair_order_id INT UNSIGNED DEFAULT NULL,
  subject VARCHAR(255) NOT NULL,
  status ENUM('open','waiting_reply','resolved','closed') NOT NULL DEFAULT 'open',
  last_message_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer_id (customer_id),
  INDEX idx_status (status),
  INDEX idx_last_message (last_message_at),
  CONSTRAINT fk_conv_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE CASCADE,
  CONSTRAINT fk_conv_appointment FOREIGN KEY (appointment_id) REFERENCES oretir_appointments(id) ON DELETE SET NULL,
  CONSTRAINT fk_conv_ro FOREIGN KEY (repair_order_id) REFERENCES oretir_repair_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Conversation messages table
CREATE TABLE IF NOT EXISTS oretir_conversation_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED NOT NULL,
  sender_type ENUM('customer','admin','employee','system') NOT NULL,
  sender_id INT UNSIGNED DEFAULT NULL,
  sender_name VARCHAR(100) NOT NULL DEFAULT '',
  body TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_conversation_id (conversation_id),
  INDEX idx_sender_type (sender_type),
  INDEX idx_is_read (is_read),
  CONSTRAINT fk_convmsg_conversation FOREIGN KEY (conversation_id) REFERENCES oretir_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Email template for conversation replies
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_conversation_reply_subject', 'New reply to your message', 'Nueva respuesta a su mensaje'),
('email_tpl_conversation_reply_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_conversation_reply_body', 'You have a new reply regarding: <strong>{{subject}}</strong><br><br>{{preview}}', 'Tiene una nueva respuesta sobre: <strong>{{subject}}</strong><br><br>{{preview}}'),
('email_tpl_conversation_reply_button', 'View Conversation', 'Ver Conversación'),
('email_tpl_conversation_reply_footer', 'Log in to your account to read and reply to messages.', 'Inicie sesión en su cuenta para leer y responder mensajes.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
