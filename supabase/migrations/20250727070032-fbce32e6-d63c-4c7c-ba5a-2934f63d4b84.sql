-- Add more sample appointments for August 2025
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
-- Early August appointments
('Patricia', 'Johnson', 'patricia.johnson@email.com', '503-555-0201', 'tire-installation', '2025-08-01', '09:00', 'Summer tire installation', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Brian', 'Williams', 'brian.williams@email.com', '503-555-0202', 'wheel-alignment', '2025-08-02', '10:30', 'Alignment after road trip', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Laura', 'Martinez', 'laura.martinez@email.com', '503-555-0203', 'tire-repair', '2025-08-05', '14:00', 'Reparación de llanta dañada', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Mark', 'Thompson', 'mark.thompson@email.com', '503-555-0204', 'tire-balancing', '2025-08-06', '11:15', 'Wheel vibration issues', 'new', 'en', NULL, now()),
('Sofia', 'Rodriguez', 'sofia.rodriguez@email.com', '503-555-0205', 'mobile-service', '2025-08-07', '08:30', 'Servicio móvil en casa', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),

-- Mid August appointments  
('Thomas', 'Anderson', 'thomas.anderson@email.com', '503-555-0206', 'new-tires', '2025-08-12', '13:00', 'Performance tires for sports car', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Maria', 'Fernandez', 'maria.fernandez@email.com', '503-555-0207', 'tire-rotation', '2025-08-13', '15:30', 'Rotación regular de llantas', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Christopher', 'Lee', 'christopher.lee@email.com', '503-555-0208', 'flat-tire-repair', '2025-08-14', '09:45', 'Emergency flat tire fix', 'new', 'en', NULL, now()),
('Angela', 'Torres', 'angela.torres@email.com', '503-555-0209', 'used-tires', '2025-08-15', '12:30', 'Looking for budget tires', 'confirmed', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('James', 'Wilson', 'james.wilson@email.com', '503-555-0210', 'wheel-alignment', '2025-08-16', '16:00', 'Post-accident alignment', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),

-- Late August appointments
('Isabella', 'Lopez', 'isabella.lopez@email.com', '503-555-0211', 'tire-installation', '2025-08-19', '10:00', 'Instalación de llantas nuevas', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Ryan', 'Davis', 'ryan.davis@email.com', '503-555-0212', 'tire-balancing', '2025-08-20', '14:30', 'Balancing for smooth ride', 'new', 'en', NULL, now()),
('Carmen', 'Sanchez', 'carmen.sanchez@email.com', '503-555-0213', 'mobile-service', '2025-08-21', '11:00', 'Servicio en el trabajo', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Michael', 'Brown', 'michael.brown@email.com', '503-555-0214', 'tire-repair', '2025-08-22', '08:15', 'Puncture repair needed', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Valeria', 'Morales', 'valeria.morales@email.com', '503-555-0215', 'new-tires', '2025-08-26', '13:45', 'Llantas para SUV familiar', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now()),
('Andrew', 'Miller', 'andrew.miller@email.com', '503-555-0216', 'wheel-alignment', '2025-08-27', '09:30', 'Pre-vacation alignment check', 'new', 'en', NULL, now()),
('Lucia', 'Jimenez', 'lucia.jimenez@email.com', '503-555-0217', 'tire-rotation', '2025-08-28', '15:00', 'Mantenimiento regular', 'confirmed', 'es', (SELECT id FROM public.oretir_employees WHERE name = 'Carlos Rodriguez' LIMIT 1), now()),
('Paul', 'Garcia', 'paul.garcia@email.com', '503-555-0218', 'flat-tire-repair', '2025-08-29', '12:00', 'Quick tire patch', 'pending', 'en', (SELECT id FROM public.oretir_employees WHERE name = 'Maria Gonzalez' LIMIT 1), now());