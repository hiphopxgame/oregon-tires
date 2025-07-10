-- Create vehicles table to track customer vehicles
CREATE TABLE public.customer_vehicles (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  customer_email TEXT NOT NULL,
  customer_name TEXT NOT NULL,
  make TEXT,
  model TEXT,
  year INTEGER,
  license_plate TEXT,
  vin TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  UNIQUE(customer_email, license_plate, vin)
);

-- Enable RLS
ALTER TABLE public.customer_vehicles ENABLE ROW LEVEL SECURITY;

-- Create policy for vehicle access
CREATE POLICY "Allow all operations on customer vehicles" 
ON public.customer_vehicles 
FOR ALL 
USING (true)
WITH CHECK (true);

-- Add vehicle_id to appointments table
ALTER TABLE public.oregon_tires_appointments 
ADD COLUMN vehicle_id UUID REFERENCES public.customer_vehicles(id);

-- Create trigger for updating vehicles timestamp
CREATE TRIGGER update_customer_vehicles_updated_at
BEFORE UPDATE ON public.customer_vehicles
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_custom_hours_updated_at();