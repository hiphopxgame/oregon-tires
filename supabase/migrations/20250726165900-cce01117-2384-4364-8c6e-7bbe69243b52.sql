-- First, clear existing service images
DELETE FROM oretir_service_images;

-- Insert the original 7 service images with proper service keys and static image URLs
INSERT INTO oretir_service_images (service_key, title, image_url, position_x, position_y, scale, is_current) VALUES
('expert-technicians', 'Expert Technicians', '/src/assets/expert-technicians.jpg', 50, 50, 1.0, true),
('fast-cars', 'Quick Service', '/src/assets/fast-cars.jpg', 50, 50, 1.0, true), 
('quality-car-parts', 'Quality Parts', '/src/assets/quality-car-parts.jpg', 50, 50, 1.0, true),
('bilingual-support', 'Bilingual Support', '/src/assets/bilingual-support.jpg', 50, 50, 1.0, true),
('tire-shop', 'Tire Services', '/src/assets/tire-shop.jpg', 50, 50, 1.0, true),
('auto-repair', 'Auto Maintenance', '/src/assets/auto-repair.jpg', 50, 50, 1.0, true),
('specialized-tools', 'Specialized Services', '/src/assets/specialized-tools.jpg', 50, 50, 1.0, true);