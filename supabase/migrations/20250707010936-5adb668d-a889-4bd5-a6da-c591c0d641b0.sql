-- Update default status for new appointments from 'pending' to 'new'
ALTER TABLE public.oregon_tires_appointments 
ALTER COLUMN status SET DEFAULT 'new';

-- Update existing pending appointments to new status
UPDATE public.oregon_tires_appointments 
SET status = 'new' 
WHERE status = 'pending';