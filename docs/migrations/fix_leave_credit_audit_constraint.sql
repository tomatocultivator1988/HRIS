-- Fix leave_credit_audit foreign key constraint
-- This allows admins (who are not in employees table) to approve/deny leave requests
-- The constraint was causing leave approval to fail because admin IDs are not in employees table

-- Drop the foreign key constraint on performed_by
ALTER TABLE leave_credit_audit 
DROP CONSTRAINT IF EXISTS leave_credit_audit_performed_by_fkey;

-- Optionally, you can add a comment to document why this constraint was removed
COMMENT ON COLUMN leave_credit_audit.performed_by IS 'User ID who performed the action (can be admin or employee)';
