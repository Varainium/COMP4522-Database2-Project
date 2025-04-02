-- Insert sample staff data
INSERT INTO staff (first_name, last_name, phone, email, department) VALUES
('Emily', 'Clark', '403-123-4567', 'emily@gmail.com','Practitioner'),
('James', 'Anderson', '403-234-5678', 'james@gmail.com','Practitioner'),
('Sarah', 'Johnson', '403-345-6789', 'Sarah@gmail.com','Admin'),
('Michael', 'Brown', '403-456-7890', 'Michael@gmail.com','Practitioner'),
('Laura', 'Wilson', '403-567-8901', 'Laura@gmail.com','Admin'),
('Anna', 'Taylor', '403-678-9012', 'anna@gmail.com', 'Admin'),
('Robert', 'Hill', '403-789-0123', 'robert@gmail.com', 'Practitioner'),
('Sophia', 'White', '403-890-1234', 'sophia@gmail.com', 'Practitioner');

-- Insert sample patient data
INSERT INTO patients (first_name, last_name, insurance_provider) VALUES 
('John', 'Doe', 'Blue Cross'),
('Jane', 'Smith', 'Manulife'),
('Robert', 'Taylor', NULL),
('Emily', 'Clark', 'Sun Life'),
('Michael', 'Brown', NULL),
('Alice', 'Johnson', 'Cigna'),
('Charlie', 'Williams', 'Sun Life'),
('David', 'Brown', NULL);

-- Insert sample appointment data
INSERT INTO appointments (patient_id, practitioner_id, appointment_date, appointment_time, appointment_type, reason, status) VALUES
(1, 1, '2024-04-01', '09:00', 'Scheduled', 'Routine checkup', 'Completed'),
(2, 2, '2024-04-02', '10:00', 'Walk-In', 'Flu symptoms', 'Completed'),
(3, 3, '2024-04-02', '11:30', 'Scheduled', 'Consultation', 'Completed'),
(4, 4, '2024-04-03', '12:00', 'Walk-In', 'Blood Pressure Check', 'Completed'),
(5, 5, '2024-04-03', '14:30', 'Scheduled', 'Skin Check', 'Completed');

-- Insert sample billing statements data
INSERT INTO billing_statement (appointment_id, practitioner_id, statement_date, total_fee, insurance_paid, patient_due, payment_method) VALUES 
(1, 1, '2024-04-01', 150.00, 100.00, 50.00, 'Insurance'),
(2, 2, '2024-04-02', 200.00, 0.00, 200.00, 'Out-of-Pocket'),
(3, 3, '2024-04-02', 180.00, 0.00, 180.00, 'Out-of-Pocket'),
(4, 4, '2024-04-03', 250.00, 150.00, 100.00, 'Insurance'),
(5, 5, '2024-04-03', 120.00, 0.00, 120.00, 'Out-of-Pocket');

-- Insert sample master schedule data
INSERT INTO daily_master_schedule (date, staff_id, shift_start_time, shift_end_time, appointment_slots, walk_in_availability) VALUES 
('2024-04-01', 1, '08:00', '16:00', 10, 1),
('2024-04-01', 2, '09:00', '17:00', 8, 0),
('2024-04-02', 3, '07:30', '15:30', 12, 1),
('2024-04-02', 4, '10:00', '18:00', 6, 1),
('2024-04-03', 5, '11:00', '19:00', 5, 0),
('2024-04-03', 7, '08:00', '14:00', 7, 1),
('2024-04-03', 8, '14:00', '20:00', 8, 0),
('2024-04-04', 1, '09:00', '17:00', 10, 1);

-- Sample Prescription Data
INSERT INTO prescriptions (patient_id, practitioner_id, drug_name, quantity, refill, instructions, date_prescribed)
VALUES
(1, 1, 'Amoxicillin', 30, 2, 'Take one tablet daily after meals', '2025-03-01'),
(2, 2, 'Ibuprofen', 20, 1, 'Take two tablets daily', '2025-03-02'),
(3, 3, 'Metformin', 60, 3, 'Take one tablet before breakfast', '2025-03-03'),
(4, 4, 'Prednisone', 15, 0, 'Take one tablet in the morning', '2025-03-04'),
(5, 5, 'Atorvastatin', 40, 2, 'Take one tablet before bedtime', '2025-03-05'),
(1, 1, 'Ciprofloxacin', 14, 0, 'Take two tablets daily for one week', '2025-03-06'),
(2, 2, 'Paracetamol', 10, 1, 'Take one tablet every 6 hours', '2025-03-07');

-- Insert sample delivery logs data
INSERT INTO daily_delivery_log (patient_id, practitioner_id, delivery_type, delivery_results, delivery_date, delivery_time) VALUES
(2, 2, 'Natural', 'Healthy baby', '2024-04-02', '11:00'),
(3, 3, 'C-Section', 'Healthy baby', '2024-04-03', '10:00'),
(4, 4, 'Natural', 'Healthy baby', '2024-04-03', '12:30'),
(5, 5, 'Natural', 'Healthy baby', '2024-04-03', '13:00');

-- Insert sample lab logs data
INSERT INTO daily_lab_log (patient_id, practitioner_id, test_type, test_results, test_date, test_time) VALUES
(1, 1, 'Blood Test', 'Normal', '2024-04-01', '09:30'),
(2, 2, 'X-Ray', 'Clear', '2024-04-02', '10:45'),
(3, 3, 'MRI', 'No abnormalities', '2024-04-03', '11:00'),
(4, 4, 'Blood Pressure Test', 'Normal', '2024-04-03', '12:15'),
(5, 5, 'Skin Biopsy', 'Benign', '2024-04-03', '15:00');

-- Insert sample recovery logs data
INSERT INTO recovery_room_log (patient_id, practitioner_id, admission_time, discharge_time, notes, medical_checks, recovery_details) VALUES
(2, 2, '2024-04-02 12:00:00', '2024-04-02 14:00:00', 'Stable', 'Vitals checked', 'Observed after delivery'),
(3, 3, '2024-04-03 10:30:00', '2024-04-03 12:00:00', 'Recovering well', 'Heart rate and blood pressure normal', 'Post C-Section Recovery'),
(4, 4, '2024-04-03 12:45:00', '2024-04-03 14:00:00', 'Stable', 'Blood pressure normal', 'Routine observation after test'),
(5, 5, '2024-04-03 14:30:00', '2024-04-03 16:00:00', 'Stable', 'Skin healing normally', 'Observed after biopsy');