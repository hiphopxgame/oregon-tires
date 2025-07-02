-- Create table for custom store hours
CREATE TABLE public.oregon_tires_custom_hours (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  date DATE NOT NULL UNIQUE,
  is_closed BOOLEAN NOT NULL DEFAULT false,
  opening_time TIME WITHOUT TIME ZONE,
  closing_time TIME WITHOUT TIME ZONE,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT now()
);

-- Enable Row Level Security
ALTER TABLE public.oregon_tires_custom_hours ENABLE ROW LEVEL SECURITY;

-- Create policy for admin access
CREATE POLICY "Admin can manage custom hours" 
ON public.oregon_tires_custom_hours 
FOR ALL 
USING (true)
WITH CHECK (true);

-- Create function to update timestamps
CREATE OR REPLACE FUNCTION public.update_oregon_tires_custom_hours_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for automatic timestamp updates
CREATE TRIGGER update_oregon_tires_custom_hours_updated_at
BEFORE UPDATE ON public.oregon_tires_custom_hours
FOR EACH ROW
EXECUTE FUNCTION public.update_oregon_tires_custom_hours_updated_at();