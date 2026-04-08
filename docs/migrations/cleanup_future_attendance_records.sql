-- Cleanup Future Attendance Records
-- This migration removes attendance records that were incorrectly created for future dates
-- These records were created by the detectAbsentEmployees function before the future date validation was added

-- Delete attendance records with dates in the future
-- This will remove any "On Leave" or "Absent" records that shouldn't exist yet
DELETE FROM attendance
WHERE date > CURRENT_DATE;

-- Verify the cleanup
SELECT 
    COUNT(*) as future_records_remaining,
    MIN(date) as earliest_future_date,
    MAX(date) as latest_future_date
FROM attendance
WHERE date > CURRENT_DATE;

-- Expected result: future_records_remaining should be 0
