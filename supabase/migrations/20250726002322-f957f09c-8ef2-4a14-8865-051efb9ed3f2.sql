-- Allow public access to view service images for the website
DROP POLICY IF EXISTS "Public can view service images" ON public.oretir_service_images;
CREATE POLICY "Public can view service images" 
ON public.oretir_service_images 
FOR SELECT 
USING (true);

-- Allow admin access to manage service images
DROP POLICY IF EXISTS "Admin can manage service images" ON public.oretir_service_images;
CREATE POLICY "Admin can manage service images" 
ON public.oretir_service_images 
FOR ALL 
USING (true)
WITH CHECK (true);