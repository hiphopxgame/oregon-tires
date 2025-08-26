-- Fix critical security issue: Remove overly permissive RLS policy on customer_vehicles
-- This policy allows public access to sensitive customer and vehicle data including VINs

-- Drop the problematic policy that allows all operations with 'true' expression
DROP POLICY IF EXISTS "Allow all operations on customer vehicles" ON customer_vehicles;

-- The remaining policies will properly protect the data:
-- 1. "Only admins can view customer vehicles" - restricts SELECT to admins only
-- 2. "Admins can manage customer vehicles" - restricts all operations to admins only

-- This ensures only authorized staff can access sensitive customer vehicle information
-- including VINs, license plates, and personal contact details