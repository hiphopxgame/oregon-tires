-- Phase 1: Fix Critical RLS Security Issues
-- Update RLS policies to require authentication and proper access controls

-- First, drop existing overly permissive policies
DROP POLICY IF EXISTS "Allow all operations on appointments" ON oretir_appointments;
DROP POLICY IF EXISTS "Allow all operations on contact messages" ON oretir_contact_messages;
DROP POLICY IF EXISTS "Allow all operations on admin notifications" ON oretir_admin_notifications;
DROP POLICY IF EXISTS "Admin can manage gallery images" ON oretir_gallery_images;
DROP POLICY IF EXISTS "Admin can view all email logs" ON oretir_email_logs;
DROP POLICY IF EXISTS "System can insert email logs" ON oretir_email_logs;
DROP POLICY IF EXISTS "Settings access policy" ON oretir_settings;

-- Create secure RLS policies for appointments (public can create, admins can manage)
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

CREATE POLICY "Admins can delete appointments" 
ON oretir_appointments 
FOR DELETE 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Create secure RLS policies for contact messages (public can create, admins can manage)
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

-- Create secure RLS policies for admin notifications (admins only)
CREATE POLICY "Admins can view admin notifications" 
ON oretir_admin_notifications 
FOR SELECT 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Admins can manage admin notifications" 
ON oretir_admin_notifications 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Create secure RLS policies for gallery images (public read, admin write)
CREATE POLICY "Public can view active gallery images" 
ON oretir_gallery_images 
FOR SELECT 
USING (is_active = true);

CREATE POLICY "Admins can manage gallery images" 
ON oretir_gallery_images 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Create secure RLS policies for email logs (admins only)
CREATE POLICY "Admins can view email logs" 
ON oretir_email_logs 
FOR SELECT 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "System can insert email logs" 
ON oretir_email_logs 
FOR INSERT 
WITH CHECK (auth.role() = 'service_role');

-- Create secure RLS policies for settings (admins only)
CREATE POLICY "Admins can manage settings" 
ON oretir_settings 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Add RLS policies for other admin-only tables
CREATE POLICY "Admins can manage employees" 
ON oretir_employees 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Admins can manage employee schedules" 
ON oretir_employee_schedules 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Admins can manage custom hours" 
ON oretir_custom_hours 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

CREATE POLICY "Public can view active service images" 
ON oretir_service_images 
FOR SELECT 
USING (is_active = true);

CREATE POLICY "Admins can manage service images" 
ON oretir_service_images 
FOR ALL 
USING (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
)
WITH CHECK (
  EXISTS (
    SELECT 1 FROM oretir_profiles 
    WHERE id = auth.uid() AND is_admin = true
  )
);

-- Enable RLS on all tables if not already enabled
ALTER TABLE oretir_employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_employee_schedules ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_custom_hours ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_service_images ENABLE ROW LEVEL SECURITY;