-- Migration 024: Add decline_reason column to oretir_estimates
-- Stores optional reason when customer declines all estimate items

ALTER TABLE oretir_estimates ADD COLUMN decline_reason VARCHAR(50) DEFAULT NULL AFTER customer_responded_at;
