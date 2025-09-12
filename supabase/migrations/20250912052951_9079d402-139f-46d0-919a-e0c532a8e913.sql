-- Fix PII exposure in por_eve_profiles by restricting access to sensitive data
-- Create a secure public view excluding sensitive information like emails and location data

-- Drop existing public access policies that expose sensitive data
DROP POLICY IF EXISTS "Public can view opted-in public profiles only" ON public.por_eve_profiles;
DROP POLICY IF EXISTS "Authenticated users can view appropriate profile data" ON public.por_eve_profiles;

-- Create a secure public view that excludes sensitive information
CREATE OR REPLACE VIEW public.por_eve_profiles_public AS
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
  -- Exclude sensitive data: email, city, state, zip_code, is_email_public
  created_at,
  updated_at,
  project_id
FROM public.por_eve_profiles
WHERE project_id = 'portland-events' AND is_email_public = true;

-- Set security_invoker to ensure view runs with querying user's permissions
ALTER VIEW public.por_eve_profiles_public SET (security_invoker = true);

-- Grant public access to the safe view only
GRANT SELECT ON public.por_eve_profiles_public TO public;

-- Recreate restrictive policies for the main table

-- Authenticated users can view profiles with emails only when explicitly opted-in
CREATE POLICY "Authenticated users can view opted-in profile data" ON public.por_eve_profiles
FOR SELECT
USING (
  (auth.uid() IS NOT NULL) AND 
  (project_id = 'portland-events') AND 
  (is_email_public = true)
);

-- Users can view their own complete profile (including sensitive data)
CREATE POLICY "Users can view their own complete profile" ON public.por_eve_profiles
FOR SELECT
USING (
  (auth.uid() = id) AND 
  (project_id = 'portland-events')
);

-- Recreate other existing policies without changes
CREATE POLICY "Users can create their own portland-events profile" ON public.por_eve_profiles
FOR INSERT
WITH CHECK (
  (auth.uid() = id) AND 
  (project_id = 'portland-events')
);

CREATE POLICY "Users can update their own portland-events profile" ON public.por_eve_profiles
FOR UPDATE
USING (
  (auth.uid() = id) AND 
  (project_id = 'portland-events')
);

-- Super admin retains full access
CREATE POLICY "Super admin can manage all por_eve_profiles" ON public.por_eve_profiles
FOR ALL
USING (auth.email() = 'tyronenorris@gmail.com');