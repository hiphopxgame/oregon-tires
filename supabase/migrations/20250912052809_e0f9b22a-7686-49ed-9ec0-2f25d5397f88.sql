-- Fix PII exposure in artist_profiles by removing public access to email addresses
-- RLS policies control row access, not column access, so we need to restrict row access entirely for public users

-- Drop all existing policies for artist_profiles
DROP POLICY IF EXISTS "Public can view public artist profiles (no email)" ON public.artist_profiles;
DROP POLICY IF EXISTS "Authenticated users can view opted-in emails" ON public.artist_profiles;
DROP POLICY IF EXISTS "Users can view their own complete profile" ON public.artist_profiles;
DROP POLICY IF EXISTS "Users can create their own profile" ON public.artist_profiles;
DROP POLICY IF EXISTS "Users can update their own profile" ON public.artist_profiles;
DROP POLICY IF EXISTS "Admins can manage all artist profiles" ON public.artist_profiles;

-- Create a public view that excludes sensitive information like email addresses
CREATE OR REPLACE VIEW public.artist_profiles_public AS
SELECT 
  id,
  name,
  bio,
  avatar_url,
  website_url,
  instagram_url,
  youtube_url,
  spotify_url,
  bandcamp_url,
  apple_music_url,
  soundcloud_url,
  tiktok_url,
  facebook_url,
  twitter_url,
  is_featured,
  display_order,
  is_public,
  created_at,
  updated_at
  -- Explicitly exclude: email, user_id, is_email_public, is_archived
FROM public.artist_profiles
WHERE is_public = true AND is_archived = false;

-- Grant public access to the safe view only
GRANT SELECT ON public.artist_profiles_public TO public;

-- Recreate restrictive RLS policies for the main table

-- Only authenticated users can view the full table (including emails)
CREATE POLICY "Authenticated users can view public profiles with emails" ON public.artist_profiles
FOR SELECT
USING (
  (auth.uid() IS NOT NULL) AND 
  (is_public = true) AND 
  (is_email_public = true)
);

-- Users can view their own complete profile
CREATE POLICY "Users can view their own complete profile" ON public.artist_profiles
FOR SELECT
USING (auth.uid() = user_id);

-- Users can create their own profile
CREATE POLICY "Users can create their own profile" ON public.artist_profiles
FOR INSERT
WITH CHECK (auth.uid() = user_id);

-- Users can update their own profile
CREATE POLICY "Users can update their own profile" ON public.artist_profiles
FOR UPDATE
USING (auth.uid() = user_id);

-- Admins can manage all artist profiles
CREATE POLICY "Admins can manage all artist profiles" ON public.artist_profiles
FOR ALL
USING (has_role(auth.uid(), 'admin'::app_role) OR (auth.email() = 'tyronenorris@gmail.com'::text))
WITH CHECK (has_role(auth.uid(), 'admin'::app_role) OR (auth.email() = 'tyronenorris@gmail.com'::text));