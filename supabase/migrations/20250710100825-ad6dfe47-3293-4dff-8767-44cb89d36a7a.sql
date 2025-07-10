-- Add new fields for tire information and vehicle identification to appointments
ALTER TABLE oregon_tires_appointments 
ADD COLUMN tire_size TEXT,
ADD COLUMN license_plate TEXT,
ADD COLUMN vin TEXT;

-- Update the updated_at trigger to handle the new columns
-- (The existing trigger will automatically handle these new columns)