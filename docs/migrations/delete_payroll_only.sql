-- ============================================
-- DELETE PAYROLL DATA ONLY
-- ============================================
-- This script deletes all payroll-related data
-- so you can generate payroll again from scratch
-- 
-- KEEPS:
-- - Employees
-- - Attendance records
-- - Position salaries
-- - All other data
-- ============================================

BEGIN;

-- Delete payroll data in correct order (child tables first)
DELETE FROM payroll_adjustments;
DELETE FROM payroll_line_items;
DELETE FROM payroll_runs;
DELETE FROM payroll_periods;

-- Show summary
DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'PAYROLL DATA DELETED!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Deleted:';
    RAISE NOTICE '  - All payroll adjustments';
    RAISE NOTICE '  - All payroll line items';
    RAISE NOTICE '  - All payroll runs';
    RAISE NOTICE '  - All payroll periods';
    RAISE NOTICE '';
    RAISE NOTICE 'KEPT (not deleted):';
    RAISE NOTICE '  - Employees';
    RAISE NOTICE '  - Attendance records';
    RAISE NOTICE '  - Position salaries';
    RAISE NOTICE '  - All other data';
    RAISE NOTICE '';
    RAISE NOTICE 'You can now generate payroll again!';
    RAISE NOTICE '========================================';
END $$;

COMMIT;

-- Verify deletion
SELECT 
    'payroll_periods' as table_name,
    COUNT(*) as remaining_records
FROM payroll_periods
UNION ALL
SELECT 
    'payroll_runs' as table_name,
    COUNT(*) as remaining_records
FROM payroll_runs
UNION ALL
SELECT 
    'payroll_line_items' as table_name,
    COUNT(*) as remaining_records
FROM payroll_line_items
UNION ALL
SELECT 
    'payroll_adjustments' as table_name,
    COUNT(*) as remaining_records
FROM payroll_adjustments;

-- Show that employees and attendance are still there
SELECT 
    'employees' as table_name,
    COUNT(*) as total_records
FROM employees
UNION ALL
SELECT 
    'attendance' as table_name,
    COUNT(*) as total_records
FROM attendance
UNION ALL
SELECT 
    'position_salaries' as table_name,
    COUNT(*) as total_records
FROM position_salaries;
