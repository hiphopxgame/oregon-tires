-- Fix security issues with Oregon Tires trigger functions by setting secure search paths

-- Update the custom hours trigger function
CREATE OR REPLACE FUNCTION public.update_oregon_tires_custom_hours_updated_at()
 RETURNS trigger
 LANGUAGE plpgsql
 SECURITY DEFINER
 SET search_path TO 'public'
AS $function$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$function$;

-- Update the employees trigger function  
CREATE OR REPLACE FUNCTION public.update_oregon_tires_employees_updated_at()
 RETURNS trigger
 LANGUAGE plpgsql
 SECURITY DEFINER
 SET search_path TO 'public'
AS $function$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$function$;

-- Update the gallery images trigger function
CREATE OR REPLACE FUNCTION public.update_oregon_tires_gallery_images_updated_at()
 RETURNS trigger
 LANGUAGE plpgsql
 SECURITY DEFINER
 SET search_path TO 'public'
AS $function$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$function$;