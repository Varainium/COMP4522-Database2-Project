-- Disable Foreign Key Support for Dropping
PRAGMA foreign_keys = OFF;

-- Drop triggers
DROP TRIGGER IF EXISTS trg_insert_staff;
DROP TRIGGER IF EXISTS trg_update_staff_to_admin;
DROP TRIGGER IF EXISTS trg_update_staff_to_practitioner;
DROP TRIGGER IF EXISTS trg_delete_staff;

-- Drop views
DROP VIEW IF EXISTS patient_monthly_statement;
DROP VIEW IF EXISTS monthly_activity_report;
DROP VIEW IF EXISTS monthly_practitioner_report;
DROP VIEW IF EXISTS daily_master_schedule_view;
DROP VIEW IF EXISTS prescription_view;
DROP VIEW IF EXISTS appointment_view;

-- Drop Tables If They Exist
DROP TABLE IF EXISTS recovery_room_log;
DROP TABLE IF EXISTS daily_delivery_log;
DROP TABLE IF EXISTS daily_lab_log;
DROP TABLE IF EXISTS prescription_receipt;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS billing_items;
DROP TABLE IF EXISTS billing_statement;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS individual_practitioner_schedule;
DROP TABLE IF EXISTS daily_master_schedule;
DROP TABLE IF EXISTS weekly_coverage_schedule;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS practitioners;
DROP TABLE IF EXISTS admins;

-- Enable Foreign Key Support Again
PRAGMA foreign_keys = ON;

-- Create Tables

-- Table: staff
CREATE TABLE IF NOT EXISTS staff (
    staff_id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT NOT NULL,
    department TEXT CHECK (department IN ('Practitioner', 'Admin')) NOT NULL
);

