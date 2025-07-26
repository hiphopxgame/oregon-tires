-- Set admin status for the primary user
INSERT INTO public.oretir_profiles (id, is_admin, created_at, updated_at)
SELECT 
  '50c27815-a68b-430a-b6ad-4a2c046d3497'::uuid,
  true,
  now(),
  now()
ON CONFLICT (id) DO UPDATE SET 
  is_admin = true,
  updated_at = now();