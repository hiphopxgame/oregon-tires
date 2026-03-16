-- Unified Dashboard: role column on members + employee-member linkage
-- Run on server: mysql -u hiphopwo_rld_player -p hiphopwo_oregon_tires < migrate-034-unified-dashboard.sql

-- Add role to members (replaces binary is_admin flag)
ALTER TABLE members
  ADD COLUMN role ENUM('member', 'employee', 'admin') NOT NULL DEFAULT 'member' AFTER is_admin;

-- Backfill: anyone with is_admin = 1 gets admin role
UPDATE members SET role = 'admin' WHERE is_admin = 1;

-- Link employees to member accounts (nullable — employees created before this won't have one)
ALTER TABLE oretir_employees
  ADD COLUMN member_id INT UNSIGNED DEFAULT NULL AFTER id,
  ADD CONSTRAINT fk_employee_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL;
