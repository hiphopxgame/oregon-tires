-- Migration 023: Add Google Calendar sync columns to appointments
-- Required by: book.php (lines 370-427), calendar-health.php, calendar-retry-sync.php, admin UI badges

ALTER TABLE oretir_appointments
  ADD COLUMN google_event_id VARCHAR(255) DEFAULT NULL,
  ADD COLUMN calendar_sync_status ENUM('pending','success','failed') DEFAULT NULL,
  ADD COLUMN calendar_synced_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN calendar_sync_error TEXT DEFAULT NULL;
