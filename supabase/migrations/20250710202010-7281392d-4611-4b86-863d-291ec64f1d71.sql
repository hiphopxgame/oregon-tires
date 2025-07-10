-- Create employee schedule table to track when employees are working
CREATE TABLE public.oregon_tires_employee_schedules (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  employee_id UUID NOT NULL REFERENCES public.oregon_tires_employees(id) ON DELETE CASCADE,
  work_date DATE NOT NULL,
  start_time TIME WITHOUT TIME ZONE NOT NULL DEFAULT '08:00:00',
  end_time TIME WITHOUT TIME ZONE NOT NULL DEFAULT '17:00:00',
  is_available BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  UNIQUE(employee_id, work_date)
);

-- Enable RLS
ALTER TABLE public.oregon_tires_employee_schedules ENABLE ROW LEVEL SECURITY;

-- Create policy for employee schedules
CREATE POLICY "Admin can manage employee schedules" 
ON public.oregon_tires_employee_schedules 
FOR ALL 
USING (true)
WITH CHECK (true);

-- Create trigger for updating schedules timestamp
CREATE TRIGGER update_oregon_tires_employee_schedules_updated_at
BEFORE UPDATE ON public.oregon_tires_employee_schedules
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_custom_hours_updated_at();