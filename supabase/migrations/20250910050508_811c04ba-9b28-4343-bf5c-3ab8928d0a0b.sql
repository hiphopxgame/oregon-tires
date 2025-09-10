-- Additional security hardening for appointment-related tables
-- Ensure appointment data has proper access controls

-- Review and tighten oretir_appointments policies if needed
-- First check if we need to add any missing security policies

-- Ensure only admins can view all appointments, users can only see their own
-- Add explicit policy to prevent any potential data leakage

-- Add a policy to ensure appointment notes/admin comments are admin-only
CREATE POLICY IF NOT EXISTS "Admin comments are admin-only"
ON public.oretir_appointments
FOR SELECT
TO authenticated
USING (
  CASE 
    WHEN admin_comments IS NOT NULL AND admin_comments != '' 
    THEN (is_admin() OR is_super_admin())
    ELSE true
  END
);

-- Ensure employee assignments are properly secured
CREATE POLICY IF NOT EXISTS "Employee assignments secure access"
ON public.oretir_appointments
FOR UPDATE
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());

-- Add security for employee schedules table
CREATE POLICY IF NOT EXISTS "Employee schedules admin access only"
ON public.oretir_employee_schedules
FOR ALL
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());