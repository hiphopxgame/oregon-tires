
-- Update the check constraint to allow 'pending' status
ALTER TABLE oregon_tires_appointments 
DROP CONSTRAINT IF EXISTS oregon_tires_appointments_status_check;

-- Add a new constraint that allows the expected status values
ALTER TABLE oregon_tires_appointments 
ADD CONSTRAINT oregon_tires_appointments_status_check 
CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled'));
