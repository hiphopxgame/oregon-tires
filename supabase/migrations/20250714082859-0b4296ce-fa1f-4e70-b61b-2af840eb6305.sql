-- Add time tracking fields to appointments table
ALTER TABLE public.oregon_tires_appointments 
ADD COLUMN started_at TIMESTAMP WITH TIME ZONE,
ADD COLUMN completed_at TIMESTAMP WITH TIME ZONE,
ADD COLUMN actual_duration_minutes INTEGER;

-- Add index for better performance on time-based queries
CREATE INDEX idx_appointments_started_at ON public.oregon_tires_appointments(started_at);
CREATE INDEX idx_appointments_completed_at ON public.oregon_tires_appointments(completed_at);