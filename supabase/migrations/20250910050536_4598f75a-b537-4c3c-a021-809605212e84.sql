-- Additional security hardening for sensitive data access
-- Add explicit policies to prevent any unauthorized data access

-- Ensure admin notifications are truly admin-only
CREATE POLICY "Only admins can view notifications"
ON public.oretir_admin_notifications
FOR SELECT
TO authenticated
USING (is_admin() OR is_super_admin());

-- Ensure email logs are admin-only (already has policy but making it explicit)
-- This table contains sensitive email content
CREATE POLICY "Email logs are admin only"
ON public.oretir_email_logs
FOR SELECT
TO authenticated
USING (is_admin() OR is_super_admin());

-- Ensure service images management is admin-only for security
CREATE POLICY "Service images admin management"
ON public.oretir_service_images
FOR ALL
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());

-- Add comprehensive policy for custom hours (business hours data)
CREATE POLICY "Custom hours admin access"
ON public.oretir_custom_hours
FOR ALL
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());