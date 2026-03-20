-- Migration 059: Add sms_reminder_sent column to appointments
-- Required by cli/send-reminders.php for SMS reminder tracking

ALTER TABLE oretir_appointments
    ADD COLUMN sms_reminder_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER reminder_sent;
