-- ============================================
-- SAFE CLEANUP SCRIPT - Checks tables first!
-- ============================================
-- This version checks if tables exist before deleting
-- Run this AFTER running inspect_database.sql
-- ============================================

DO $$
DECLARE
    admin_employee_id UUID;
    admin_user_id UUID;
    table_exists BOOLEAN;
BEGIN
    -- Find admin employee
    SELECT id INTO admin_employee_id 
    FROM employees 
    WHERE work_email = 'admin@company.com' 
       OR work_email LIKE '%admin%'
    LIMIT 1;
    
    -- Find admin user in auth.users
    SELECT id INTO admin_user_id
    FROM auth.users
    WHERE email = 'admin@company.com'
       OR email LIKE '%admin%'
    LIMIT 1;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'CLEANUP STARTING';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Admin Employee ID: %', admin_employee_id;
    RAISE NOTICE 'Admin User ID: %', admin_user_id;
    RAISE NOTICE '';
    
    IF admin_employee_id IS NULL THEN
        RAISE EXCEPTION 'Admin employee not found! Please check your admin email.';
    END IF;
    
    -- 1. PAYROLL DATA
    RAISE NOTICE 'Cleaning payroll data...';
    
    -- Check and delete payroll_adjustments
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_adjustments') INTO table_exists;
    IF table_exists THEN
        DELETE FROM payroll_adjustments;
        RAISE NOTICE '  ✓ Deleted payroll_adjustments';
    ELSE
        RAISE NOTICE '  ⊘ Table payroll_adjustments does not exist';
    END IF;
    
    -- Check and delete payroll_line_items
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_line_items') INTO table_exists;
    IF table_exists THEN
        DELETE FROM payroll_line_items;
        RAISE NOTICE '  ✓ Deleted payroll_line_items';
    ELSE
        RAISE NOTICE '  ⊘ Table payroll_line_items does not exist';
    END IF;
    
    -- Check and delete payroll_runs
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_runs') INTO table_exists;
    IF table_exists THEN
        DELETE FROM payroll_runs;
        RAISE NOTICE '  ✓ Deleted payroll_runs';
    ELSE
        RAISE NOTICE '  ⊘ Table payroll_runs does not exist';
    END IF;
    
    -- Check and delete payroll_periods
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_periods') INTO table_exists;
    IF table_exists THEN
        DELETE FROM payroll_periods;
        RAISE NOTICE '  ✓ Deleted payroll_periods';
    ELSE
        RAISE NOTICE '  ⊘ Table payroll_periods does not exist';
    END IF;
    
    -- 2. RECRUITMENT DATA
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning recruitment data...';
    
    -- Check and delete applicant_evaluations
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'applicant_evaluations') INTO table_exists;
    IF table_exists THEN
        DELETE FROM applicant_evaluations;
        RAISE NOTICE '  ✓ Deleted applicant_evaluations';
    ELSE
        RAISE NOTICE '  ⊘ Table applicant_evaluations does not exist';
    END IF;
    
    -- Check and delete applicants
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'applicants') INTO table_exists;
    IF table_exists THEN
        DELETE FROM applicants;
        RAISE NOTICE '  ✓ Deleted applicants';
    ELSE
        RAISE NOTICE '  ⊘ Table applicants does not exist';
    END IF;
    
    -- Check and delete job_postings
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'job_postings') INTO table_exists;
    IF table_exists THEN
        DELETE FROM job_postings;
        RAISE NOTICE '  ✓ Deleted job_postings';
    ELSE
        RAISE NOTICE '  ⊘ Table job_postings does not exist';
    END IF;
    
    -- 3. EMPLOYEE DOCUMENTS (except admin's)
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning employee documents...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employee_documents') INTO table_exists;
    IF table_exists THEN
        DELETE FROM employee_documents 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted employee_documents (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table employee_documents does not exist';
    END IF;
    
    -- 4. ATTENDANCE (except admin's)
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning attendance records...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'attendance') INTO table_exists;
    IF table_exists THEN
        DELETE FROM attendance 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted attendance (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table attendance does not exist';
    END IF;
    
    -- 5. LEAVE DATA (except admin's)
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning leave data...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_requests') INTO table_exists;
    IF table_exists THEN
        DELETE FROM leave_requests 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted leave_requests (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table leave_requests does not exist';
    END IF;
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_credits') INTO table_exists;
    IF table_exists THEN
        DELETE FROM leave_credits 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted leave_credits (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table leave_credits does not exist';
    END IF;
    
    -- 6. COMPENSATION DATA (except admin's) - OPTIONAL
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning compensation data...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employee_compensations') INTO table_exists;
    IF table_exists THEN
        DELETE FROM employee_compensations 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted employee_compensations (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table employee_compensations does not exist (OK - using position_salaries)';
    END IF;
    
    -- 7. ANNOUNCEMENTS
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning announcements...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'announcements') INTO table_exists;
    IF table_exists THEN
        DELETE FROM announcements;
        RAISE NOTICE '  ✓ Deleted announcements';
    ELSE
        RAISE NOTICE '  ⊘ Table announcements does not exist';
    END IF;
    
    -- 8. USER SESSIONS (except admin's)
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning user sessions...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'user_sessions') INTO table_exists;
    IF table_exists THEN
        DELETE FROM user_sessions 
        WHERE supabase_user_id != admin_user_id;
        RAISE NOTICE '  ✓ Deleted user_sessions (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table user_sessions does not exist';
    END IF;
    
    -- 9. AUDIT LOGS (except admin's)
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning audit logs...';
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'system_audit_log') INTO table_exists;
    IF table_exists THEN
        DELETE FROM system_audit_log 
        WHERE user_id != admin_user_id;
        RAISE NOTICE '  ✓ Deleted system_audit_log (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table system_audit_log does not exist';
    END IF;
    
    SELECT EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_credit_audit') INTO table_exists;
    IF table_exists THEN
        DELETE FROM leave_credit_audit 
        WHERE employee_id != admin_employee_id;
        RAISE NOTICE '  ✓ Deleted leave_credit_audit (kept admin''s)';
    ELSE
        RAISE NOTICE '  ⊘ Table leave_credit_audit does not exist';
    END IF;
    
    -- 10. DELETE ALL EMPLOYEES EXCEPT ADMIN
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning employees...';
    
    DELETE FROM employees 
    WHERE id != admin_employee_id;
    RAISE NOTICE '  ✓ Deleted all employees except admin';
    
    -- 11. DELETE ALL AUTH USERS EXCEPT ADMIN
    RAISE NOTICE '';
    RAISE NOTICE 'Cleaning auth users...';
    
    IF admin_user_id IS NOT NULL THEN
        DELETE FROM auth.users 
        WHERE id != admin_user_id;
        RAISE NOTICE '  ✓ Deleted all auth users except admin';
    ELSE
        RAISE NOTICE '  ⊘ Admin user ID not found in auth.users';
    END IF;
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'CLEANUP COMPLETED!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Only admin account remains.';
    RAISE NOTICE 'Admin email: Check employees table';
    
END $$;

-- Verify what's left
SELECT 'Employees' as table_name, COUNT(*) as count FROM employees
UNION ALL
SELECT 'Attendance', COUNT(*) FROM attendance WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'attendance')
UNION ALL
SELECT 'Leave Requests', COUNT(*) FROM leave_requests WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_requests')
UNION ALL
SELECT 'Payroll Periods', COUNT(*) FROM payroll_periods WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_periods')
UNION ALL
SELECT 'Job Postings', COUNT(*) FROM job_postings WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'job_postings')
UNION ALL
SELECT 'Applicants', COUNT(*) FROM applicants WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'applicants')
UNION ALL
SELECT 'Documents', COUNT(*) FROM employee_documents WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employee_documents')
UNION ALL
SELECT 'Announcements', COUNT(*) FROM announcements WHERE EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'announcements');
