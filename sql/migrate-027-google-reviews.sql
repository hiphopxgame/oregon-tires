-- Migration 027: Google Reviews integration
-- Adds source tracking, homepage toggle, and Google review fields to testimonials

ALTER TABLE oretir_testimonials
  ADD COLUMN source ENUM('manual','google') NOT NULL DEFAULT 'manual' AFTER id,
  ADD COLUMN google_review_id VARCHAR(255) NULL AFTER source,
  ADD COLUMN author_photo_url VARCHAR(500) NULL AFTER customer_name,
  ADD COLUMN google_published_at DATETIME NULL AFTER author_photo_url,
  ADD COLUMN show_on_homepage TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active,
  ADD UNIQUE INDEX idx_google_review_id (google_review_id);

-- Feature all existing seeded reviews on homepage
UPDATE oretir_testimonials SET show_on_homepage = 1 WHERE is_active = 1;
