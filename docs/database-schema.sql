-- HRIS MVP Database Schema for Supabase
-- Complete database setup with all tables, relationships, constraints, and initial data

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Employees Table
CREATE TABLE IF NOT EXISTS employees (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    supabase_user_id UUID UNIQUE NOT NULL, -- Links to Supabase auth.users
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    work_email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),
    department VARCHAR(100),
    position VARCHAR(100),
    employment_status VARCHAR(50) CHECK (employment_status IN ('Regular', 'Probationary', 'Contractual', 'Part-time')),
    date_hired DATE,
    manager_id UUID REFERENCES employees(id), -- For leave approval hierarchy
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 2. Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    supabase_user_id UUID UNIQUE NOT NULL, -- Links to Supabase auth.users
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'admin' CHECK (role IN ('admin', 'hr_manager', 'super_admin')),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 3. Leave Types Table
CREATE TABLE IF NOT EXISTS leave_types (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    days_allowed INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- 4. Work Calendar Table
CREATE TABLE IF NOT EXISTS work_calendar (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    is_working_day BOOLEAN DEFAULT true,
    description VARCHAR(255), -- Holiday name or special day description
    created_at TIMESTAMP DEFAULT NOW()
);

-- 5. Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    time_in TIMESTAMP,
    time_out TIMESTAMP,
    status VARCHAR(20) CHECK (status IN ('Present', 'Late', 'Absent', 'Half-day')),
    work_hours DECIMAL(4,2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(employee_id, date)
);

-- 6. Leave Credits Table
CREATE TABLE IF NOT EXISTS leave_credits (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    leave_type_id UUID REFERENCES leave_types(id),
    total_credits INT DEFAULT 0,
    used_credits INT DEFAULT 0,
    remaining_credits INT GENERATED ALWAYS AS (total_credits - used_credits) STORED,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(employee_id, leave_type_id, year)
);

-- 7. Leave Requests Table
CREATE TABLE IF NOT EXISTS leave_requests (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    leave_type_id UUID REFERENCES leave_types(id),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(3,1) NOT NULL, -- Allow half days (0.5)
    reason TEXT,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Denied', 'Cancelled')),
    reviewed_by UUID REFERENCES employees(id),
    reviewed_at TIMESTAMP,
    denial_reason TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT no_self_approval CHECK (employee_id != reviewed_by),
    CONSTRAINT valid_date_range CHECK (end_date >= start_date)
);

-- 8. Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    posted_by UUID REFERENCES admins(id), -- Only admins can post announcements
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 9. Leave Credit Audit Table
CREATE TABLE IF NOT EXISTS leave_credit_audit (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id),
    leave_type_id UUID REFERENCES leave_types(id),
    action VARCHAR(50) NOT NULL, -- 'ALLOCATED', 'ADJUSTED', 'USED', 'RESTORED'
    previous_total INT,
    new_total INT,
    previous_used INT,
    new_used INT,
    reason TEXT,
    performed_by UUID REFERENCES employees(id),
    created_at TIMESTAMP DEFAULT NOW()
);

-- 10. System Audit Log Table
CREATE TABLE IF NOT EXISTS system_audit_log (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID, -- Can reference either employees(id) or admins(id)
    user_type VARCHAR(20) CHECK (user_type IN ('employee', 'admin')),
    action VARCHAR(100) NOT NULL, -- 'LOGIN', 'LOGOUT', 'CREATE_EMPLOYEE', 'UPDATE_ATTENDANCE', etc.
    table_name VARCHAR(50),
    record_id UUID,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- 11. User Sessions Table (Optional - for additional session tracking)
CREATE TABLE IF NOT EXISTS user_sessions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    supabase_user_id UUID NOT NULL, -- Links to Supabase auth.users
    user_type VARCHAR(20) NOT NULL CHECK (user_type IN ('employee', 'admin')),
    ip_address INET,
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Create Indexes for Performance
CREATE INDEX IF NOT EXISTS idx_employees_supabase_user_id ON employees(supabase_user_id);
CREATE INDEX IF NOT EXISTS idx_employees_employee_id ON employees(employee_id);
CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department);
CREATE INDEX IF NOT EXISTS idx_employees_is_active ON employees(is_active);

CREATE INDEX IF NOT EXISTS idx_admins_supabase_user_id ON admins(supabase_user_id);
CREATE INDEX IF NOT EXISTS idx_admins_email ON admins(email);
CREATE INDEX IF NOT EXISTS idx_admins_is_active ON admins(is_active);

