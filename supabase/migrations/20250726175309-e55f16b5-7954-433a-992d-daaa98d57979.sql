-- Create security definer function to check admin status
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path = 'public'
AS $$
  SELECT COALESCE(
    (SELECT is_admin FROM public.oretir_profiles WHERE id = auth.uid()),
    false
  )
$$;

-- Update RLS policies for appointments table to require admin access
DROP POLICY IF EXISTS "Allow all operations on appointments" ON public.oretir_appointments;

CREATE POLICY "Admin can manage all appointments"
ON public.oretir_appointments
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

CREATE POLICY "Public can insert appointments"
ON public.oretir_appointments
FOR INSERT
WITH CHECK (true);

-- Update RLS policies for contact messages table to require admin access
DROP POLICY IF EXISTS "Allow all operations on contact messages" ON public.oretir_contact_messages;

CREATE POLICY "Admin can manage all contact messages"
ON public.oretir_contact_messages
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

CREATE POLICY "Public can insert contact messages"
ON public.oretir_contact_messages
FOR INSERT
WITH CHECK (true);

-- Update RLS policies for profiles table
DROP POLICY IF EXISTS "Allow all operations on profiles" ON public.oretir_profiles;

CREATE POLICY "Users can view their own profile"
ON public.oretir_profiles
FOR SELECT
USING (auth.uid() = id);

CREATE POLICY "Users can update their own profile"
ON public.oretir_profiles
FOR UPDATE
USING (auth.uid() = id)
WITH CHECK (auth.uid() = id);

CREATE POLICY "System can create profiles"
ON public.oretir_profiles
FOR INSERT
WITH CHECK (true);

-- Update other admin-only tables
DROP POLICY IF EXISTS "Admin can manage employee schedules" ON public.oretir_employee_schedules;
CREATE POLICY "Admin can manage employee schedules"
ON public.oretir_employee_schedules
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

DROP POLICY IF EXISTS "Admin can manage service images" ON public.oretir_service_images;
CREATE POLICY "Admin can manage service images"
ON public.oretir_service_images
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

DROP POLICY IF EXISTS "Admin can manage custom hours" ON public.oretir_custom_hours;
CREATE POLICY "Admin can manage custom hours"
ON public.oretir_custom_hours
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

DROP POLICY IF EXISTS "Admin can manage gallery images" ON public.oretir_gallery_images;
CREATE POLICY "Admin can manage gallery images"
ON public.oretir_gallery_images
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

DROP POLICY IF EXISTS "System can insert email logs" ON public.oretir_email_logs;
CREATE POLICY "Admin can view email logs"
ON public.oretir_email_logs
FOR SELECT
USING (is_admin());

CREATE POLICY "System can insert email logs"
ON public.oretir_email_logs
FOR INSERT
WITH CHECK (true);

DROP POLICY IF EXISTS "Allow all operations on admin notifications" ON public.oretir_admin_notifications;
CREATE POLICY "Admin can manage notifications"
ON public.oretir_admin_notifications
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());

DROP POLICY IF EXISTS "Allow all operations on settings" ON public.oretir_settings;
CREATE POLICY "Admin can manage settings"
ON public.oretir_settings
FOR ALL
USING (is_admin())
WITH CHECK (is_admin());