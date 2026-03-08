-- Oregon Tires — Seed: Gallery Images + Service Images
-- Run AFTER schema.sql has been imported

SET NAMES utf8mb4;

-- ─── Gallery Images ─────────────────────────────────────────────────────────
INSERT INTO oretir_gallery_images (image_url, title_en, title_es, description_en, description_es, is_active, display_order) VALUES
  ('/uploads/gallery/services-promo.jpg', 'Our Services', 'Nuestros Servicios', '10% off with Google review — Tires, Oil Change, Brakes & More', '10% de descuento con reseña de Google — Llantas, Cambio de Aceite, Frenos y Más', 1, 1),
  ('/uploads/gallery/insta-credit-promo.jpg', 'Insta Credit Available', 'Crédito Instantáneo Disponible', 'Financing with valid bank account — New & Used Tires', 'Financiamiento con cuenta bancaria válida — Llantas Nuevas y Usadas', 1, 2),
  ('/uploads/gallery/roadside-service.jpg', 'Roadside Service 24/7', 'Servicio en Carretera 24/7', 'Towing and roadside assistance — We come to you!', 'Grúa y asistencia en carretera — ¡Vamos a ti!', 1, 3),
  ('/uploads/gallery/payment-plan-promo.jpg', 'Payment Plans', 'Planes de Pago', 'Install today, pay later — Easy financing, no credit needed', 'Instala hoy, paga después — Financiamiento fácil, sin crédito necesario', 1, 4)
ON DUPLICATE KEY UPDATE title_en = VALUES(title_en);

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
