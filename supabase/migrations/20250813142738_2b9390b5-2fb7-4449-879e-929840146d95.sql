-- Insert initial hero background image if it doesn't exist
INSERT INTO public.oretir_service_images (
    service_key,
    title,
    image_url,
    position_x,
    position_y,
    scale,
    is_current
)
SELECT 
    'hero-background',
    'Hero Background Image',
    '/lovable-uploads/afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png',
    50,
    50,
    1.0,
    true
WHERE NOT EXISTS (
    SELECT 1 FROM public.oretir_service_images 
    WHERE service_key = 'hero-background'
);