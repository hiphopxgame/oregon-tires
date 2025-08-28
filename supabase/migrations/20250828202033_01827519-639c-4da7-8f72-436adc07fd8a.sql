-- Fix critical PII exposure in oregon_tires_contact_messages table
-- Remove dangerous public access and ensure admin-only viewing

-- Drop the extremely dangerous policy that allows all public operations
DROP POLICY IF EXISTS "Allow all operations on contact messages" ON public.oregon_tires_contact_messages;

-- Ensure RLS is enabled
ALTER TABLE public.oregon_tires_contact_messages ENABLE ROW LEVEL SECURITY;

-- Clean up any redundant policies and create a secure set
DROP POLICY IF EXISTS "Admins can manage contact messages" ON public.oregon_tires_contact_messages;
DROP POLICY IF EXISTS "Admins can delete contact messages" ON public.oregon_tires_contact_messages;
DROP POLICY IF EXISTS "Only admins can view contact messages" ON public.oregon_tires_contact_messages;
DROP POLICY IF EXISTS "Anyone can submit contact messages" ON public.oregon_tires_contact_messages;

-- Create secure policies: Public can only submit, admins can view/manage
CREATE POLICY "Public can submit contact messages only"
ON public.oregon_tires_contact_messages
FOR INSERT
TO public
WITH CHECK (true);

CREATE POLICY "Admin only - view contact messages"
ON public.oregon_tires_contact_messages
FOR SELECT
TO authenticated
USING (is_admin() OR is_super_admin());

CREATE POLICY "Admin only - update contact messages"
ON public.oregon_tires_contact_messages
FOR UPDATE
TO authenticated
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());

CREATE POLICY "Admin only - delete contact messages"
ON public.oregon_tires_contact_messages
FOR DELETE
TO authenticated
USING (is_admin() OR is_super_admin());

-- Add explicit public denial for SELECT to prevent any data leaks
CREATE POLICY "Deny public read access to contact messages"
ON public.oregon_tires_contact_messages
FOR SELECT
TO public
USING (false);