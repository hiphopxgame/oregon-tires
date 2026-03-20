-- Migration 056: Add 'on_hold' status to repair orders
-- Adds On Hold as a new RO status between in_progress and waiting_parts

ALTER TABLE oretir_repair_orders
  MODIFY COLUMN status ENUM(
    'intake','diagnosis','estimate_pending','pending_approval',
    'approved','in_progress','on_hold','waiting_parts',
    'ready','completed','invoiced','cancelled'
  ) NOT NULL DEFAULT 'intake';
