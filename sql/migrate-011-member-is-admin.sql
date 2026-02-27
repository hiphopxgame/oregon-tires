-- Add is_admin column to members table for super admin detection
-- Run on server: mysql -u hiphopwo_rld_player -p hiphopwo_oregon_tires < migrate-011-member-is-admin.sql

ALTER TABLE members ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;
