-- Fix the new security definer view issue created by artist_profiles_public
-- Set security_invoker=true to ensure the view runs with querying user's permissions

ALTER VIEW public.artist_profiles_public SET (security_invoker = true);