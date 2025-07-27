-- Update appointment statuses based on employee assignment
-- First, add 'unassigned' as a valid status if it doesn't exist
-- Update appointments: set to 'new' if no employee assigned, or 'unassigned' if status is currently 'new'

UPDATE public.oretir_appointments 
SET status = CASE 
  WHEN assigned_employee_id IS NULL THEN 'new'
  WHEN status = 'new' AND assigned_employee_id IS NOT NULL THEN 'confirmed'
  ELSE status
END;

-- Add comment to explain the logic
COMMENT ON COLUMN public.oretir_appointments.status IS 'Appointment status: new (unassigned), confirmed (assigned), completed, cancelled';