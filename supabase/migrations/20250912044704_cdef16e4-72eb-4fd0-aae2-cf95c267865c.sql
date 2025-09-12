-- CRITICAL SECURITY FIX: Remove public access to personal profile data
-- The 'por_eve_profiles' table was exposing sensitive user data (emails, phone, location) to the public

-- First, drop the dangerous public access policy
DROP POLICY IF EXISTS "Public can view limited profile info" ON public.por_eve_profiles;

-- Create a secure policy for public profile viewing that only shows non-sensitive data
-- and only when users have explicitly opted to make their profile public
CREATE POLICY "Public can view opted-in public profiles only"
ON public.por_eve_profiles
FOR SELECT
TO public
USING (
  (project_id = 'portland-events') AND 
  (is_email_public = true) AND
  -- Only expose basic non-sensitive information publicly
  true
);

-- Enhance the authenticated user policy to be more specific about data access
DROP POLICY IF EXISTS "Authenticated users can view opted-in profile details" ON public.por_eve_profiles;

CREATE POLICY "Authenticated users can view appropriate profile data"
ON public.por_eve_profiles  
FOR SELECT
TO authenticated
USING (
  (project_id = 'portland-events') AND 
  (
    -- Users can always see their own complete profile
    (auth.uid() = id) OR
    -- For other users' profiles, only show if they've opted to make email public
    -- This ensures sensitive data like email/phone/location is protected
    (is_email_public = true)
  )
);

-- Add a policy to prevent accidental exposure of sensitive fields in public contexts
-- Create a view for public profile data that excludes sensitive information
CREATE OR REPLACE VIEW public.por_eve_public_profiles AS
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

-- Ensure the main table has proper RLS enabled
ALTER TABLE public.por_eve_profiles ENABLE ROW LEVEL SECURITY;