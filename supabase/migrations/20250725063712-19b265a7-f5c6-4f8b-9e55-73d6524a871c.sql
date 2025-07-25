-- Drop the existing weekly schedule table
DROP TABLE IF EXISTS oregon_tires_employee_schedules;

-- Create new daily schedule table for employees
CREATE TABLE oregon_tires_employee_schedules (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  employee_id UUID NOT NULL REFERENCES oregon_tires_employees(id) ON DELETE CASCADE,
  schedule_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  UNIQUE(employee_id, schedule_date)
);

-- Enable RLS
ALTER TABLE oregon_tires_employee_schedules ENABLE ROW LEVEL SECURITY;

-- Create RLS policies
CREATE POLICY "Admin can manage employee schedules" 
ON oregon_tires_employee_schedules 
FOR ALL 
USING (true);

-- Create function for updating timestamps
CREATE OR REPLACE FUNCTION public.update_oregon_tires_employee_schedules_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER SET search_path = 'public';

-- Create trigger for automatic timestamp updates
CREATE TRIGGER update_oregon_tires_employee_schedules_updated_at
BEFORE UPDATE ON oregon_tires_employee_schedules
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_employee_schedules_updated_at();

-- Create index for better performance
CREATE INDEX idx_employee_schedules_employee_date ON oregon_tires_employee_schedules(employee_id, schedule_date);
CREATE INDEX idx_employee_schedules_date ON oregon_tires_employee_schedules(schedule_date);