-- Create a super admin function to check if user is the universal admin
CREATE OR REPLACE FUNCTION public.is_super_admin()
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path TO 'public'
AS $$
  SELECT EXISTS (
    SELECT 1 FROM auth.users 
    WHERE id = auth.uid() AND email = 'tyronenorris@gmail.com'
  )
$$;

-- Update the is_admin function to include super admin check
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path TO 'public'
AS $$
  SELECT COALESCE(
    -- Check if super admin
    (SELECT true FROM auth.users WHERE id = auth.uid() AND email = 'tyronenorris@gmail.com'),
    -- Or check regular project admin
    (SELECT is_admin FROM public.oretir_profiles 
     WHERE id = auth.uid() AND project_id = 'oregon-tires'),
    false
  )
$$;

-- Update set_admin_by_email to handle super admin
CREATE OR REPLACE FUNCTION set_admin_by_email(user_email TEXT, admin_status BOOLEAN DEFAULT TRUE, target_project_id TEXT DEFAULT 'oregon-tires')
RETURNS BOOLEAN AS $$
DECLARE
    user_id UUID;
    affected_rows INTEGER;
BEGIN
    -- Get user ID from auth.users
    SELECT id INTO user_id 
    FROM auth.users 
    WHERE email = user_email;
    
    IF user_id IS NULL THEN
        RAISE EXCEPTION 'User with email % not found', user_email;
    END IF;
    
    -- Special handling for super admin
    IF user_email = 'tyronenorris@gmail.com' THEN
        -- Ensure super admin has profiles for all projects they need access to
        INSERT INTO public.oretir_profiles (id, is_admin, project_id, updated_at)
        VALUES (user_id, true, target_project_id, NOW())
        ON CONFLICT (id) 
        DO UPDATE SET 
            is_admin = true,  -- Always admin for super admin
            updated_at = NOW();
    ELSE
        -- Regular project admin handling
        INSERT INTO public.oretir_profiles (id, is_admin, project_id, updated_at)
        VALUES (user_id, admin_status, target_project_id, NOW())
        ON CONFLICT (id) 
        DO UPDATE SET 
            is_admin = admin_status,
            project_id = target_project_id,
            updated_at = NOW();
    END IF;
    
    GET DIAGNOSTICS affected_rows = ROW_COUNT;
    
    RETURN affected_rows > 0;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Ensure tyronenorris@gmail.com has admin access for oregon-tires
SELECT set_admin_by_email('tyronenorris@gmail.com', true, 'oregon-tires');

-- Update RLS policies to include super admin access
DROP POLICY IF EXISTS "Users can view their own profile in same project" ON public.oretir_profiles;
DROP POLICY IF EXISTS "Users can update their own profile in same project" ON public.oretir_profiles;

CREATE POLICY "Users can view their own profile in same project or super admin" 
ON public.oretir_profiles 
FOR SELECT 
USING (
  auth.uid() = id AND project_id = 'oregon-tires' OR 
  is_super_admin()
);

CREATE POLICY "Users can update their own profile in same project or super admin" 
ON public.oretir_profiles 
FOR UPDATE 
USING (
  auth.uid() = id AND project_id = 'oregon-tires' OR 
  is_super_admin()
)
WITH CHECK (
  auth.uid() = id AND project_id = 'oregon-tires' OR 
  is_super_admin()
);

-- Add super admin access to other tables that use is_admin()
-- Update admin notifications policy
DROP POLICY IF EXISTS "Admin can manage notifications" ON public.oretir_admin_notifications;
CREATE POLICY "Admin can manage notifications" 
ON public.oretir_admin_notifications 
FOR ALL 
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());

-- Update email logs policy  
DROP POLICY IF EXISTS "Admin can view email logs" ON public.oretir_email_logs;
CREATE POLICY "Admin can view email logs" 
ON public.oretir_email_logs 
FOR SELECT 
USING (is_admin() OR is_super_admin());

-- Update other admin-only tables
DROP POLICY IF EXISTS "Admin can manage settings" ON public.oretir_settings;
CREATE POLICY "Admin can manage settings" 
ON public.oretir_settings 
FOR ALL 
USING (is_admin() OR is_super_admin())
WITH CHECK (is_admin() OR is_super_admin());