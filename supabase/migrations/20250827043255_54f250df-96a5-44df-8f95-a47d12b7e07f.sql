-- Consolidate appointment tables: Migrate oregon_tires_appointments data to oretir_appointments
-- and update oretir_appointments schema to include missing fields

-- Step 1: Add missing column to oretir_appointments
ALTER TABLE oretir_appointments ADD COLUMN IF NOT EXISTS updated_at timestamp with time zone NOT NULL DEFAULT now();

-- Step 2: Add trigger to auto-update updated_at column
CREATE OR REPLACE FUNCTION update_oretir_appointments_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER oretir_appointments_updated_at_trigger
  BEFORE UPDATE ON oretir_appointments
  FOR EACH ROW
  EXECUTE FUNCTION update_oretir_appointments_updated_at();

-- Step 3: Migrate data from oregon_tires_appointments to oretir_appointments
-- Convert date/time types to text format to match oretir_appointments schema
INSERT INTO oretir_appointments (
  id, first_name, last_name, email, phone, service, preferred_date, preferred_time,
  message, language, status, created_at, updated_at, assigned_employee_id,
  tire_size, license_plate, vin, service_location, customer_address,
  customer_city, customer_state, customer_zip, vehicle_id,
  travel_distance_miles, travel_cost_estimate, started_at, completed_at,
  actual_duration_minutes, actual_duration_seconds, admin_notes
)
SELECT 
  id, first_name, last_name, email, phone, service,
  preferred_date::text as preferred_date,
  preferred_time::text as preferred_time,
  message, language, status, created_at, updated_at, assigned_employee_id,
  tire_size, license_plate, vin, service_location, customer_address,
  customer_city, customer_state, customer_zip, vehicle_id,
  travel_distance_miles, travel_cost_estimate, started_at, completed_at,
  actual_duration_minutes, actual_duration_seconds,
  NULL as admin_notes -- oregon_tires_appointments doesn't have this field
FROM oregon_tires_appointments
WHERE id NOT IN (SELECT id FROM oretir_appointments);

-- Step 4: Update any existing records in oretir_appointments with data from oregon_tires_appointments
-- if oregon_tires_appointments has more recent data
UPDATE oretir_appointments 
SET 
  updated_at = GREATEST(oretir_appointments.updated_at, ot.updated_at),
  assigned_employee_id = COALESCE(ot.assigned_employee_id, oretir_appointments.assigned_employee_id),
  vehicle_id = COALESCE(ot.vehicle_id, oretir_appointments.vehicle_id),
  travel_distance_miles = COALESCE(ot.travel_distance_miles, oretir_appointments.travel_distance_miles),
  travel_cost_estimate = COALESCE(ot.travel_cost_estimate, oretir_appointments.travel_cost_estimate),
  started_at = COALESCE(ot.started_at, oretir_appointments.started_at),
  completed_at = COALESCE(ot.completed_at, oretir_appointments.completed_at),
  actual_duration_minutes = COALESCE(ot.actual_duration_minutes, oretir_appointments.actual_duration_minutes),
  actual_duration_seconds = COALESCE(ot.actual_duration_seconds, oretir_appointments.actual_duration_seconds)
FROM oregon_tires_appointments ot
WHERE oretir_appointments.id = ot.id
  AND ot.updated_at > oretir_appointments.created_at;