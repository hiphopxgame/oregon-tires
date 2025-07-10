-- Add address fields for home service appointments
ALTER TABLE oregon_tires_appointments 
ADD COLUMN service_location text DEFAULT 'shop',
ADD COLUMN customer_address text,
ADD COLUMN customer_city text,
ADD COLUMN customer_state text,
ADD COLUMN customer_zip text;