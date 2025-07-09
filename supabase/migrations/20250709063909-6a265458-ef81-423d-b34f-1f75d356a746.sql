
-- Create table for gallery images
CREATE TABLE public.oregon_tires_gallery_images (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  image_url TEXT NOT NULL,
  language TEXT NOT NULL DEFAULT 'english',
  display_order INTEGER DEFAULT 0,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable Row Level Security
ALTER TABLE public.oregon_tires_gallery_images ENABLE ROW LEVEL SECURITY;

-- Create policy for admin access
CREATE POLICY "Admin can manage gallery images" 
ON public.oregon_tires_gallery_images 
FOR ALL 
USING (true);

-- Create policy for public viewing of active images
CREATE POLICY "Public can view active gallery images" 
ON public.oregon_tires_gallery_images 
FOR SELECT 
USING (is_active = true);

-- Create storage bucket for gallery images
INSERT INTO storage.buckets (id, name, public) 
VALUES ('gallery-images', 'gallery-images', true);

-- Create storage policies for gallery uploads
CREATE POLICY "Admin can upload gallery images" 
ON storage.objects 
FOR INSERT 
WITH CHECK (bucket_id = 'gallery-images');

CREATE POLICY "Admin can update gallery images" 
ON storage.objects 
FOR UPDATE 
USING (bucket_id = 'gallery-images');

CREATE POLICY "Admin can delete gallery images" 
ON storage.objects 
FOR DELETE 
USING (bucket_id = 'gallery-images');

CREATE POLICY "Gallery images are publicly accessible" 
ON storage.objects 
FOR SELECT 
USING (bucket_id = 'gallery-images');

-- Create function to update timestamps
CREATE OR REPLACE FUNCTION public.update_oregon_tires_gallery_images_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for automatic timestamp updates
CREATE TRIGGER update_oregon_tires_gallery_images_updated_at
BEFORE UPDATE ON public.oregon_tires_gallery_images
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_gallery_images_updated_at();