CREATE INDEX IF NOT EXISTS idx_attendance_employee_date ON attendance(employee_id, date);
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);
CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance(status);

CREATE INDEX IF NOT EXISTS idx_leave_requests_employee ON leave_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_requests_status ON leave_requests(status);
CREATE INDEX IF NOT EXISTS idx_leave_requests_dates ON leave_requests(start_date, end_date);

CREATE INDEX IF NOT EXISTS idx_leave_credits_employee_year ON leave_credits(employee_id, year);
CREATE INDEX IF NOT EXISTS idx_leave_credits_employee_type ON leave_credits(employee_id, leave_type_id);

CREATE INDEX IF NOT EXISTS idx_work_calendar_date ON work_calendar(date);
CREATE INDEX IF NOT EXISTS idx_work_calendar_working_day ON work_calendar(is_working_day);

CREATE INDEX IF NOT EXISTS idx_announcements_active ON announcements(is_active);
CREATE INDEX IF NOT EXISTS idx_announcements_created_at ON announcements(created_at);

CREATE INDEX IF NOT EXISTS idx_leave_credit_audit_employee ON leave_credit_audit(employee_id);
CREATE INDEX IF NOT EXISTS idx_system_audit_log_user ON system_audit_log(user_id, user_type);
CREATE INDEX IF NOT EXISTS idx_system_audit_log_created_at ON system_audit_log(created_at);

-- Initialize Default Leave Types
INSERT INTO leave_types (name, days_allowed) VALUES
    ('Annual Leave', 15),
    ('Sick Leave', 10),
    ('Emergency Leave', 5),
    ('Maternity/Paternity Leave', 60),
    ('Bereavement Leave', 3)
ON CONFLICT DO NOTHING;

-- Initialize Work Calendar with Basic Working Days (2024-2026)
-- This populates working days (Monday-Friday) and marks weekends as non-working days
INSERT INTO work_calendar (date, is_working_day, description)
SELECT 
    generate_series::date,
    CASE 
        WHEN EXTRACT(DOW FROM generate_series) IN (0, 6) THEN false -- Sunday=0, Saturday=6
        ELSE true 
    END,
    CASE 
        WHEN EXTRACT(DOW FROM generate_series) = 0 THEN 'Sunday'
        WHEN EXTRACT(DOW FROM generate_series) = 6 THEN 'Saturday'
        ELSE NULL
    END
FROM generate_series(
    '2024-01-01'::date,
    '2026-12-31'::date,
    INTERVAL '1 day'
)
ON CONFLICT (date) DO NOTHING;

-- Add Common Holidays (US holidays - customize for your region)
INSERT INTO work_calendar (date, is_working_day, description) VALUES
    -- 2024 Holidays
    ('2024-01-01', false, 'New Year''s Day'),
    ('2024-07-04', false, 'Independence Day'),
    ('2024-12-25', false, 'Christmas Day'),
    
    -- 2025 Holidays
    ('2025-01-01', false, 'New Year''s Day'),
    ('2025-07-04', false, 'Independence Day'),
    ('2025-12-25', false, 'Christmas Day'),
    
    -- 2026 Holidays
    ('2026-01-01', false, 'New Year''s Day'),
    ('2026-07-04', false, 'Independence Day'),
    ('2026-12-25', false, 'Christmas Day')
ON CONFLICT (date) DO UPDATE SET
    is_working_day = EXCLUDED.is_working_day,
    description = EXCLUDED.description;

-- Create Functions for Business Logic

-- Function to calculate business days between two dates
CREATE OR REPLACE FUNCTION calculate_business_days(start_date DATE, end_date DATE)
RETURNS INTEGER AS $$
DECLARE
    business_days INTEGER := 0;
    check_date DATE := start_date;
BEGIN
    WHILE check_date <= end_date LOOP
        -- Check if it's a working day
        IF EXISTS (
            SELECT 1 FROM work_calendar 
            WHERE date = check_date AND is_working_day = true
        ) THEN
            business_days := business_days + 1;
        END IF;
        check_date := check_date + INTERVAL '1 day';
    END LOOP;
    
    RETURN business_days;
END;
$$ LANGUAGE plpgsql;

-- Function to automatically initialize leave credits for new employees
CREATE OR REPLACE FUNCTION initialize_employee_leave_credits()
RETURNS TRIGGER AS $$
DECLARE
    leave_type_record RECORD;
    current_year INTEGER := EXTRACT(YEAR FROM CURRENT_DATE);
