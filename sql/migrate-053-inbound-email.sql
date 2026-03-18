-- Migration 053: Inbound email integration into admin messaging
-- Adds email Message-ID tracking for threading, source columns on conversations/messages

-- 1. Email Message-ID tracking table for dedup + threading
CREATE TABLE IF NOT EXISTS oretir_email_message_ids (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id_header VARCHAR(512) NOT NULL,
  conversation_id INT UNSIGNED NOT NULL,
  conversation_message_id INT UNSIGNED DEFAULT NULL,
  direction ENUM('inbound','outbound') NOT NULL DEFAULT 'inbound',
  in_reply_to VARCHAR(512) DEFAULT NULL,
  from_email VARCHAR(254) NOT NULL DEFAULT '',
  subject VARCHAR(255) NOT NULL DEFAULT '',
  has_attachments TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_message_id (message_id_header(255)),
  INDEX idx_conversation_id (conversation_id),
  INDEX idx_from_email (from_email),
  FOREIGN KEY (conversation_id) REFERENCES oretir_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add source + attachments to conversation messages
ALTER TABLE oretir_conversation_messages
  ADD COLUMN source ENUM('web','email','system') NOT NULL DEFAULT 'web' AFTER is_read,
  ADD COLUMN attachments_json JSON DEFAULT NULL AFTER source;

-- 3. Add source + email thread ID to conversations
ALTER TABLE oretir_conversations
  ADD COLUMN source ENUM('web','email','contact_form') NOT NULL DEFAULT 'web' AFTER status,
  ADD COLUMN email_thread_id VARCHAR(512) DEFAULT NULL AFTER source,
  ADD INDEX idx_email_thread (email_thread_id(255));

-- 4. Seed email reply template for branded outbound replies
INSERT INTO oretir_site_settings (setting_key, value_en, value_es) VALUES
('email_tpl_email_reply_subject', 'Re: {{subject}}', 'Re: {{subject}}'),
('email_tpl_email_reply_greeting', 'Hi {{name}},', 'Hola {{name}},'),
('email_tpl_email_reply_body', '{{message}}', '{{message}}'),
('email_tpl_email_reply_button', 'View Online', 'Ver en Línea'),
('email_tpl_email_reply_footer', 'This is a reply from Oregon Tires Auto Care. You can respond directly to this email.', 'Esta es una respuesta de Oregon Tires Auto Care. Puede responder directamente a este correo.')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
