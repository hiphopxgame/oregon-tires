-- Create table for service images with history
CREATE TABLE public.oregon_tires_service_images (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  service_key TEXT NOT NULL,
  title TEXT NOT NULL,
  image_url TEXT NOT NULL,
  position_x INTEGER NOT NULL DEFAULT 50,
  position_y INTEGER NOT NULL DEFAULT 50,
  scale DECIMAL NOT NULL DEFAULT 1.0,
  is_current BOOLEAN NOT NULL DEFAULT false,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable Row Level Security
ALTER TABLE public.oregon_tires_service_images ENABLE ROW LEVEL SECURITY;

-- Create policies for admin access
CREATE POLICY "Admin can manage service images" 
ON public.oregon_tires_service_images 
FOR ALL 
USING (true);

-- Create policies for public viewing of current images
CREATE POLICY "Public can view current service images" 
ON public.oregon_tires_service_images 
FOR SELECT 
USING (is_current = true);

-- Create function to update timestamps
CREATE OR REPLACE FUNCTION public.update_oregon_tires_service_images_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = 'public';

-- Create trigger for automatic timestamp updates
CREATE TRIGGER update_oregon_tires_service_images_updated_at
BEFORE UPDATE ON public.oregon_tires_service_images
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_service_images_updated_at();

-- Create index for better performance
CREATE INDEX idx_oregon_tires_service_images_service_key ON public.oregon_tires_service_images(service_key);
CREATE INDEX idx_oregon_tires_service_images_current ON public.oregon_tires_service_images(is_current);

-- Insert default current images
INSERT INTO public.oregon_tires_service_images (service_key, title, image_url, is_current) VALUES
('expert-technicians', 'Expert Technicians', '/src/assets/expert-technicians.jpg', true),
('fast-cars', 'Fast Service', '/src/assets/fast-cars.jpg', true),
('quality-parts', 'Quality Parts', '/src/assets/quality-car-parts.jpg', true),
('bilingual-support', 'Bilingual Support', '/src/assets/bilingual-support.jpg', true),
('tire-shop', 'Tire Services', '/src/assets/tire-shop.jpg', true),
('auto-repair', 'Auto Maintenance', '/src/assets/auto-repair.jpg', true),
('specialized-tools', 'Specialized Services', '/src/assets/specialized-tools.jpg', true);