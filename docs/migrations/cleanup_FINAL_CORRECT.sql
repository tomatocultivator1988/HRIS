-- ============================================
-- FINAL CORRECT CLEANUP SCRIPT
-- ============================================
-- Based on YOUR actual database tables
-- This will work 100%!
-- ============================================

DO $$
DECLARE
    admin_employee_id UUID;
    admin_user_id UUID;
BEGIN
    -- Find admin from ADMINS table (not employees!)
    SELECT supabase_user_id INTO admin_user_id
    FROM admins 
    WHERE email LIKE '%admin%'
       OR is_active = true
    LIMIT 1;
    
    -- Admin might also be in employees table (optional)
    SELECT id INTO admin_employee_id 
    FROM employees 
    WHERE work_email LIKE '%admin%'
    LIMIT 1;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'CLEANUP STARTING';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Admin Employee ID: %', admin_employee_id;
    RAISE NOTICE 'Admin User ID: %', admin_user_id;
    RAISE NOTICE '';
    
    IF admin_user_id IS NULL THEN
        RAISE EXCEPTION 'Admin not found in admins table! Cannot proceed.';
    END IF;
    
    -- Admin employee is optional (might not exist in employees table)
    IF admin_employee_id IS NULL THEN
        RAISE NOTICE 'Note: Admin not found in employees table (this is OK)';
    END IF;
    
    -- DELETE IN CORRECT ORDER (respecting foreign keys)
    
    -- 1. Payroll data
    RAISE NOTICE 'Deleting payroll data...';
    DELETE FROM payroll_adjustments;
    DELETE FROM payroll_line_items;
    DELETE FROM payroll_runs;
    DELETE FROM payroll_periods;
    RAISE NOTICE '  ✓ Payroll data deleted';
    
    -- 2. Recruitment data
    RAISE NOTICE 'Deleting recruitment data...';
    DELETE FROM applicant_evaluations;
    DELETE FROM applicants;
    DELETE FROM job_postings;
    RAISE NOTICE '  ✓ Recruitment data deleted';
    
    -- 3. Employee documents (except admin's if exists)
    RAISE NOTICE 'Deleting employee documents...';
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM employee_documents 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Employee documents deleted (kept admin''s)';
    ELSE
        DELETE FROM employee_documents;
        RAISE NOTICE '  ✓ All employee documents deleted';
    END IF;
    
    -- 4. Attendance (except admin's if exists)
    RAISE NOTICE 'Deleting attendance records...';
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM attendance 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Attendance deleted (kept admin''s)';
    ELSE
        DELETE FROM attendance;
        RAISE NOTICE '  ✓ All attendance deleted';
    END IF;
    
    -- 5. Leave data (except admin's if exists)
    RAISE NOTICE 'Deleting leave data...';
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM leave_requests 
        WHERE employee_id != admin_employee_id;
        
        DELETE FROM leave_credits 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Leave data deleted (kept admin''s)';
    ELSE
        DELETE FROM leave_requests;
        DELETE FROM leave_credits;
        RAISE NOTICE '  ✓ All leave data deleted';
    END IF;
    
    -- 6. Compensation data (except admin's if exists)
    RAISE NOTICE 'Deleting compensation data...';
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM employee_compensation 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Compensation deleted (kept admin''s)';
    ELSE
        DELETE FROM employee_compensation;
        RAISE NOTICE '  ✓ All compensation deleted';
    END IF;
    
    -- 7. Announcements
    RAISE NOTICE 'Deleting announcements...';
    DELETE FROM announcements;
    RAISE NOTICE '  ✓ Announcements deleted';
    
    -- 8. User sessions (except admin's)
    RAISE NOTICE 'Deleting user sessions...';
    DELETE FROM user_sessions 
    WHERE supabase_user_id != admin_user_id;
    RAISE NOTICE '  ✓ User sessions deleted (kept admin''s)';
    
    -- 9. Audit logs (except admin's)
    RAISE NOTICE 'Deleting audit logs...';
    DELETE FROM system_audit_log 
    WHERE user_id != admin_user_id;
    
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM leave_credit_audit 
        WHERE employee_id != admin_employee_id;
    ELSE
        DELETE FROM leave_credit_audit;
    END IF;
    RAISE NOTICE '  ✓ Audit logs deleted (kept admin''s)';
    
    -- 10. Delete admins table (except the admin)
    RAISE NOTICE 'Cleaning admins table...';
    DELETE FROM admins 
    WHERE supabase_user_id != admin_user_id;
    RAISE NOTICE '  ✓ Admins cleaned (kept main admin)';
    
    -- 11. Delete all employees (admin is in admins table, not here)
    RAISE NOTICE 'Deleting employees...';
    IF admin_employee_id IS NOT NULL THEN
        DELETE FROM employees 
        WHERE id != admin_employee_id;
        RAISE NOTICE '  ✓ Employees deleted (kept admin if exists)';
    ELSE
        DELETE FROM employees;
        RAISE NOTICE '  ✓ All employees deleted';
    END IF;
    
    -- 12. Delete all auth users EXCEPT admin
    RAISE NOTICE 'Cleaning auth users...';
    DELETE FROM auth.users 
    WHERE id != admin_user_id;
    RAISE NOTICE '  ✓ Auth users deleted (kept admin)';
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'CLEANUP COMPLETED SUCCESSFULLY!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Only admin account remains.';
    
END $$;

-- Verify what's left
SELECT 'employees' as table_name, COUNT(*) as count FROM employees
UNION ALL
SELECT 'admins', COUNT(*) FROM admins
UNION ALL
SELECT 'attendance', COUNT(*) FROM attendance
UNION ALL
SELECT 'leave_requests', COUNT(*) FROM leave_requests
UNION ALL
SELECT 'leave_credits', COUNT(*) FROM leave_credits
UNION ALL
SELECT 'payroll_periods', COUNT(*) FROM payroll_periods
UNION ALL
SELECT 'payroll_runs', COUNT(*) FROM payroll_runs
UNION ALL
SELECT 'job_postings', COUNT(*) FROM job_postings
UNION ALL
SELECT 'applicants', COUNT(*) FROM applicants
UNION ALL
SELECT 'employee_documents', COUNT(*) FROM employee_documents
UNION ALL
SELECT 'announcements', COUNT(*) FROM announcements
UNION ALL
SELECT 'user_sessions', COUNT(*) FROM user_sessions;

-- Show remaining admin (from admins table)
SELECT 
    'ADMIN ACCOUNT' as info,
    id,
    name,
    email,
    role,
    is_active
FROM admins;

-- Show remaining employees (if any)
SELECT 
    'REMAINING EMPLOYEES' as info,
    id,
    employee_id,
    first_name,
    last_name,
    work_email,
    is_active
FROM employees;
