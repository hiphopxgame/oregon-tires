-- Migration 072: Add Google OAuth support for admin accounts
-- Allows admins to sign in with Google in addition to password

ALTER TABLE oretir_admins
  ADD COLUMN google_id VARCHAR(255) DEFAULT NULL AFTER notification_email,
  ADD UNIQUE INDEX idx_admin_google_id (google_id);
