-- Enable replica identity for real-time updates
ALTER TABLE public.oregon_tires_appointments REPLICA IDENTITY FULL;
ALTER TABLE public.oregon_tires_contact_messages REPLICA IDENTITY FULL;
ALTER TABLE public.oregon_tires_employees REPLICA IDENTITY FULL;
ALTER TABLE public.oregon_tires_custom_hours REPLICA IDENTITY FULL;

-- Add tables to the supabase_realtime publication for real-time functionality
ALTER PUBLICATION supabase_realtime ADD TABLE public.oregon_tires_appointments;
ALTER PUBLICATION supabase_realtime ADD TABLE public.oregon_tires_contact_messages;
ALTER PUBLICATION supabase_realtime ADD TABLE public.oregon_tires_employees;
ALTER PUBLICATION supabase_realtime ADD TABLE public.oregon_tires_custom_hours;