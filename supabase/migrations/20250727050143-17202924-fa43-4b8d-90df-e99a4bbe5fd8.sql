-- Add more upcoming sample appointments for testing
INSERT INTO public.oretir_appointments (
  customer_name,
  customer_email,
  customer_phone,
  service_type,
  vehicle_info,
  preferred_date,
  preferred_time,
  message,
  status,
  assigned_employee_id,
  created_at,
  updated_at
) VALUES
-- Today and tomorrow appointments
('Sarah Wilson', 'sarah.wilson@email.com', '503-555-0123', 'tire-installation', '2022 Honda Civic', '2025-01-27', '09:00', 'Need four new tires installed', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),
('Mike Chen', 'mike.chen@email.com', '503-555-0124', 'wheel-alignment', '2021 Toyota Camry', '2025-01-27', '14:30', 'Car pulling to the right', 'pending', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Lisa Anderson', 'lisa.anderson@email.com', '503-555-0125', 'tire-repair', '2020 Ford F-150', '2025-01-28', '10:00', 'Flat tire repair needed', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),
('David Kim', 'david.kim@email.com', '503-555-0126', 'tire-balancing', '2019 Subaru Outback', '2025-01-28', '15:00', 'Vibration at highway speeds', 'new', NULL, now(), now()),

-- This week appointments
('Jennifer Lopez', 'jennifer.lopez@email.com', '503-555-0127', 'new-tires', '2018 Nissan Altima', '2025-01-29', '08:30', 'Looking for budget-friendly tires', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Robert Taylor', 'robert.taylor@email.com', '503-555-0128', 'mobile-service', '2023 BMW X5', '2025-01-29', '12:00', 'Mobile tire change at office', 'pending', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),
('Amanda Brown', 'amanda.brown@email.com', '503-555-0129', 'tire-rotation', '2021 Mazda CX-5', '2025-01-30', '11:00', 'Regular maintenance rotation', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Carlos Ramirez', 'carlos.ramirez@email.com', '503-555-0130', 'flat-tire-repair', '2020 Chevrolet Silverado', '2025-01-30', '16:30', 'Pinchazo en llanta trasera', 'new', NULL, now(), now()),

-- Next week appointments
('Michelle Davis', 'michelle.davis@email.com', '503-555-0131', 'used-tires', '2017 Honda Accord', '2025-02-03', '09:30', 'Need affordable used tires', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),
('Kevin Johnson', 'kevin.johnson@email.com', '503-555-0132', 'tire-installation', '2022 Jeep Wrangler', '2025-02-03', '13:00', 'Off-road tire installation', 'pending', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Tracy Miller', 'tracy.miller@email.com', '503-555-0133', 'wheel-alignment', '2019 Audi A4', '2025-02-04', '10:30', 'Alignment after pothole damage', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),
('Jose Martinez', 'jose.martinez@email.com', '503-555-0134', 'tire-balancing', '2020 Ford Explorer', '2025-02-04', '14:00', 'Balanceo de llantas necesario', 'new', NULL, now(), now()),
('Rebecca Smith', 'rebecca.smith@email.com', '503-555-0135', 'tire-repair', '2021 Volkswagen Jetta', '2025-02-05', '08:00', 'Slow leak in front tire', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Daniel Garcia', 'daniel.garcia@email.com', '503-555-0136', 'mobile-service', '2018 Tesla Model 3', '2025-02-05', '11:30', 'Emergency roadside assistance', 'pending', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now()),

-- Future appointments
('Nicole White', 'nicole.white@email.com', '503-555-0137', 'new-tires', '2022 Hyundai Elantra', '2025-02-10', '09:00', 'Winter to summer tire change', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now(), now()),
('Steven Clark', 'steven.clark@email.com', '503-555-0138', 'tire-rotation', '2020 Kia Sorento', '2025-02-10', '15:30', 'Quarterly tire rotation', 'new', NULL, now(), now()),
('Rachel Adams', 'rachel.adams@email.com', '503-555-0139', 'wheel-alignment', '2019 Lexus RX', '2025-02-12', '12:00', 'Annual alignment check', 'confirmed', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now(), now());