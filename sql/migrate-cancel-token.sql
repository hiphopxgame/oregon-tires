-- Cancel/Reschedule token support for appointments
-- Allows customers to cancel or reschedule via email link

ALTER TABLE oretir_appointments
ADD COLUMN cancel_token VARCHAR(64) DEFAULT NULL AFTER admin_notes,
ADD COLUMN cancel_token_expires DATETIME DEFAULT NULL AFTER cancel_token,
ADD UNIQUE INDEX idx_cancel_token (cancel_token);
