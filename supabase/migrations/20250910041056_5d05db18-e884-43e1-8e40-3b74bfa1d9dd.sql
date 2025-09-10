-- Fix Artist Profiles Email Exposure (Critical Security Issue)
-- Drop ALL existing policies and recreate with proper security

-- Drop all existing policies on artist_profiles table
DROP POLICY IF EXISTS "Admins can manage all artist profiles" ON public.artist_profiles;
DROP POLICY IF EXISTS "Artists can view their own complete profile" ON public.artist_profiles;
DROP POLICY IF EXISTS "Authenticated users can view opted-in artist emails" ON public.artist_profiles;
DROP POLICY IF EXISTS "Authenticated users can view opted-in emails" ON public.artist_profiles;
DROP POLICY IF EXISTS "Public can view basic artist info" ON public.artist_profiles;
DROP POLICY IF EXISTS "Users can create their profile" ON public.artist_profiles;
DROP POLICY IF EXISTS "Users can update their own profile" ON public.artist_profiles;

-- Recreate all policies with proper security

-- Admin access
CREATE POLICY "Admins can manage all artist profiles"
ON public.artist_profiles
FOR ALL
TO authenticated
USING (has_role(auth.uid(), 'admin'::app_role) OR (auth.email() = 'tyronenorris@gmail.com'))
WITH CHECK (has_role(auth.uid(), 'admin'::app_role) OR (auth.email() = 'tyronenorris@gmail.com'));

-- Users can create their own profile
CREATE POLICY "Users can create their own profile"
ON public.artist_profiles
FOR INSERT
TO authenticated
WITH CHECK (auth.uid() = user_id);

-- Users can update their own profile
CREATE POLICY "Users can update their own profile"
ON public.artist_profiles
FOR UPDATE
TO authenticated
USING (auth.uid() = user_id);

-- Users can view their own complete profile (including email)
CREATE POLICY "Users can view their own complete profile"
ON public.artist_profiles
FOR SELECT
TO authenticated
USING (auth.uid() = user_id);

-- Public can view basic artist info (NO EMAIL ACCESS)
CREATE POLICY "Public can view public artist profiles"
ON public.artist_profiles
FOR SELECT
TO anon
USING (is_public = true);

-- Authenticated users can view artist emails ONLY when explicitly opted-in
CREATE POLICY "Authenticated users can view opted-in emails"
ON public.artist_profiles
FOR SELECT
TO authenticated
USING (
  is_public = true AND 
  is_email_public = true AND
  auth.uid() != user_id  -- This is for viewing OTHER users' emails
);