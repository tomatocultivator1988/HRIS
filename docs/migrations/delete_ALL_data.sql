-- ============================================
-- NUCLEAR OPTION - DELETE ALL DATA
-- ============================================
-- WARNING: This deletes EVERYTHING including admin!
-- Use this if you want to start completely fresh
-- ============================================

DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'DELETING ALL DATA - NO EXCEPTIONS!';
    RAISE NOTICE '========================================';
    
    -- Delete in correct order (respecting foreign keys)
    
    RAISE NOTICE 'Deleting payroll data...';
    DELETE FROM payroll_adjustments;
    DELETE FROM payroll_line_items;
    DELETE FROM payroll_runs;
    DELETE FROM payroll_periods;
    
    RAISE NOTICE 'Deleting recruitment data...';
    DELETE FROM applicant_evaluations;
    DELETE FROM applicants;
    DELETE FROM job_postings;
    
    RAISE NOTICE 'Deleting employee documents...';
    DELETE FROM employee_documents;
    
    RAISE NOTICE 'Deleting attendance...';
    DELETE FROM attendance;
    
    RAISE NOTICE 'Deleting leave data...';
    DELETE FROM leave_requests;
    DELETE FROM leave_credits;
    -- Don't delete leave_types - keep them for next setup
    
    RAISE NOTICE 'Deleting compensation...';
    DELETE FROM employee_compensation;
    -- Don't delete position_salaries - keep them for next setup
    
    RAISE NOTICE 'Deleting announcements...';
    DELETE FROM announcements;
    
    RAISE NOTICE 'Deleting sessions...';
    DELETE FROM user_sessions;
    
    RAISE NOTICE 'Deleting audit logs...';
    DELETE FROM system_audit_log;
    DELETE FROM leave_credit_audit;
    
    RAISE NOTICE 'Deleting admins...';
    DELETE FROM admins;
    
    RAISE NOTICE 'Deleting employees...';
    DELETE FROM employees;
    
    RAISE NOTICE 'Deleting auth users...';
    DELETE FROM auth.users;
    
    -- Don't delete these reference tables:
    -- - leave_types
    -- - position_salaries
    -- - work_calendar
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'ALL DATA DELETED!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Reference tables kept: leave_types, position_salaries, work_calendar';
    RAISE NOTICE '';
    RAISE NOTICE 'NEXT STEPS:';
    RAISE NOTICE '1. Go to Supabase Dashboard > Authentication > Users';
    RAISE NOTICE '2. Click "Add User"';
    RAISE NOTICE '3. Create admin user with email and password';
    RAISE NOTICE '4. Copy the UUID';
    RAISE NOTICE '5. Run insert_fresh_admin.sql with that UUID';
    
END $$;

-- Verify everything is empty
SELECT 
    'employees' as table_name,
    COUNT(*) as count
FROM employees
UNION ALL
SELECT 'admins', COUNT(*) FROM admins
UNION ALL
SELECT 'auth.users', COUNT(*) FROM auth.users
UNION ALL
SELECT 'attendance', COUNT(*) FROM attendance
UNION ALL
SELECT 'leave_requests', COUNT(*) FROM leave_requests
UNION ALL
SELECT 'payroll_periods', COUNT(*) FROM payroll_periods
UNION ALL
SELECT 'job_postings', COUNT(*) FROM job_postings
UNION ALL
SELECT 'applicants', COUNT(*) FROM applicants
UNION ALL
SELECT 'employee_documents', COUNT(*) FROM employee_documents
UNION ALL
SELECT 'announcements', COUNT(*) FROM announcements;

-- Show what's left (reference data)
SELECT 'leave_types' as table_name, COUNT(*) as count FROM leave_types
UNION ALL
SELECT 'position_salaries', COUNT(*) FROM position_salaries
UNION ALL
SELECT 'work_calendar', COUNT(*) FROM work_calendar;
