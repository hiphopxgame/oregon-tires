-- Add column to store complete duration in seconds
ALTER TABLE public.oregon_tires_appointments 
ADD COLUMN actual_duration_seconds integer;