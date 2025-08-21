-- Drop and recreate the view without SECURITY DEFINER to fix security issue
-- This ensures the view respects the querying user's RLS policies instead of the creator's

DROP VIEW IF EXISTS public.public_por_eve_profiles;

-- Recreate the view with SECURITY INVOKER (default behavior)
-- This view will now respect the RLS policies of the user making the query
CREATE VIEW public.public_por_eve_profiles AS
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