-- Table: practitioners
CREATE TABLE IF NOT EXISTS practitioners (
    practitioner_id INTEGER PRIMARY KEY AUTOINCREMENT,
    staff_id INTEGER UNIQUE NOT NULL,
    specialization TEXT NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
    staff_id INTEGER UNIQUE NOT NULL,
    job_title TEXT,
    work_type TEXT CHECK (work_type IN ('Full-time', 'Part-time')) NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

-- Table: weekly_coverage_schedule
CREATE TABLE IF NOT EXISTS weekly_coverage_schedule (
    week_start_date TEXT NOT NULL,
    staff_id INTEGER NOT NULL,
    shift_start_time TEXT NOT NULL,
    shift_end_time TEXT NOT NULL,
    on_call_status INTEGER DEFAULT 0,
    assigned_role TEXT NOT NULL,
    PRIMARY KEY (week_start_date, staff_id),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

-- Table: daily_master_schedule
CREATE TABLE IF NOT EXISTS daily_master_schedule (
    date TEXT NOT NULL,
    staff_id INTEGER NOT NULL,
    shift_start_time TEXT NOT NULL,
    shift_end_time TEXT NOT NULL,
    appointment_slots INTEGER DEFAULT 0,
    walk_in_availability INTEGER DEFAULT 1,
    PRIMARY KEY (date, staff_id, shift_start_time),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

-- Table: individual_practitioner_schedule
CREATE TABLE IF NOT EXISTS individual_practitioner_schedule (
    date TEXT NOT NULL,
    practitioner_id INTEGER NOT NULL,
    appointment_id INTEGER NOT NULL,
    appointment_time TEXT NOT NULL,
    appointment_type TEXT NOT NULL,
    notes TEXT NULL,
    PRIMARY KEY (date, practitioner_id, appointment_id),
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE
);

-- Table: patient
CREATE TABLE IF NOT EXISTS patients (
    patient_id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    insurance_provider TEXT NULL
);

-- Table: appointment
CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    appointment_date TEXT NOT NULL,
    appointment_time TEXT NOT NULL,
    appointment_type TEXT CHECK (appointment_type IN ('Scheduled', 'Walk-In')) NOT NULL,
    reason TEXT,
    status TEXT CHECK (status IN ('Pending', 'Completed', 'Cancelled')) DEFAULT 'Pending',
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Table: billing_statement
CREATE TABLE IF NOT EXISTS billing_statement (
    statement_id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    statement_date TEXT NOT NULL DEFAULT CURRENT_DATE,
    total_fee REAL NOT NULL,
    insurance_paid REAL DEFAULT 0.00,
    patient_due REAL NOT NULL,
    payment_method TEXT CHECK (payment_method IN ('Out-of-Pocket', 'Insurance', 'Government')) NOT NULL,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Table: billing_items
CREATE TABLE IF NOT EXISTS billing_items (
    item_id INTEGER PRIMARY KEY AUTOINCREMENT,
    statement_id INTEGER NOT NULL,
    item_type TEXT CHECK (item_type IN ('Procedure', 'Diagnosis')) NOT NULL,
    code TEXT NOT NULL,
    description TEXT NOT NULL,
    fee REAL NOT NULL,
    FOREIGN KEY (statement_id) REFERENCES billing_statement(statement_id) ON DELETE CASCADE
);

-- Table: prescription
CREATE TABLE IF NOT EXISTS prescriptions (
    prescription_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    drug_name TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    refill INTEGER NOT NULL,
    instructions TEXT NOT NULL,
    date_prescribed TEXT NOT NULL DEFAULT CURRENT_DATE,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Table: prescription_receipt
CREATE TABLE IF NOT EXISTS prescription_receipt (
    receipt_id INTEGER PRIMARY KEY AUTOINCREMENT,
    prescription_id INTEGER NOT NULL,
    base_price REAL NOT NULL,
    insurance_cover REAL DEFAULT 0.00,
    final_price REAL NOT NULL,
    payment_method TEXT CHECK (payment_method IN ('Out-of-Pocket', 'Insurance', 'Government')) NOT NULL,
    payment_date TEXT NOT NULL DEFAULT CURRENT_DATE,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(prescription_id) ON DELETE CASCADE
);

-- Table: daily_lab_log
CREATE TABLE IF NOT EXISTS daily_lab_log (
    lab_log_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    test_type TEXT NOT NULL,
    test_results TEXT,
    test_date TEXT NOT NULL DEFAULT CURRENT_DATE,
    test_time TEXT NOT NULL DEFAULT CURRENT_TIME,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Table: daily_delivery_log
CREATE TABLE IF NOT EXISTS daily_delivery_log (
    delivery_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    delivery_type TEXT CHECK (delivery_type IN ('Natural', 'C-Section')) NOT NULL,
    delivery_results TEXT,
    delivery_date TEXT NOT NULL DEFAULT CURRENT_DATE,
    delivery_time TEXT NOT NULL DEFAULT CURRENT_TIME,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Table: recovery_room_log
CREATE TABLE IF NOT EXISTS recovery_room_log (
    recovery_id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    practitioner_id INTEGER NOT NULL,
    admission_time TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    discharge_time TEXT NULL,
    notes TEXT,
    medical_checks TEXT,
    recovery_details TEXT,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (practitioner_id) REFERENCES practitioners(practitioner_id) ON DELETE CASCADE
);

-- Create Views

-- Create patient_monthly_statement
CREATE VIEW IF NOT EXISTS patient_monthly_statement AS
SELECT 
    p.patient_id,
    p.first_name,
    p.last_name,
    bs.statement_id,
    bs.statement_date,
    bs.total_fee,
    bs.insurance_paid AS total_paid,
    (bs.total_fee - bs.insurance_paid) AS outstanding_balance
FROM patients p
JOIN appointments a ON p.patient_id = a.patient_id
JOIN billing_statement bs ON a.appointment_id = bs.appointment_id
GROUP BY p.patient_id, bs.statement_id;

-- Create monthly_activity_report
CREATE VIEW IF NOT EXISTS monthly_activity_report AS
SELECT 
    strftime('%Y-%m', a.appointment_date) AS month_year,
    COUNT(DISTINCT a.appointment_id) AS total_appointments,
    COUNT(DISTINCT dl.delivery_id) AS total_deliveries,
    COUNT(DISTINCT ll.lab_log_id) AS total_lab_tests,
    COUNT(DISTINCT rv.recovery_id) AS total_recoveries,
    AVG((julianday(rv.discharge_time) - julianday(a.appointment_date || ' ' || a.appointment_time)) * 1440) AS avg_appointment_duration -- Convert days to minutes
FROM appointments a
LEFT JOIN daily_delivery_log dl 
    ON a.patient_id = dl.patient_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', dl.delivery_date)
LEFT JOIN daily_lab_log ll 
    ON a.patient_id = ll.patient_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', ll.test_date)
LEFT JOIN recovery_room_log rv 
    ON a.patient_id = rv.patient_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', rv.admission_time)
GROUP BY month_year;

-- Create monthly_ractitioner_report
CREATE VIEW IF NOT EXISTS monthly_practitioner_report AS
SELECT 
    strftime('%Y-%m', a.appointment_date) AS month_year,
    p.practitioner_id,
    s.first_name || ' ' || s.last_name AS practitioner_name,
    COUNT(DISTINCT a.appointment_id) AS total_appointments,
    COUNT(DISTINCT pr.prescription_id) AS total_prescriptions,
    COUNT(DISTINCT ll.lab_log_id) AS total_lab_tests,
    COUNT(DISTINCT dl.delivery_id) AS total_deliveries,
    COUNT(DISTINCT rr.recovery_id) AS total_recoveries,
    COALESCE(SUM(bs.total_fee), 0) AS total_revenue,
    COALESCE(AVG((julianday(rr.discharge_time) - julianday(rr.admission_time)) * 1440), 0) AS avg_appointment_duration
FROM practitioners p
JOIN staff s ON p.staff_id = s.staff_id
LEFT JOIN appointments a ON p.practitioner_id = a.practitioner_id
LEFT JOIN billing_statement bs ON a.appointment_id = bs.appointment_id
LEFT JOIN prescriptions pr ON p.practitioner_id = pr.practitioner_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', pr.date_prescribed)
LEFT JOIN daily_lab_log ll ON p.practitioner_id = ll.practitioner_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', ll.test_date)
LEFT JOIN daily_delivery_log dl ON p.practitioner_id = dl.practitioner_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', dl.delivery_date)
LEFT JOIN recovery_room_log rr ON p.practitioner_id = rr.practitioner_id AND strftime('%Y-%m', a.appointment_date) = strftime('%Y-%m', rr.admission_time)
GROUP BY month_year, p.practitioner_id, s.first_name, s.last_name;


-- Create daily_master_schedule_view
CREATE VIEW IF NOT EXISTS daily_master_schedule_view AS
SELECT 
    dms.date, 
    dms.staff_id, 
    s.first_name, 
    s.last_name, 
    dms.shift_start_time, 
    dms.shift_end_time, 
    dms.appointment_slots, 
    dms.walk_in_availability
FROM daily_master_schedule AS dms
JOIN staff AS s ON dms.staff_id = s.staff_id;

-- Create prescription_view
CREATE VIEW IF NOT EXISTS prescription_view AS
SELECT 
    pr.prescription_id,
    pr.patient_id,
    p.first_name AS patient_first_name,
    p.last_name AS patient_last_name,
    pr.practitioner_id,
    s.first_name AS practitioner_first_name,
    s.last_name AS practitioner_last_name,
    pr.drug_name,
    pr.quantity,
    pr.refill,
    pr.instructions,
    pr.date_prescribed
FROM prescriptions pr
JOIN patients p ON pr.patient_id = p.patient_id
JOIN practitioners prac ON pr.practitioner_id = prac.practitioner_id
JOIN staff s ON prac.staff_id = s.staff_id;

-- Create appointment_view
CREATE VIEW IF NOT EXISTS appointment_view AS
SELECT a.appointment_id, 
       a.patient_id, 
       a.practitioner_id, 
       a.appointment_date, 
       a.appointment_time, 
       a.appointment_type, 
       a.reason, 
       a.status, 
       p.first_name || ' ' || p.last_name AS patient_name, 
       s.first_name || ' ' || s.last_name AS practitioner_name 
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN staff s ON a.practitioner_id = s.staff_id;

-- Create Triggers

-- Insert Staff Trigger
CREATE TRIGGER IF NOT EXISTS trg_insert_staff
AFTER INSERT ON staff
BEGIN
    -- Insert into Admins if department is 'Admin'
    INSERT INTO admins (staff_id, job_title, work_type)
    SELECT NEW.staff_id, 'Admin', 'Full-time'
    WHERE NEW.department = 'Admin';

    -- Insert into Practitioners if department is 'Practitioner'
    INSERT INTO practitioners (staff_id, specialization)
    SELECT NEW.staff_id, 'General Practitioner'
    WHERE NEW.department = 'Practitioner';
END;

-- Update Admin Trigger
CREATE TRIGGER IF NOT EXISTS trg_update_staff_to_admin
AFTER UPDATE ON staff
WHEN NEW.department = 'Admin'
BEGIN
    DELETE FROM practitioners WHERE staff_id = NEW.staff_id;

    INSERT INTO admins (staff_id, job_title, work_type)
    VALUES (NEW.staff_id, 'Admin', 'Full-time');
END;

-- Update Practitioner Trigger
CREATE TRIGGER IF NOT EXISTS trg_update_staff_to_practitioner
AFTER UPDATE ON staff
WHEN NEW.department = 'Practitioner'
BEGIN
    DELETE FROM admins WHERE staff_id = NEW.staff_id;

    INSERT INTO practitioners (staff_id, specialization)
    VALUES (NEW.staff_id, 'General Practitioner');
END;

-- Delete Staff Trigger
CREATE TRIGGER IF NOT EXISTS trg_delete_staff
AFTER DELETE ON staff
BEGIN
    DELETE FROM admins WHERE staff_id = OLD.staff_id;
    DELETE FROM practitioners WHERE staff_id = OLD.staff_id;
END;