BEGIN
    -- Initialize leave credits for all leave types for the current year
    FOR leave_type_record IN SELECT id, days_allowed FROM leave_types LOOP
        INSERT INTO leave_credits (employee_id, leave_type_id, total_credits, used_credits, year)
        VALUES (NEW.id, leave_type_record.id, leave_type_record.days_allowed, 0, current_year);
    END LOOP;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to automatically initialize leave credits for new employees
CREATE TRIGGER trigger_initialize_leave_credits
    AFTER INSERT ON employees
    FOR EACH ROW
    EXECUTE FUNCTION initialize_employee_leave_credits();

-- Function to update leave credits when leave is approved
CREATE OR REPLACE FUNCTION update_leave_credits_on_approval()
RETURNS TRIGGER AS $$
BEGIN
    -- Only process if status changed to 'Approved'
    IF NEW.status = 'Approved' AND (OLD.status IS NULL OR OLD.status != 'Approved') THEN
        -- Deduct leave days from credits
        UPDATE leave_credits 
        SET used_credits = used_credits + NEW.total_days,
            updated_at = NOW()
        WHERE employee_id = NEW.employee_id 
          AND leave_type_id = NEW.leave_type_id 
          AND year = EXTRACT(YEAR FROM NEW.start_date);
          
        -- Log the credit usage
        INSERT INTO leave_credit_audit (
            employee_id, leave_type_id, action, previous_used, new_used, 
            reason, performed_by
        )
        SELECT 
            NEW.employee_id, 
            NEW.leave_type_id, 
            'USED', 
            lc.used_credits - NEW.total_days, 
            lc.used_credits,
            'Leave request approved: ' || NEW.id,
            NEW.reviewed_by
        FROM leave_credits lc
        WHERE lc.employee_id = NEW.employee_id 
          AND lc.leave_type_id = NEW.leave_type_id 
          AND lc.year = EXTRACT(YEAR FROM NEW.start_date);
    END IF;
    
    -- If status changed from 'Approved' to something else, restore credits
    IF OLD.status = 'Approved' AND NEW.status != 'Approved' THEN
        UPDATE leave_credits 
        SET used_credits = used_credits - NEW.total_days,
            updated_at = NOW()
        WHERE employee_id = NEW.employee_id 
          AND leave_type_id = NEW.leave_type_id 
          AND year = EXTRACT(YEAR FROM NEW.start_date);
          
        -- Log the credit restoration
        INSERT INTO leave_credit_audit (
            employee_id, leave_type_id, action, previous_used, new_used, 
            reason, performed_by
        )
        SELECT 
            NEW.employee_id, 
            NEW.leave_type_id, 
            'RESTORED', 
            lc.used_credits + NEW.total_days, 
            lc.used_credits,
            'Leave request status changed: ' || NEW.id,
            NEW.reviewed_by
        FROM leave_credits lc
        WHERE lc.employee_id = NEW.employee_id 
          AND lc.leave_type_id = NEW.leave_type_id 
          AND lc.year = EXTRACT(YEAR FROM NEW.start_date);
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to update leave credits on leave approval/denial
CREATE TRIGGER trigger_update_leave_credits
    AFTER UPDATE ON leave_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_leave_credits_on_approval();

-- Function to automatically calculate work hours
CREATE OR REPLACE FUNCTION calculate_work_hours()
RETURNS TRIGGER AS $$
BEGIN
    -- Calculate work hours if both time_in and time_out are present
    IF NEW.time_in IS NOT NULL AND NEW.time_out IS NOT NULL THEN
        NEW.work_hours := EXTRACT(EPOCH FROM (NEW.time_out - NEW.time_in)) / 3600.0;
    END IF;
    
    -- Auto-determine status based on time_in
    IF NEW.time_in IS NOT NULL AND NEW.status IS NULL THEN
        -- Late if time_in is after 9:00 AM
        IF EXTRACT(HOUR FROM NEW.time_in) >= 9 THEN
            NEW.status := 'Late';
        ELSE
            NEW.status := 'Present';
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to automatically calculate work hours and status
CREATE TRIGGER trigger_calculate_work_hours
    BEFORE INSERT OR UPDATE ON attendance
    FOR EACH ROW
    EXECUTE FUNCTION calculate_work_hours();

