-- Fix the function type mismatch
DROP FUNCTION IF EXISTS get_admin_users();

CREATE OR REPLACE FUNCTION get_admin_users()
RETURNS TABLE (
  id UUID,
  email VARCHAR(255),
  name TEXT,
  is_admin BOOLEAN,
  created_at TIMESTAMPTZ,
  updated_at TIMESTAMPTZ,
  last_sign_in_at TIMESTAMPTZ
)
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  -- Only allow admins or super admin to call this function
  IF NOT (is_admin() OR is_super_admin()) THEN
    RAISE EXCEPTION 'Access denied: Only admins can view admin users';
  END IF;

  RETURN QUERY
  SELECT 
    p.id,
    u.email,
    COALESCE(u.raw_user_meta_data->>'full_name', SPLIT_PART(u.email, '@', 1)) as name,
    p.is_admin,
    p.created_at,
    p.updated_at,
    u.last_sign_in_at
  FROM oretir_profiles p
  JOIN auth.users u ON p.id = u.id
  WHERE p.is_admin = true 
    AND p.project_id = 'oregon-tires'
  ORDER BY p.created_at ASC;
END;
$$;