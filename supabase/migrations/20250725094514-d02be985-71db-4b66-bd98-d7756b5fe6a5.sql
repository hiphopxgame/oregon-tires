-- Update the service images with proper image URLs that will work in the browser
UPDATE oregon_tires_service_images 
SET image_url = CASE service_key
  WHEN 'expert-technicians' THEN 'https://images.unsplash.com/photo-1581092786450-7a86c41d2987?q=80&w=500&h=400&fit=crop'
  WHEN 'fast-cars' THEN 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=80&w=500&h=400&fit=crop'
  WHEN 'quality-parts' THEN 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?q=80&w=500&h=400&fit=crop'
  WHEN 'bilingual-support' THEN 'https://images.unsplash.com/photo-1559526324-c1f275fbfa32?q=80&w=500&h=400&fit=crop'
  WHEN 'tire-shop' THEN 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?q=80&w=500&h=400&fit=crop'
  WHEN 'auto-repair' THEN 'https://images.unsplash.com/photo-1486312338219-ce68e2c63c3a?q=80&w=500&h=400&fit=crop'
  WHEN 'specialized-tools' THEN 'https://images.unsplash.com/photo-1581092334651-ddf26d9a09d0?q=80&w=500&h=400&fit=crop'
  ELSE image_url
END;