-- Add mileage and estimate fields to appointments table for mobile/roadside services
ALTER TABLE public.oregon_tires_appointments 
ADD COLUMN travel_distance_miles NUMERIC(5,2),
ADD COLUMN travel_cost_estimate NUMERIC(8,2);