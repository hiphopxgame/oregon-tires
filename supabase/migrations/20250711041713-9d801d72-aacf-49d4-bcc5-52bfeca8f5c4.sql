-- Create a table to log all emails sent
CREATE TABLE public.oregon_tires_email_logs (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  email_type TEXT NOT NULL,
  recipient_email TEXT NOT NULL,
  recipient_name TEXT NOT NULL,
  recipient_type TEXT NOT NULL CHECK (recipient_type IN ('customer', 'employee')),
  subject TEXT NOT NULL,
  body TEXT NOT NULL,
  appointment_id UUID REFERENCES oregon_tires_appointments(id),
  sent_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  resend_message_id TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable RLS
ALTER TABLE public.oregon_tires_email_logs ENABLE ROW LEVEL SECURITY;

-- Create policy for admin access
CREATE POLICY "Admin can view all email logs" 
ON public.oregon_tires_email_logs 
FOR SELECT 
USING (true);

-- Create policy for inserting logs
CREATE POLICY "System can insert email logs" 
ON public.oregon_tires_email_logs 
FOR INSERT 
WITH CHECK (true);

-- Create index for better performance
CREATE INDEX idx_oregon_tires_email_logs_sent_at ON public.oregon_tires_email_logs(sent_at DESC);
CREATE INDEX idx_oregon_tires_email_logs_appointment_id ON public.oregon_tires_email_logs(appointment_id);