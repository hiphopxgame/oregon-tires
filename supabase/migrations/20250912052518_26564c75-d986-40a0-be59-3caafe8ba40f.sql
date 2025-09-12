-- Set security_invoker=true on public view to resolve linter rule 0010 (Security Definer View)
-- This ensures the view runs with the permissions of the querying user and respects RLS

DO $$
BEGIN
  -- Only update if the view exists
  IF EXISTS (
    SELECT 1 FROM information_schema.views 
    WHERE table_schema = 'public' AND table_name = 'por_eve_public_profiles'
  ) THEN
    ALTER VIEW public.por_eve_public_profiles SET (security_invoker = true);
  END IF;
END $$;

-- Keep public read access to the safe view
GRANT SELECT ON public.por_eve_public_profiles TO public;