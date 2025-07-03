-- Create employees table
CREATE TABLE public.oregon_tires_employees (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  name TEXT NOT NULL,
  email TEXT,
  phone TEXT,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.oregon_tires_employees ENABLE ROW LEVEL SECURITY;

-- Create policy for admin access
CREATE POLICY "Admin can manage employees" 
ON public.oregon_tires_employees 
FOR ALL 
USING (true);

-- Insert the 5 employees
INSERT INTO public.oregon_tires_employees (name) VALUES 
('Alex'),
('Bob'), 
('Chris'),
('Dave'),
('Edward');

-- Add simultaneous_bookings column to custom hours table
ALTER TABLE public.oregon_tires_custom_hours 
ADD COLUMN simultaneous_bookings INTEGER DEFAULT 2;

-- Create trigger for updating timestamps
CREATE OR REPLACE FUNCTION public.update_oregon_tires_employees_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_oregon_tires_employees_updated_at
  BEFORE UPDATE ON public.oregon_tires_employees
  FOR EACH ROW
  EXECUTE FUNCTION public.update_oregon_tires_employees_updated_at();