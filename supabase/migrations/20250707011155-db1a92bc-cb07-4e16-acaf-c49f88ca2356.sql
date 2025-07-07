-- Drop the existing check constraint
ALTER TABLE public.oregon_tires_appointments 
DROP CONSTRAINT IF EXISTS oregon_tires_appointments_status_check;

-- Add a new check constraint that includes all existing statuses plus 'new'
ALTER TABLE public.oregon_tires_appointments 
ADD CONSTRAINT oregon_tires_appointments_status_check 
CHECK (status IN ('new', 'pending', 'confirmed', 'completed', 'cancelled'));

-- Update default status for new appointments to 'new'
ALTER TABLE public.oregon_tires_appointments 
ALTER COLUMN status SET DEFAULT 'new';

-- Update existing pending appointments to new status
UPDATE public.oregon_tires_appointments 
SET status = 'new' 
WHERE status = 'pending';