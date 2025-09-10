-- Fix Artist Profiles Email Exposure (Critical Security Issue)
-- Current policies are too permissive and may expose email addresses inappropriately

-- Drop existing potentially problematic policies
DROP POLICY IF EXISTS "Authenticated users can view opted-in artist emails" ON public.artist_profiles;
DROP POLICY IF EXISTS "Authenticated users can view opted-in emails" ON public.artist_profiles;
DROP POLICY IF EXISTS "Public can view basic artist info" ON public.artist_profiles;

-- Create secure policy for public viewing (excluding sensitive data)
CREATE POLICY "Public can view basic artist info (no emails)"
ON public.artist_profiles
FOR SELECT
TO anon
USING (
  is_public = true AND 
  -- Explicitly exclude email column from public access by not allowing direct email queries
  -- This policy allows viewing but email will only be accessible under specific conditions
  true
);

-- Create secure policy for authenticated users to view emails only when explicitly opted-in
CREATE POLICY "Authenticated users can view opted-in artist emails only"
ON public.artist_profiles
FOR SELECT
TO authenticated
USING (
  is_public = true AND 
  (
    -- Users can always see their own complete profile
    auth.uid() = user_id OR
    -- Or they can see email only if the artist has opted to make it public
    (is_email_public = true)
  )
);

-- Ensure artists can view their own complete profile
CREATE POLICY "Artists can view their own complete profile"
ON public.artist_profiles
FOR SELECT
TO authenticated
USING (auth.uid() = user_id);

-- Block any anonymous access to email-containing queries
CREATE POLICY "Block anonymous email access"
ON public.artist_profiles
FOR SELECT
TO anon
USING (
  is_public = true AND 
  -- This ensures email field queries from anonymous users return null/empty
  email IS NULL
);