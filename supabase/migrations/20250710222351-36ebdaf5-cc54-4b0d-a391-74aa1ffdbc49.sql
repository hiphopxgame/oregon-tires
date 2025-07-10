-- Update all employees to be active
UPDATE public.oregon_tires_employees 
SET is_active = true 
WHERE is_active = false;