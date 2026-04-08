-- Migration: Fix reviewed_by and performed_by foreign key constraints
-- Date: 2026-04-07
-- Description: Remove foreign key constraints to allow admins to approve/deny leave requests

-- Drop the foreign key constraint on leave_requests.reviewed_by
ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_reviewed_by_fkey;

-- Drop the foreign key constraint on leave_credit_audit.performed_by
ALTER TABLE leave_credit_audit DROP CONSTRAINT IF EXISTS leave_credit_audit_performed_by_fkey;

-- The reviewed_by and performed_by columns can now accept any UUID (admin or employee)
-- We'll validate this in the application layer instead

-- Verify the constraints are removed
SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE (conrelid = 'leave_requests'::regclass AND conname LIKE '%reviewed_by%')
   OR (conrelid = 'leave_credit_audit'::regclass AND conname LIKE '%performed_by%');
