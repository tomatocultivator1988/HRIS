-- ============================================
-- INSERT FRESH ADMIN ACCOUNT
-- ============================================
-- Run this AFTER resetting your Supabase project
-- This creates a fresh admin account with all necessary data
-- ============================================

-- Step 1: Create admin user in auth.users (Supabase Auth)
-- NOTE: You need to do this in Supabase Dashboard > Authentication > Users
-- Click "Add User" and create:
-- Email: admin@company.com
-- Password: Admin123! (or your preferred password)
-- Confirm Password: Yes
-- 
-- After creating, copy the UUID and use it below

-- Step 2: Insert admin employee record
-- REPLACE 'YOUR-SUPABASE-USER-UUID-HERE' with the actual UUID from Step 1
INSERT INTO employees (
    id,
    supabase_user_id,
    employee_id,
    first_name,
    last_name,
    work_email,
    mobile_number,
    department,
    position,
    employment_status,
    date_hired,
    is_active,
    role
) VALUES (
    gen_random_uuid(),
    'YOUR-SUPABASE-USER-UUID-HERE'::uuid,  -- REPLACE THIS!
    'EMP-0001',
    'System',
    'Administrator',
    'admin@company.com',
    '09123456789',
    'Administration',
    'System Administrator',
    'Regular',
    CURRENT_DATE,
    true,
    'admin'
);

-- Step 3: Get the employee ID we just created
DO $$
DECLARE
    admin_emp_id UUID;
BEGIN
    SELECT id INTO admin_emp_id 
    FROM employees 
    WHERE work_email = 'admin@company.com';
    
    -- Insert default leave types if they don't exist
    INSERT INTO leave_types (name, description, max_days_per_year, requires_approval, is_paid)
    VALUES 
        ('Sick Leave', 'For medical reasons', 15, true, true),
        ('Vacation Leave', 'For rest and recreation', 15, true, true),
        ('Emergency Leave', 'For urgent personal matters', 5, true, true)
    ON CONFLICT (name) DO NOTHING;
    
    -- Insert leave credits for admin
    INSERT INTO leave_credits (employee_id, leave_type_id, total_credits, used_credits, remaining_credits, year)
    SELECT 
        admin_emp_id,
        lt.id,
        lt.max_days_per_year,
        0,
        lt.max_days_per_year,
        EXTRACT(YEAR FROM CURRENT_DATE)
    FROM leave_types lt
    ON CONFLICT (employee_id, leave_type_id, year) DO NOTHING;
    
    -- Insert position salary for admin
    INSERT INTO position_salaries (
        position,
        base_salary,
        payroll_type,
        standard_work_hours_per_day,
        sss_contribution,
        philhealth_contribution,
        pagibig_contribution,
        tax_rate,
        is_active
    ) VALUES (
        'System Administrator',
        50000.00,
        'Monthly',
        8.0,
        1000.00,
        500.00,
        200.00,
        0.15,
        true
    )
    ON CONFLICT (position) DO NOTHING;
    
    RAISE NOTICE 'Admin account created successfully!';
    RAISE NOTICE 'Employee ID: %', admin_emp_id;
    RAISE NOTICE 'Email: admin@company.com';
    RAISE NOTICE 'You can now login with your admin credentials.';
END $$;

-- Verify the admin account
SELECT 
    e.id,
    e.employee_id,
    e.first_name,
    e.last_name,
    e.work_email,
    e.role,
    e.is_active
FROM employees e
WHERE e.work_email = 'admin@company.com';

-- Check leave credits
SELECT 
    lc.employee_id,
    lt.name as leave_type,
    lc.total_credits,
    lc.remaining_credits,
    lc.year
FROM leave_credits lc
JOIN leave_types lt ON lc.leave_type_id = lt.id
JOIN employees e ON lc.employee_id = e.id
WHERE e.work_email = 'admin@company.com';
