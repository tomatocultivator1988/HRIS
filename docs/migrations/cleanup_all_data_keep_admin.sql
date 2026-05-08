-- ============================================
-- CLEANUP ALL DATA BUT KEEP ADMIN ACCOUNT
-- ============================================
-- This script deletes ALL test/demo data but preserves the admin account
-- Run this in Supabase SQL Editor before going to production
-- 
-- IMPORTANT: This will delete ALL data except the admin user!
-- Make sure you have a backup if needed.
-- ============================================

-- Step 1: Get the admin employee ID (change email if different)
DO $$
DECLARE
    admin_employee_id UUID;
    admin_user_id UUID;
BEGIN
    -- Find admin employee (adjust email if needed)
    SELECT id INTO admin_employee_id 
    FROM employees 
    WHERE work_email = 'admin@company.com' 
    LIMIT 1;
    
    -- Find admin user in auth.users
    SELECT id INTO admin_user_id
    FROM auth.users
    WHERE email = 'admin@company.com'
    LIMIT 1;
    
    RAISE NOTICE 'Admin Employee ID: %', admin_employee_id;
    RAISE NOTICE 'Admin User ID: %', admin_user_id;
    
    -- Delete all data in correct order (respecting foreign keys)
    
    -- 1. Delete payroll data
    DELETE FROM payroll_adjustments;
    DELETE FROM payroll_line_items;
    DELETE FROM payroll_runs;
    DELETE FROM payroll_periods;
    
    -- 2. Delete recruitment data (correct table names!)
    DELETE FROM applicant_evaluations;
    DELETE FROM applicants;
    DELETE FROM job_postings;
    
    -- 3. Delete employee documents (except admin's)
    DELETE FROM employee_documents 
    WHERE employee_id != admin_employee_id;
    
    -- 4. Delete attendance records (except admin's)
    DELETE FROM attendance 
    WHERE employee_id != admin_employee_id;
    
    -- 5. Delete leave data (except admin's)
    DELETE FROM leave_requests 
    WHERE employee_id != admin_employee_id;
    
    DELETE FROM leave_credits 
    WHERE employee_id != admin_employee_id;
    
    -- 6. Delete compensation data (except admin's)
    DELETE FROM employee_compensations 
    WHERE employee_id != admin_employee_id;
    
    -- 7. Delete announcements
    DELETE FROM announcements;
    
    -- 8. Delete user sessions
    DELETE FROM user_sessions 
    WHERE supabase_user_id != admin_user_id;
    
    -- 9. Delete audit logs (except admin's actions)
    DELETE FROM system_audit_log 
    WHERE user_id != admin_user_id;
    
    DELETE FROM leave_credit_audit 
    WHERE employee_id != admin_employee_id;
    
    -- 9. Delete all employees EXCEPT admin
    DELETE FROM employees 
    WHERE id != admin_employee_id;
    
    -- 10. Delete all auth users EXCEPT admin
    DELETE FROM auth.users 
    WHERE id != admin_user_id;
    
    RAISE NOTICE 'Cleanup completed! Only admin account remains.';
    RAISE NOTICE 'Admin email: admin@company.com';
    
END $$;

-- Verify what's left
SELECT 'Employees' as table_name, COUNT(*) as count FROM employees
UNION ALL
SELECT 'Auth Users', COUNT(*) FROM auth.users
UNION ALL
SELECT 'Attendance', COUNT(*) FROM attendance
UNION ALL
SELECT 'Leave Requests', COUNT(*) FROM leave_requests
UNION ALL
SELECT 'Payroll Periods', COUNT(*) FROM payroll_periods
UNION ALL
SELECT 'Job Postings', COUNT(*) FROM job_postings
UNION ALL
SELECT 'Applicants', COUNT(*) FROM applicants
UNION ALL
SELECT 'Applicant Evaluations', COUNT(*) FROM applicant_evaluations
UNION ALL
SELECT 'Documents', COUNT(*) FROM employee_documents
UNION ALL
SELECT 'Announcements', COUNT(*) FROM announcements
UNION ALL
SELECT 'User Sessions', COUNT(*) FROM user_sessions;
