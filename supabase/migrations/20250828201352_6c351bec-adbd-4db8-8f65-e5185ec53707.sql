-- Fix critical PII exposure in oretir_employees table
-- Remove the dangerous public access policy and ensure admin-only access

-- Drop the overly permissive policy that allows public access
DROP POLICY IF EXISTS "Admin can manage employees" ON public.oretir_employees;

-- Ensure only the secure admin policies remain (they already exist but let's be explicit)
-- The existing policies "Admins can manage employees" and "Only admins can view employees" 
-- already properly restrict access to is_admin() OR is_super_admin()

-- Double-check that RLS is enabled on this table
ALTER TABLE public.oretir_employees ENABLE ROW LEVEL SECURITY;

-- Verify no public access by ensuring all policies require authentication
-- Add a fail-safe policy that explicitly denies public access
CREATE POLICY "Deny all public access to employees"
ON public.oretir_employees
FOR ALL
TO public
USING (false)
WITH CHECK (false);

-- Update the existing admin policies to be more explicit and secure
DROP POLICY IF EXISTS "Admins can manage employees" ON public.oretir_employees;
DROP POLICY IF EXISTS "Only admins can view employees" ON public.oretir_employees;

-- Create secure admin-only policies
CREATE POLICY "Admin only - can view employees"
ON public.oretir_employees
FOR SELECT
TO authenticated
USING (is_admin() OR is_super_admin());

CREATE POLICY "Admin only - can insert employees"
ON public.oretir_employees
FOR INSERT
TO authenticated
WITH CHECK (is_admin() OR is_super_admin());

CREATE POLICY "Admin only - can update employees"
ON public.oretir_employees
FOR UPDATE
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());

CREATE POLICY "Admin only - can delete employees"
ON public.oretir_employees
FOR DELETE
TO authenticated
USING (is_admin() OR is_super_admin());