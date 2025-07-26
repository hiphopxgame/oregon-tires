-- Add project_id field to oretir_profiles table
ALTER TABLE public.oretir_profiles 
ADD COLUMN project_id TEXT NOT NULL DEFAULT 'oregon-tires';

-- Update existing users to have oregon-tires project
UPDATE public.oretir_profiles 
SET project_id = 'oregon-tires' 
WHERE project_id IS NULL OR project_id = '';

-- Update the handle_new_user function to include project_id
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
BEGIN
  INSERT INTO public.oretir_profiles (id, is_admin, project_id)
  VALUES (new.id, false, 'oregon-tires');
  RETURN new;
END;
$$;

-- Update the admin check function to include project validation
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS boolean
LANGUAGE sql
STABLE SECURITY DEFINER
SET search_path TO 'public'
AS $$
  SELECT COALESCE(
    (SELECT is_admin FROM public.oretir_profiles 
     WHERE id = auth.uid() AND project_id = 'oregon-tires'),
    false
  )
$$;

-- Update set_admin_by_email function to be project-aware
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
    
    -- Update or insert profile with project_id
    INSERT INTO public.oretir_profiles (id, is_admin, project_id, updated_at)
    VALUES (user_id, admin_status, target_project_id, NOW())
    ON CONFLICT (id) 
    DO UPDATE SET 
        is_admin = admin_status,
        project_id = target_project_id,
        updated_at = NOW();
    
    GET DIAGNOSTICS affected_rows = ROW_COUNT;
    
    RETURN affected_rows > 0;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Update RLS policies to include project filtering
DROP POLICY IF EXISTS "Users can view their own profile" ON public.oretir_profiles;
DROP POLICY IF EXISTS "Users can update their own profile" ON public.oretir_profiles;

CREATE POLICY "Users can view their own profile in same project" 
ON public.oretir_profiles 
FOR SELECT 
USING (auth.uid() = id AND project_id = 'oregon-tires');

CREATE POLICY "Users can update their own profile in same project" 
ON public.oretir_profiles 
FOR UPDATE 
USING (auth.uid() = id AND project_id = 'oregon-tires')
WITH CHECK (auth.uid() = id AND project_id = 'oregon-tires');