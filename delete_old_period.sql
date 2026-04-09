-- Delete the existing 2026-04 period
-- Run this in your database if you want to recreate it

DELETE FROM payroll_periods WHERE code = '2026-04';

-- Or see all periods first:
-- SELECT code, start_date, end_date, status FROM payroll_periods ORDER BY start_date DESC;
