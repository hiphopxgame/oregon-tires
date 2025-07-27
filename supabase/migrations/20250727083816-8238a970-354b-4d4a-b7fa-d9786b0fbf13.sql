-- Add notes column to appointments table
ALTER TABLE public.oretir_appointments 
ADD COLUMN admin_notes TEXT;