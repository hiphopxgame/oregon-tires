-- Fix critical security issue: Remove overly permissive RLS policy on oregon_tires_appointments
-- This policy allowed public access to sensitive customer data

-- Drop the problematic policy that allows all operations with 'true' expression
DROP POLICY IF EXISTS "Allow all operations on appointments" ON oregon_tires_appointments;

-- The remaining policies will properly protect the data:
-- 1. "Only admins can view appointments" - restricts SELECT to admins only
-- 2. "Admins can manage appointments" - restricts UPDATE to admins only  
-- 3. "Admins can delete appointments" - restricts DELETE to admins only
-- 4. "Anyone can create appointments" - allows INSERT for booking functionality

-- Verify current policies are properly restrictive
-- SELECT operations: Only is_admin() OR is_super_admin()
-- UPDATE operations: Only is_admin() OR is_super_admin()  
-- DELETE operations: Only is_admin() OR is_super_admin()
-- INSERT operations: Anyone (needed for customer bookings)