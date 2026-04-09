-- Reset Payroll for Testing
-- This deletes all payroll data so you can test from scratch

-- WARNING: This will delete ALL payroll data!
-- Only run this in development/testing environment

-- Step 1: Delete in correct order (respecting foreign keys)
DELETE FROM payroll_adjustments;
DELETE FROM payroll_line_items;
DELETE FROM payroll_runs;
DELETE FROM payroll_periods;

-- Step 2: Verify everything is deleted
SELECT 'Payroll Periods' as table_name, COUNT(*) as remaining_records FROM payroll_periods
UNION ALL
SELECT 'Payroll Runs', COUNT(*) FROM payroll_runs
UNION ALL
SELECT 'Payroll Line Items', COUNT(*) FROM payroll_line_items
UNION ALL
SELECT 'Payroll Adjustments', COUNT(*) FROM payroll_adjustments;

-- Now you can create new periods and test again!
