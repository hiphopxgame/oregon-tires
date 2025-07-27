-- Add more upcoming sample appointments for testing
INSERT INTO public.oretir_appointments (
  first_name,
  last_name,
  email,
  phone,
  service,
  preferred_date,
  preferred_time,
  message,
  status,
  language,
  assigned_employee_id,
  created_at
) VALUES
-- Today and tomorrow appointments
('Sarah', 'Wilson', 'sarah.wilson@email.com', '503-555-0123', 'tire-installation', '2025-01-27', '09:00', 'Need four new tires installed', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Mike', 'Chen', 'mike.chen@email.com', '503-555-0124', 'wheel-alignment', '2025-01-27', '14:30', 'Car pulling to the right', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Lisa', 'Anderson', 'lisa.anderson@email.com', '503-555-0125', 'tire-repair', '2025-01-28', '10:00', 'Flat tire repair needed', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('David', 'Kim', 'david.kim@email.com', '503-555-0126', 'tire-balancing', '2025-01-28', '15:00', 'Vibration at highway speeds', 'new', 'en', NULL, now()),

-- This week appointments
('Jennifer', 'Lopez', 'jennifer.lopez@email.com', '503-555-0127', 'new-tires', '2025-01-29', '08:30', 'Looking for budget-friendly tires', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Robert', 'Taylor', 'robert.taylor@email.com', '503-555-0128', 'mobile-service', '2025-01-29', '12:00', 'Mobile tire change at office', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Amanda', 'Brown', 'amanda.brown@email.com', '503-555-0129', 'tire-rotation', '2025-01-30', '11:00', 'Regular maintenance rotation', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Carlos', 'Ramirez', 'carlos.ramirez@email.com', '503-555-0130', 'flat-tire-repair', '2025-01-30', '16:30', 'Pinchazo en llanta trasera', 'new', 'es', NULL, now()),

-- Next week appointments
('Michelle', 'Davis', 'michelle.davis@email.com', '503-555-0131', 'used-tires', '2025-02-03', '09:30', 'Need affordable used tires', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Kevin', 'Johnson', 'kevin.johnson@email.com', '503-555-0132', 'tire-installation', '2025-02-03', '13:00', 'Off-road tire installation', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Tracy', 'Miller', 'tracy.miller@email.com', '503-555-0133', 'wheel-alignment', '2025-02-04', '10:30', 'Alignment after pothole damage', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Jose', 'Martinez', 'jose.martinez@email.com', '503-555-0134', 'tire-balancing', '2025-02-04', '14:00', 'Balanceo de llantas necesario', 'new', 'es', NULL, now()),
('Rebecca', 'Smith', 'rebecca.smith@email.com', '503-555-0135', 'tire-repair', '2025-02-05', '08:00', 'Slow leak in front tire', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Daniel', 'Garcia', 'daniel.garcia@email.com', '503-555-0136', 'mobile-service', '2025-02-05', '11:30', 'Emergency roadside assistance', 'pending', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),

-- Future appointments
('Nicole', 'White', 'nicole.white@email.com', '503-555-0137', 'new-tires', '2025-02-10', '09:00', 'Winter to summer tire change', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Steven', 'Clark', 'steven.clark@email.com', '503-555-0138', 'tire-rotation', '2025-02-10', '15:30', 'Quarterly tire rotation', 'new', 'en', NULL, now()),
('Rachel', 'Adams', 'rachel.adams@email.com', '503-555-0139', 'wheel-alignment', '2025-02-12', '12:00', 'Annual alignment check', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now());