-- Function to update timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers to automatically update updated_at columns
CREATE TRIGGER trigger_employees_updated_at
    BEFORE UPDATE ON employees
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_admins_updated_at
    BEFORE UPDATE ON admins
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_leave_requests_updated_at
    BEFORE UPDATE ON leave_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_leave_credits_updated_at
    BEFORE UPDATE ON leave_credits
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trigger_announcements_updated_at
    BEFORE UPDATE ON announcements
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Create Views for Common Queries

-- View for employee details with leave balances
CREATE OR REPLACE VIEW employee_dashboard AS
SELECT 
    e.id,
    e.employee_id,
    e.first_name,
    e.last_name,
    e.work_email,
    e.department,
    e.position,
    e.employment_status,
    e.date_hired,
    e.is_active,
    COALESCE(SUM(lc.remaining_credits), 0) as total_leave_balance,
    COUNT(DISTINCT lr.id) FILTER (WHERE lr.status = 'Pending') as pending_requests
FROM employees e
LEFT JOIN leave_credits lc ON e.id = lc.employee_id AND lc.year = EXTRACT(YEAR FROM CURRENT_DATE)
LEFT JOIN leave_requests lr ON e.id = lr.employee_id AND lr.status = 'Pending'
WHERE e.is_active = true
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, e.work_email, 
         e.department, e.position, e.employment_status, e.date_hired, e.is_active;

-- View for attendance summary
CREATE OR REPLACE VIEW attendance_summary AS
SELECT 
    e.id as employee_uuid,
    e.employee_id,
    e.first_name,
    e.last_name,
    e.department,
    COUNT(a.id) as total_days,
    COUNT(a.id) FILTER (WHERE a.status = 'Present') as present_days,
    COUNT(a.id) FILTER (WHERE a.status = 'Late') as late_days,
    COUNT(a.id) FILTER (WHERE a.status = 'Absent') as absent_days,
    ROUND(
        (COUNT(a.id) FILTER (WHERE a.status IN ('Present', 'Late'))::DECIMAL / 
         NULLIF(COUNT(a.id), 0)) * 100, 2
    ) as attendance_percentage
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id 
    AND a.date >= DATE_TRUNC('month', CURRENT_DATE)
WHERE e.is_active = true
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, e.department;

-- Grant necessary permissions (adjust as needed for your Supabase setup)
-- Note: Supabase handles most permissions through RLS policies

-- Enable Row Level Security (RLS) for sensitive tables
ALTER TABLE employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE admins ENABLE ROW LEVEL SECURITY;
ALTER TABLE attendance ENABLE ROW LEVEL SECURITY;
ALTER TABLE leave_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE leave_credits ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcements ENABLE ROW LEVEL SECURITY;

-- Basic RLS Policies (customize based on your security requirements)
-- These are examples - adjust according to your specific needs

-- Employees can only see their own data
CREATE POLICY "Employees can view own data" ON employees
    FOR SELECT USING (supabase_user_id = auth.uid());

-- Admins can see all employee data
CREATE POLICY "Admins can view all employees" ON employees
    FOR ALL USING (
        EXISTS (
            SELECT 1 FROM admins 
            WHERE supabase_user_id = auth.uid() AND is_active = true
        )
    );

-- Similar policies for other tables...
-- (Add more RLS policies as needed for your security requirements)

-- Insert sample data for testing (optional - remove in production)
-- This is commented out by default - uncomment if you want sample data

/*
-- Sample Admin User (you'll need to create the corresponding Supabase auth user)
INSERT INTO admins (supabase_user_id, name, email, role) VALUES
    ('00000000-0000-0000-0000-000000000001', 'System Administrator', 'admin@company.com', 'admin');

-- Sample Employee (you'll need to create the corresponding Supabase auth user)
INSERT INTO employees (employee_id, supabase_user_id, first_name, last_name, work_email, department, position, employment_status, date_hired) VALUES
    ('EMP001', '00000000-0000-0000-0000-000000000002', 'John', 'Doe', 'employee@company.com', 'IT', 'Software Developer', 'Regular', '2024-01-15');

-- Sample Announcement
INSERT INTO announcements (title, content, posted_by) VALUES
    ('Welcome to HRIS MVP', 'Welcome to our new Human Resource Information System. Please update your profile information.', 
     (SELECT id FROM admins WHERE email = 'admin@company.com' LIMIT 1));
*/

-- Database setup complete
-- Remember to:
-- 1. Create corresponding Supabase Auth users for admin and employee accounts
-- 2. Update the supabase_user_id fields with actual Supabase user IDs
-- 3. Customize RLS policies according to your security requirements
-- 4. Add any additional holidays or working day exceptions to work_calendar table