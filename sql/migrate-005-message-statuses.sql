-- Migration 005: Align message statuses with admin UI
-- Old: new, read, replied → New: new, priority, completed
-- Run: mysql -u USER -p DB_NAME < migrate-005-message-statuses.sql

-- Step 1: Map existing values to new values
UPDATE oretir_contact_messages SET status = 'new' WHERE status = 'read';
UPDATE oretir_contact_messages SET status = 'new' WHERE status = 'replied';

-- Step 2: Change the ENUM to new values
ALTER TABLE oretir_contact_messages
  MODIFY COLUMN status ENUM('new', 'priority', 'completed') NOT NULL DEFAULT 'new';
