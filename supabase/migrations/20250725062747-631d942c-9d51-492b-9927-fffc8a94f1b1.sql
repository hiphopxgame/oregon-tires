-- Drop the existing table that has the wrong structure
DROP TABLE IF EXISTS public.oregon_tires_employee_schedules CASCADE;

-- Create the correct employee schedules table
CREATE TABLE public.oregon_tires_employee_schedules (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  employee_id UUID NOT NULL REFERENCES oregon_tires_employees(id) ON DELETE CASCADE,
  day_of_week INTEGER NOT NULL CHECK (day_of_week >= 0 AND day_of_week <= 6), -- 0 = Sunday, 6 = Saturday
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  UNIQUE(employee_id, day_of_week)
);

-- Enable RLS
ALTER TABLE public.oregon_tires_employee_schedules ENABLE ROW LEVEL SECURITY;

-- Create policies
CREATE POLICY "Admin can manage employee schedules" 
ON public.oregon_tires_employee_schedules 
FOR ALL 
USING (true);

-- Create trigger for automatic timestamp updates
CREATE TRIGGER update_oregon_tires_employee_schedules_updated_at
BEFORE UPDATE ON public.oregon_tires_employee_schedules
FOR EACH ROW
EXECUTE FUNCTION public.update_updated_at_column();