-- Add role column to employees table
ALTER TABLE public.oregon_tires_employees 
ADD COLUMN role text DEFAULT 'Worker';

-- Set Alex & Bob as Managers, everyone else as Workers
UPDATE public.oregon_tires_employees 
SET role = 'Manager' 
WHERE name IN ('Alex', 'Bob');

-- Function to format service names from slugs
CREATE OR REPLACE FUNCTION format_service_name(service_slug text)
RETURNS text
LANGUAGE plpgsql
AS $$
BEGIN
  RETURN CASE service_slug
    WHEN 'used-tires' THEN 'Used Tires'
    WHEN 'new-tires' THEN 'New Tires'
    WHEN 'tire-repair' THEN 'Tire Repair'
    WHEN 'tire-installation' THEN 'Tire Installation'
    WHEN 'tire-rotation' THEN 'Tire Rotation'
    WHEN 'tire-balancing' THEN 'Tire Balancing'
    WHEN 'flat-tire-repair' THEN 'Flat Tire Repair'
    WHEN 'wheel-alignment' THEN 'Wheel Alignment'
    WHEN 'mobile-service' THEN 'Mobile Service'
    ELSE INITCAP(REPLACE(service_slug, '-', ' '))
  END;
END;
$$;