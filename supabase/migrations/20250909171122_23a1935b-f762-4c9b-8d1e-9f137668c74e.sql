-- Fix service image URLs to use public paths
UPDATE oretir_service_images 
SET image_url = CASE 
  WHEN service_key = 'expert-technicians' THEN '/images/expert-technicians.jpg'
  WHEN service_key = 'fast-cars' THEN '/images/fast-cars.jpg'
  WHEN service_key = 'quality-car-parts' THEN '/images/quality-parts.jpg'
  WHEN service_key = 'bilingual-support' THEN '/images/bilingual-service.jpg'
  WHEN service_key = 'tire-shop' THEN '/images/tire-services.jpg'
  WHEN service_key = 'auto-repair' THEN '/images/auto-maintenance.jpg'
  WHEN service_key = 'specialized-tools' THEN '/images/specialized-services.jpg'
  ELSE image_url
END
WHERE is_current = true 
AND service_key IN ('expert-technicians', 'fast-cars', 'quality-car-parts', 'bilingual-support', 'tire-shop', 'auto-repair', 'specialized-tools');