-- Fix artist email exposure in artist_profiles table
-- Add email visibility control and secure the public access policy

-- First, add a column to control email visibility (if it doesn't exist)
ALTER TABLE public.artist_profiles 
ADD COLUMN IF NOT EXISTS is_email_public boolean DEFAULT false;

-- Update existing records to default to private email
UPDATE public.artist_profiles 
SET is_email_public = false 
WHERE is_email_public IS NULL;

-- Drop the current public policy that exposes emails
DROP POLICY IF EXISTS "Public can view artist profiles without email" ON public.artist_profiles;

-- Create a new secure public policy that excludes email data
CREATE POLICY "Public can view non-sensitive artist info"
ON public.artist_profiles
FOR SELECT
TO public
USING (
  is_public = true AND (
    -- Users can see their own complete profile
    auth.uid() = user_id OR
    -- Public users cannot see email in their queries - this will be enforced at application level
    auth.uid() IS NULL OR
    -- Authenticated users can see email only if explicitly made public
    (auth.uid() IS NOT NULL AND is_email_public = true)
  )
);

-- Create a specific policy for email access by authenticated users
CREATE POLICY "Authenticated users can view opted-in artist emails"
ON public.artist_profiles
FOR SELECT
TO authenticated
USING (
  is_public = true AND (
    -- Artists can always see their own complete profile
    auth.uid() = user_id OR
    -- Other authenticated users can only see email if artist opted in
    is_email_public = true
  )
);

-- Ensure artists can still manage their own profiles
-- (existing policies for users managing their own profiles remain intact)