-- Fix the evaluation_date constraint to allow today's date
-- The current constraint is too strict and causes issues with timezone differences

-- Drop the old constraint
ALTER TABLE applicant_evaluations 
DROP CONSTRAINT IF EXISTS applicant_evaluations_evaluation_date_check;

-- Add new constraint that allows dates up to tomorrow (to handle timezone issues)
-- This prevents future dates while allowing today's date regardless of timezone
ALTER TABLE applicant_evaluations 
ADD CONSTRAINT applicant_evaluations_evaluation_date_check 
CHECK (evaluation_date <= CURRENT_DATE + INTERVAL '1 day');

-- Verify the constraint
SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE conname = 'applicant_evaluations_evaluation_date_check';
