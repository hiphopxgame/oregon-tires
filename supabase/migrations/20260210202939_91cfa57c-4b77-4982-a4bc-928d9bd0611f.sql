
-- Fix 1: Email log insert policy - allow service_role AND admins
DROP POLICY IF EXISTS "Admin inserts email logs" ON public.oretir_email_logs;

CREATE POLICY "Service role can insert email logs"
  ON public.oretir_email_logs FOR INSERT TO service_role
  WITH CHECK (true);

CREATE POLICY "Admins can insert email logs"
  ON public.oretir_email_logs FOR INSERT TO authenticated
  WITH CHECK (public.is_admin());

-- Fix 2: Gallery storage bucket - restrict to admins only
DROP POLICY IF EXISTS "Admin can upload gallery images" ON storage.objects;
DROP POLICY IF EXISTS "Admin can update gallery images" ON storage.objects;
DROP POLICY IF EXISTS "Admin can delete gallery images" ON storage.objects;

CREATE POLICY "Admin can upload gallery images"
  ON storage.objects FOR INSERT TO authenticated
  WITH CHECK (bucket_id = 'gallery-images' AND public.is_admin());

CREATE POLICY "Admin can update gallery images"
  ON storage.objects FOR UPDATE TO authenticated
  USING (bucket_id = 'gallery-images' AND public.is_admin());

CREATE POLICY "Admin can delete gallery images"
  ON storage.objects FOR DELETE TO authenticated
  USING (bucket_id = 'gallery-images' AND public.is_admin());
