-- ============================================
-- DATABASE INSPECTION SCRIPT
-- ============================================
-- Run this in Supabase SQL Editor to see ALL tables and their row counts
-- This will help us create the correct cleanup script
-- ============================================

-- Part 1: List ALL tables in your database
SELECT 
    schemaname as schema,
    tablename as table_name,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY tablename;

-- Part 2: Count rows in each table
DO $$
DECLARE
    table_record RECORD;
    row_count INTEGER;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'TABLE ROW COUNTS';
    RAISE NOTICE '========================================';
    
    FOR table_record IN 
        SELECT tablename 
        FROM pg_tables 
        WHERE schemaname = 'public'
        ORDER BY tablename
    LOOP
        EXECUTE format('SELECT COUNT(*) FROM %I', table_record.tablename) INTO row_count;
        RAISE NOTICE '% : % rows', RPAD(table_record.tablename, 40), row_count;
    END LOOP;
    
    RAISE NOTICE '========================================';
END $$;

-- Part 3: Show table structures (columns and types)
SELECT 
    table_name,
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'public'
ORDER BY table_name, ordinal_position;

-- Part 4: Show foreign key relationships
SELECT
    tc.table_name as from_table,
    kcu.column_name as from_column,
    ccu.table_name AS to_table,
    ccu.column_name AS to_column
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
    AND tc.table_schema = kcu.table_schema
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
    AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = 'FOREIGN KEY'
    AND tc.table_schema = 'public'
ORDER BY tc.table_name, kcu.column_name;

-- Part 5: Check if specific tables exist
SELECT 
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employees') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as employees,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'attendance') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as attendance,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_requests') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as leave_requests,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_credits') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as leave_credits,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_periods') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as payroll_periods,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_runs') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as payroll_runs,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_line_items') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as payroll_line_items,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'payroll_adjustments') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as payroll_adjustments,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employee_compensations') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as employee_compensations,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'position_salaries') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as position_salaries,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'job_postings') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as job_postings,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'applicants') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as applicants,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'applicant_evaluations') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as applicant_evaluations,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'employee_documents') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as employee_documents,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'announcements') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as announcements,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'system_audit_log') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as system_audit_log,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'leave_credit_audit') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as leave_credit_audit,
    CASE WHEN EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'user_sessions') 
        THEN '✓ EXISTS' ELSE '✗ MISSING' END as user_sessions;

-- Part 6: Find admin account
SELECT 
    'Admin Employee' as type,
    id,
    employee_id,
    first_name,
    last_name,
    work_email,
    is_active
FROM employees
WHERE work_email LIKE '%admin%'
LIMIT 5;

-- Part 7: Sample data from each table (first 3 rows)
DO $$
DECLARE
    table_record RECORD;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'SAMPLE DATA (First 3 rows per table)';
    RAISE NOTICE '========================================';
    
    FOR table_record IN 
        SELECT tablename 
        FROM pg_tables 
        WHERE schemaname = 'public'
        ORDER BY tablename
    LOOP
        RAISE NOTICE '';
        RAISE NOTICE 'Table: %', table_record.tablename;
        RAISE NOTICE '----------------------------------------';
        
        -- This will show in the query results, not in NOTICE
        EXECUTE format('SELECT * FROM %I LIMIT 3', table_record.tablename);
    END LOOP;
END $$;
