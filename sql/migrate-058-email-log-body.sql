-- Migration 058: Add recipient_email, subject, body to email_logs for full audit trail

ALTER TABLE oretir_email_logs
  ADD COLUMN recipient_email VARCHAR(255) DEFAULT NULL AFTER admin_email,
  ADD COLUMN subject VARCHAR(500) DEFAULT NULL AFTER recipient_email,
  ADD COLUMN body MEDIUMTEXT DEFAULT NULL AFTER subject;

ALTER TABLE oretir_email_logs
  ADD INDEX idx_recipient (recipient_email);
