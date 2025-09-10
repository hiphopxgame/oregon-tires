-- Fix cbake_orders table security by ensuring only authorized access
-- Current policies allow authenticated users to view orders by email match,
-- but we need to ensure no public access is possible

-- First, let's check if RLS is enabled (it should be based on the policies)
-- and tighten the SELECT policy to be more explicit

-- Drop existing policies and recreate with more restrictive access
DROP POLICY IF EXISTS "Users can view their own orders" ON public.cbake_orders;
DROP POLICY IF EXISTS "Anyone can view cbake orders for admin" ON public.cbake_orders;
DROP POLICY IF EXISTS "Anyone can update cbake messages for admin" ON public.cbake_orders;

-- Create more restrictive SELECT policy
CREATE POLICY "Authenticated users can view their own orders only"
ON public.cbake_orders
FOR SELECT
TO authenticated
USING (
  -- Admin can see all orders
  (auth.email() = 'hiphopxgame@gmail.com') OR
  -- Users can only see their own orders (both by user_id and email)
  (
    auth.uid() IS NOT NULL AND 
    (
      (user_id IS NOT NULL AND auth.uid() = user_id) OR
      (email IS NOT NULL AND auth.email() = email)
    )
  )
);

-- Ensure no public SELECT access
CREATE POLICY "Block public access to orders"
ON public.cbake_orders
FOR SELECT
TO anon
USING (false);

-- Also fix the cbake_messages table that was mentioned in similar policies
DROP POLICY IF EXISTS "Anyone can view cbake messages for admin" ON public.cbake_messages;

CREATE POLICY "Authenticated users can view messages"
ON public.cbake_messages
FOR SELECT
TO authenticated
USING (
  -- Admin can see all messages
  (auth.email() = 'hiphopxgame@gmail.com') OR
  -- Users can see their own messages
  (
    auth.uid() IS NOT NULL AND 
    (
      (user_id IS NOT NULL AND auth.uid() = user_id) OR
      (email IS NOT NULL AND auth.email() = email)
    )
  )
);

-- Block public access to messages
CREATE POLICY "Block public access to messages"
ON public.cbake_messages
FOR SELECT
TO anon
USING (false);