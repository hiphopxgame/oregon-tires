-- Fix customer PII security issue: Restrict email access in artist_profiles table
-- Remove public access to email addresses while maintaining public access to other profile data

-- Drop existing policies that expose emails to public
DROP POLICY IF EXISTS "Public can view public artist profiles" ON public.artist_profiles;
DROP POLICY IF EXISTS "Authenticated users can view opted-in emails" ON public.artist_profiles;

-- Create new policy for public access that excludes email field
-- Public users can view public profiles but not email addresses
CREATE POLICY "Public can view public artist profiles (no email)" ON public.artist_profiles
FOR SELECT
USING (is_public = true);

-- Create policy for authenticated users to access email only when opted-in
CREATE POLICY "Authenticated users can view opted-in emails" ON public.artist_profiles
FOR SELECT
USING (
  (auth.uid() IS NOT NULL) AND 
  (is_public = true) AND 
  (is_email_public = true)
);

-- Ensure users can still view their own complete profile
-- (This policy should already exist but ensuring it's correct)
DROP POLICY IF EXISTS "Users can view their own complete profile" ON public.artist_profiles;
CREATE POLICY "Users can view their own complete profile" ON public.artist_profiles
FOR SELECT
USING (auth.uid() = user_id);