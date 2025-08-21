-- Fix the Security Definer View issue by properly recreating the view
-- and ensuring it follows RLS policies of the querying user

-- Drop the existing view
DROP VIEW IF EXISTS public.public_por_eve_profiles;

-- Revoke any explicit permissions that might cause SECURITY DEFINER behavior
REVOKE ALL ON TABLE por_eve_profiles FROM anon, authenticated, service_role;

-- Recreate the view as SECURITY INVOKER (explicit)
CREATE VIEW public.public_por_eve_profiles 
WITH (security_invoker = true) AS
SELECT 
  id,
  username,
  display_name,
  avatar_url,
  city,
  state,
  website_url,
  facebook_url,
  instagram_url,
  twitter_url,
  youtube_url,
  spotify_url,
  bandcamp_url,
  soundcloud_url
FROM por_eve_profiles
WHERE project_id = 'portland-events';

-- Grant only SELECT permission on the view to public roles
GRANT SELECT ON public.public_por_eve_profiles TO anon, authenticated;