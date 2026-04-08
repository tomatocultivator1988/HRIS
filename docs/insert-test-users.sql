-- ============================================================================
-- HRIS MVP - Insert Test Admin and Employee Records
-- ============================================================================
-- Run this in Supabase SQL Editor after creating auth users
-- ============================================================================

-- Insert Admin Record
INSERT INTO admins (supabase_user_id, name, email, role, is_active) 
VALUES (
    '58c76bb0-3608-47db-b262-8dd9fb6c936e',
    'System Administrator',
    'admin@company.com',
    'admin',
    true
);

-- Insert Employee Record
INSERT INTO employees (
    employee_id,
    supabase_user_id,
    first_name,
    last_name,
    work_email,
    mobile_number,
    department,
    position,
    employment_status,
    date_hired,
    is_active
) VALUES (
    'EMP001',
    'c7b7fb4f-305b-4243-97eb-cdd1b448ba47',
    'Juan',
    'Dela Cruz',
    'employee@company.com',
    '+63 912 345 6789',
    'IT Department',
    'Software Developer',
    'Regular',
    '2024-01-15',
    true
);

-- Verify records were created successfully
SELECT 'Admin User' as record_type, id, name, email, role, is_active, created_at 
FROM admins 
WHERE email = 'admin@company.com'

UNION ALL

SELECT 'Employee User' as record_type, id, 
       first_name || ' ' || last_name as name, 
       work_email as email, 
       position as role, 
       is_active, 
       created_at 
FROM employees 
WHERE work_email = 'employee@company.com';

-- Check leave credits were auto-initialized for employee
SELECT 'Leave Credits' as info, 
       e.employee_id,
       e.first_name || ' ' || e.last_name as employee_name,
       lt.name as leave_type,
       lc.total_credits,
       lc.used_credits,
       lc.remaining_credits,
       lc.year
FROM leave_credits lc
JOIN employees e ON lc.employee_id = e.id
JOIN leave_types lt ON lc.leave_type_id = lt.id
WHERE e.work_email = 'employee@company.com'
ORDER BY lt.name;
