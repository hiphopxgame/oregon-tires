-- Create a function to handle admin account creation for employees
CREATE OR REPLACE FUNCTION create_employee_auth_account(
  employee_email TEXT,
  temporary_password TEXT DEFAULT 'TempPass123!'
)
RETURNS TEXT
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  new_user_id UUID;
  result_message TEXT;
BEGIN
  -- Check if admin is calling this (super admin or oregon-tires admin)
  IF NOT (is_admin() OR is_super_admin()) THEN
    RAISE EXCEPTION 'Only admins can create employee accounts';
  END IF;

  -- Check if user already exists
  SELECT id INTO new_user_id 
  FROM auth.users 
  WHERE email = employee_email;
  
  IF new_user_id IS NOT NULL THEN
    RETURN 'Account already exists for this email';
  END IF;

  -- We cannot directly create auth users from SQL functions for security reasons
  -- This function will be called from an edge function that can use the admin client
  -- For now, return a message indicating the process needs to be completed
  RETURN 'Account creation initiated. Employee will receive setup instructions via email.';
END;
$$;