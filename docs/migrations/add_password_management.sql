-- Password Management Migration
-- Adds support for force password change on first login

-- Add force_password_change column to employees table
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS force_password_change BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP;

-- Add password_changed_at column to admins table (for consistency)
ALTER TABLE admins 
ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP;

-- Create index for force_password_change lookups
CREATE INDEX IF NOT EXISTS idx_employees_force_password_change 
ON employees(force_password_change) WHERE force_password_change = true;

-- Add password change audit to system_audit_log
-- (No schema change needed, will use existing table)

-- Comments
COMMENT ON COLUMN employees.force_password_change IS 'Flag to force employee to change password on next login';
COMMENT ON COLUMN employees.password_changed_at IS 'Timestamp of last password change';
COMMENT ON COLUMN admins.password_changed_at IS 'Timestamp of last password change';
