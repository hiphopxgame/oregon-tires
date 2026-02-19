-- Oregon Tires — Seed: Gallery Images + Service Images
-- Run AFTER schema.sql has been imported

SET NAMES utf8mb4;

-- ─── Gallery Images ─────────────────────────────────────────────────────────
INSERT INTO oretir_gallery_images (image_url, title, description, is_active, display_order) VALUES
  ('/uploads/gallery/services-promo.jpg', 'Our Services', '10% off with Google review — Tires, Oil Change, Brakes & More', 1, 1),
  ('/uploads/gallery/insta-credit-promo.jpg', 'Insta Credit Available', 'Financing with valid bank account — New & Used Tires', 1, 2),
  ('/uploads/gallery/roadside-service.jpg', 'Roadside Service 24/7', 'Towing and roadside assistance — We come to you!', 1, 3),
  ('/uploads/gallery/payment-plan-promo.jpg', 'Payment Plans', 'Install today, pay later — Easy financing, no credit needed', 1, 4)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ─── Service Images (hero + 7 feature cards) ───────────────────────────────
INSERT INTO oretir_service_images (service_key, image_url, position_x, position_y, scale, is_current) VALUES
  ('hero-background', '/assets/hero-bg.png', 50, 50, 1.00, 1),
  ('expert-technicians', '/images/expert-technicians.jpg', 50, 50, 1.00, 1),
  ('fast-cars', '/images/fast-cars.jpg', 50, 50, 1.00, 1),
  ('quality-car-parts', '/images/quality-parts.jpg', 50, 50, 1.00, 1),
  ('bilingual-support', '/images/bilingual-service.jpg', 50, 50, 1.00, 1),
  ('tire-shop', '/images/tire-services.jpg', 50, 50, 1.00, 1),
  ('auto-repair', '/images/auto-maintenance.jpg', 50, 50, 1.00, 1),
  ('specialized-tools', '/images/specialized-services.jpg', 50, 50, 1.00, 1)
ON DUPLICATE KEY UPDATE image_url = VALUES(image_url);
