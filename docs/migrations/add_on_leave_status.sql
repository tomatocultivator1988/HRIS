-- Migration: Add 'On Leave' to attendance status constraint
-- Date: 2026-04-07
-- Description: Allows 'On Leave' status for attendance records when leave is approved

-- Drop the existing constraint
ALTER TABLE attendance DROP CONSTRAINT IF EXISTS attendance_status_check;

-- Add new constraint with 'On Leave' included
ALTER TABLE attendance ADD CONSTRAINT attendance_status_check 
    CHECK (status IN ('Present', 'Late', 'Absent', 'Half-day', 'On Leave'));

-- Verify the constraint
SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE conrelid = 'attendance'::regclass 
AND conname = 'attendance_status_check';
