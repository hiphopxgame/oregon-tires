-- Add assigned_employee_id column to appointments table
ALTER TABLE public.oregon_tires_appointments 
ADD COLUMN assigned_employee_id UUID REFERENCES public.oregon_tires_employees(id);