-- Fix function security by setting proper search_path for all functions
-- This prevents potential SQL injection through search_path manipulation

-- Update all functions to have secure search_path settings
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean
LANGUAGE sql
STABLE 
SECURITY DEFINER
SET search_path = 'public'
AS $$
  SELECT COALESCE(
    -- Check if super admin
    (SELECT true FROM auth.users WHERE id = auth.uid() AND email = 'tyronenorris@gmail.com'),
    -- Or check regular project admin
    (SELECT is_admin FROM public.oretir_profiles 
     WHERE id = auth.uid() AND project_id = 'oregon-tires'),
    false
  )
$$;

CREATE OR REPLACE FUNCTION public.is_super_admin()
RETURNS boolean
LANGUAGE sql
STABLE 
SECURITY DEFINER
SET search_path = 'public'
AS $$
  SELECT EXISTS (
    SELECT 1 FROM auth.users 
    WHERE id = auth.uid() AND email = 'tyronenorris@gmail.com'
  )
$$;

CREATE OR REPLACE FUNCTION public.has_role(_user_id uuid, _role app_role)
RETURNS boolean
LANGUAGE sql
STABLE 
SECURITY DEFINER
SET search_path = 'public'
AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.user_roles
    WHERE user_id = _user_id
      AND role = _role
  )
$$;