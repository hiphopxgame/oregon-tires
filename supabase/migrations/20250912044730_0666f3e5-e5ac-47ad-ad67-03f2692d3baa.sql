-- Fix the security definer view issue by recreating the view without SECURITY DEFINER
-- The view was automatically created with SECURITY DEFINER which is a security risk

-- Drop the existing view
DROP VIEW IF EXISTS public.por_eve_public_profiles;

-- Recreate the view without SECURITY DEFINER (default is SECURITY INVOKER which is safer)
CREATE VIEW public.por_eve_public_profiles AS
SELECT 
  id,
  display_name,
  username,
  avatar_url,
  website_url,
  facebook_url,
  instagram_url,
  twitter_url,
  youtube_url,
  spotify_url,
  bandcamp_url,
  soundcloud_url,
  city, -- Only general location, not specific address
  state,
  -- Explicitly exclude sensitive data like email, phone, zip_code
  created_at,
  updated_at,
  project_id
FROM public.por_eve_profiles
WHERE is_email_public = true AND project_id = 'portland-events';

-- Grant public access to the safe view
GRANT SELECT ON public.por_eve_public_profiles TO public;