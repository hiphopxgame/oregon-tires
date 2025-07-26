-- Fix schema mismatch: Update table structures to match our application code

-- Drop and recreate oretir_appointments with correct schema
DROP TABLE IF EXISTS oretir_appointments CASCADE;

CREATE TABLE oretir_appointments (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  service TEXT NOT NULL,
  preferred_date TEXT NOT NULL,
  preferred_time TEXT NOT NULL,
  message TEXT,
  status TEXT NOT NULL DEFAULT 'new',
  language TEXT NOT NULL DEFAULT 'english',
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  assigned_employee_id UUID,
  tire_size TEXT,
  license_plate TEXT,
  vin TEXT,
  service_location TEXT DEFAULT 'shop',
  customer_address TEXT,
  customer_city TEXT,
  customer_state TEXT,
  customer_zip TEXT,
  vehicle_id UUID,
  travel_distance_miles NUMERIC,
  travel_cost_estimate NUMERIC,
  started_at TIMESTAMP WITH TIME ZONE,
  completed_at TIMESTAMP WITH TIME ZONE,
  actual_duration_minutes INTEGER,
  actual_duration_seconds INTEGER
);

-- Drop and recreate oretir_contact_messages with correct schema
DROP TABLE IF EXISTS oretir_contact_messages CASCADE;

CREATE TABLE oretir_contact_messages (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  message TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'new',
  language TEXT NOT NULL DEFAULT 'english',
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable RLS on both tables
ALTER TABLE oretir_appointments ENABLE ROW LEVEL SECURITY;
ALTER TABLE oretir_contact_messages ENABLE ROW LEVEL SECURITY;

-- RLS policies for oretir_appointments
CREATE POLICY "Public can create appointments" ON oretir_appointments
  FOR INSERT WITH CHECK (true);

CREATE POLICY "Admin can view all appointments" ON oretir_appointments
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM oretir_profiles 
      WHERE id = auth.uid() AND is_admin = true
    )
  );

CREATE POLICY "Admin can update appointments" ON oretir_appointments
  FOR UPDATE USING (
    EXISTS (
      SELECT 1 FROM oretir_profiles 
      WHERE id = auth.uid() AND is_admin = true
    )
  );

-- RLS policies for oretir_contact_messages
CREATE POLICY "Public can create contact messages" ON oretir_contact_messages
  FOR INSERT WITH CHECK (true);

CREATE POLICY "Admin can view all contact messages" ON oretir_contact_messages
  FOR SELECT USING (
    EXISTS (
      SELECT 1 FROM oretir_profiles 
      WHERE id = auth.uid() AND is_admin = true
    )
  );

CREATE POLICY "Admin can update contact messages" ON oretir_contact_messages
  FOR UPDATE USING (
    EXISTS (
      SELECT 1 FROM oretir_profiles 
      WHERE id = auth.uid() AND is_admin = true
    )
  );