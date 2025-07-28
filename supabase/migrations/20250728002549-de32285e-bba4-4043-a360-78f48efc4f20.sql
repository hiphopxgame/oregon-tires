-- Add RLS policy to allow admins to view all admin profiles
CREATE POLICY "Admins can view all admin profiles" 
ON public.oretir_profiles 
FOR SELECT 
USING (
  (is_admin() OR is_super_admin()) AND is_admin = true
);