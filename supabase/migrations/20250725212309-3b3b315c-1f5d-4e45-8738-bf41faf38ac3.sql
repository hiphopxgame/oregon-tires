-- Fix RLS policies with proper security - simplified version
-- Only update policies that need fixing

-- Fix overly permissive policies for appointments (public can create, admins can manage)
DROP POLICY IF EXISTS "Allow all operations on appointments" ON oretir_appointments;

CREATE POLICY "Public can create appointments" 
ON oretir_appointments 
FOR INSERT 
WITH CHECK (true);

CREATE POLICY "Admins can view all appointments" 
ON oretir_appointments 
FOR SELECT 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Admins can update appointments" 
ON oretir_appointments 
FOR UPDATE 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Fix overly permissive policies for contact messages
DROP POLICY IF EXISTS "Allow all operations on contact messages" ON oretir_contact_messages;

CREATE POLICY "Public can create contact messages" 
ON oretir_contact_messages 
FOR INSERT 
WITH CHECK (true);

CREATE POLICY "Admins can view all contact messages" 
ON oretir_contact_messages 
FOR SELECT 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Admins can update contact messages" 
ON oretir_contact_messages 
FOR UPDATE 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Enable RLS on all tables
ALTER TABLE oretir_employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_employee_schedules ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_custom_hours ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_service_images ENABLE ROW LEVEL SECURITY;