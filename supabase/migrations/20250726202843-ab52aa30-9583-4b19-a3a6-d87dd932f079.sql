-- Insert sample appointments with various states for testing
INSERT INTO oretir_appointments (
    first_name, last_name, email, phone, service, preferred_date, preferred_time,
    message, language, status, assigned_employee_id, started_at, completed_at, actual_duration_minutes,
    tire_size, license_plate, service_location, customer_address, customer_city, customer_state, customer_zip
) VALUES 
-- New appointments (unassigned)
('John', 'Smith', 'john.smith@email.com', '503-555-0101', 'tire-repair', '2025-01-28', '09:00:00', 'Need tire repair urgently', 'english', 'new', NULL, NULL, NULL, NULL, '225/65R17', 'ABC123', 'mobile', '123 Main St', 'Portland', 'OR', '97201'),
('Maria', 'Garcia', 'maria.garcia@email.com', '503-555-0102', 'new-tires', '2025-01-29', '10:30:00', 'Necesito llantas nuevas', 'spanish', 'new', NULL, NULL, NULL, NULL, '195/60R15', 'XYZ789', 'shop', NULL, NULL, NULL, NULL),

-- Pending appointments (assigned but not confirmed)
('David', 'Johnson', 'david.j@email.com', '503-555-0103', 'tire-installation', '2025-01-28', '14:00:00', 'Install winter tires', 'english', 'pending', (SELECT id FROM oretir_employees LIMIT 1), NULL, NULL, NULL, '215/55R16', 'DEF456', 'shop', NULL, NULL, NULL, NULL),
('Ana', 'Rodriguez', 'ana.r@email.com', '503-555-0104', 'tire-rotation', '2025-01-29', '11:00:00', 'Rotación de llantas programada', 'spanish', 'pending', (SELECT id FROM oretir_employees LIMIT 1), NULL, NULL, NULL, '205/70R15', 'GHI789', 'mobile', '456 Oak Ave', 'Portland', 'OR', '97202'),

-- Confirmed appointments (assigned and confirmed)
('Michael', 'Brown', 'michael.brown@email.com', '503-555-0105', 'flat-tire-repair', '2025-01-28', '16:30:00', 'Flat tire on front left', 'english', 'confirmed', (SELECT id FROM oretir_employees LIMIT 1), NULL, NULL, NULL, '225/60R16', 'JKL012', 'mobile', '789 Pine St', 'Portland', 'OR', '97203'),
('Carmen', 'Lopez', 'carmen.lopez@email.com', '503-555-0106', 'tire-balancing', '2025-01-29', '13:15:00', 'Balanceo de ruedas', 'spanish', 'confirmed', (SELECT id FROM oretir_employees OFFSET 1 LIMIT 1), NULL, NULL, NULL, '185/65R15', 'MNO345', 'shop', NULL, NULL, NULL, NULL),

-- In-progress appointments (started but not completed)
('Robert', 'Wilson', 'robert.w@email.com', '503-555-0107', 'wheel-alignment', '2025-01-27', '15:00:00', 'Car pulls to the right', 'english', 'confirmed', (SELECT id FROM oretir_employees LIMIT 1), NOW() - INTERVAL '45 minutes', NULL, NULL, '235/55R17', 'PQR678', 'shop', NULL, NULL, NULL, NULL),
('Sofia', 'Martinez', 'sofia.m@email.com', '503-555-0108', 'mobile-service', '2025-01-27', '12:00:00', 'Servicio móvil en oficina', 'spanish', 'confirmed', (SELECT id FROM oretir_employees OFFSET 1 LIMIT 1), NOW() - INTERVAL '30 minutes', NULL, NULL, '215/60R16', 'STU901', 'mobile', '321 Business Blvd', 'Portland', 'OR', '97204'),

-- Recently completed appointments
('James', 'Davis', 'james.davis@email.com', '503-555-0109', 'used-tires', '2025-01-26', '10:00:00', 'Looking for affordable used tires', 'english', 'completed', (SELECT id FROM oretir_employees LIMIT 1), NOW() - INTERVAL '2 hours', NOW() - INTERVAL '1 hour', 60, '205/65R16', 'VWX234', 'shop', NULL, NULL, NULL, NULL),
('Isabella', 'Hernandez', 'isabella.h@email.com', '503-555-0110', 'tire-repair', '2025-01-26', '14:30:00', 'Reparación de llanta pinchada', 'spanish', 'completed', (SELECT id FROM oretir_employees OFFSET 1 LIMIT 1), NOW() - INTERVAL '4 hours', NOW() - INTERVAL '3 hours 30 minutes', 30, '195/70R14', 'YZA567', 'mobile', '654 Elm St', 'Portland', 'OR', '97205'),

-- Cancelled appointments
('William', 'Miller', 'william.m@email.com', '503-555-0111', 'new-tires', '2025-01-25', '09:30:00', 'Changed mind about tire purchase', 'english', 'cancelled', NULL, NULL, NULL, NULL, '225/65R17', 'BCD890', 'shop', NULL, NULL, NULL, NULL),
('Lucia', 'Gonzalez', 'lucia.g@email.com', '503-555-0112', 'tire-installation', '2025-01-25', '16:00:00', 'Cancelada por cliente', 'spanish', 'cancelled', (SELECT id FROM oretir_employees LIMIT 1), NULL, NULL, NULL, '215/55R17', 'EFG123', 'mobile', '987 Cedar Ave', 'Portland', 'OR', '97206');