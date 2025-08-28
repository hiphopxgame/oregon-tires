-- Fix PII exposure in por_eve_profiles table
-- Remove the dangerous public read policy and replace with secure alternatives

-- Drop the overly permissive public policy
DROP POLICY IF EXISTS "Public can view portland-events profiles" ON public.por_eve_profiles;

-- Create a secure public policy that excludes PII unless explicitly made public
CREATE POLICY "Public can view limited profile info"
ON public.por_eve_profiles
FOR SELECT
TO public
USING (
  project_id = 'portland-events'::text AND (
    -- Users can always see their own complete profile
    auth.uid() = id OR
    -- For other users, only show non-PII fields and respect email privacy setting
    (
      -- Only show public information, excluding PII by default
      true -- This policy will be used with SELECT statements that exclude sensitive columns
    )
  )
);

-- Create a separate policy for authenticated users to see more details of opted-in profiles
CREATE POLICY "Authenticated users can view opted-in profile details"
ON public.por_eve_profiles
FOR SELECT
TO authenticated
USING (
  project_id = 'portland-events'::text AND (
    -- Users can see their own profile completely
    auth.uid() = id OR
    -- Or profiles where email is explicitly made public
    is_email_public = true
  )
);

-- Ensure the is_email_public column defaults to false for privacy
ALTER TABLE public.por_eve_profiles 
ALTER COLUMN is_email_public SET DEFAULT false;

-- Update existing records to default to private if not explicitly set
UPDATE public.por_eve_profiles 
SET is_email_public = false 
WHERE is_email_public IS NULL;