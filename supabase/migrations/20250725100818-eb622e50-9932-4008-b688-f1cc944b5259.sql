-- Rename all oregon_tires_ tables to oretir_
ALTER TABLE oregon_tires_gallery_images RENAME TO oretir_gallery_images;
ALTER TABLE oregon_tires_custom_hours RENAME TO oretir_custom_hours;
ALTER TABLE oregon_tires_employees RENAME TO oretir_employees;
ALTER TABLE oregon_tires_email_logs RENAME TO oretir_email_logs;
ALTER TABLE oregon_tires_service_images RENAME TO oretir_service_images;
ALTER TABLE oregon_tires_appointments RENAME TO oretir_appointments;

-- Update function names to match new table names
DROP FUNCTION IF EXISTS update_oregon_tires_employee_schedules_updated_at();
DROP FUNCTION IF EXISTS update_oregon_tires_service_images_updated_at();
DROP FUNCTION IF EXISTS update_oregon_tires_custom_hours_updated_at();
DROP FUNCTION IF EXISTS update_oregon_tires_employees_updated_at();
DROP FUNCTION IF EXISTS update_oregon_tires_gallery_images_updated_at();

-- Recreate functions with new names
CREATE OR REPLACE FUNCTION public.update_oretir_employee_schedules_updated_at()
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

CREATE OR REPLACE FUNCTION public.update_oretir_service_images_updated_at()
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

CREATE OR REPLACE FUNCTION public.update_oretir_custom_hours_updated_at()
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

CREATE OR REPLACE FUNCTION public.update_oretir_employees_updated_at()
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

CREATE OR REPLACE FUNCTION public.update_oretir_gallery_images_updated_at()
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

-- Update triggers to use new function names
DROP TRIGGER IF EXISTS update_oregon_tires_employee_schedules_updated_at ON oregon_tires_employee_schedules;
DROP TRIGGER IF EXISTS update_oregon_tires_service_images_updated_at ON oretir_service_images;
DROP TRIGGER IF EXISTS update_oregon_tires_custom_hours_updated_at ON oretir_custom_hours;
DROP TRIGGER IF EXISTS update_oregon_tires_employees_updated_at ON oretir_employees;
DROP TRIGGER IF EXISTS update_oregon_tires_gallery_images_updated_at ON oretir_gallery_images;

CREATE TRIGGER update_oretir_service_images_updated_at
  BEFORE UPDATE ON oretir_service_images
  FOR EACH ROW
  EXECUTE FUNCTION public.update_oretir_service_images_updated_at();

CREATE TRIGGER update_oretir_custom_hours_updated_at
  BEFORE UPDATE ON oretir_custom_hours
  FOR EACH ROW
  EXECUTE FUNCTION public.update_oretir_custom_hours_updated_at();

CREATE TRIGGER update_oretir_employees_updated_at
  BEFORE UPDATE ON oretir_employees
  FOR EACH ROW
  EXECUTE FUNCTION public.update_oretir_employees_updated_at();

CREATE TRIGGER update_oretir_gallery_images_updated_at
  BEFORE UPDATE ON oretir_gallery_images
  FOR EACH ROW
  EXECUTE FUNCTION public.update_oretir_gallery_images_updated_at();