-- ============================================================================
-- COPY THIS ENTIRE FILE AND RUN IT IN SUPABASE SQL EDITOR
-- ============================================================================
-- 
-- Instructions:
-- 1. Go to https://supabase.com/dashboard
-- 2. Select your project
-- 3. Click "SQL Editor" in the left sidebar
-- 4. Click "New Query"
-- 5. Copy and paste this ENTIRE file
-- 6. Click "Run" button
-- 7. You should see "Success. No rows returned"
--
-- ============================================================================

-- Create position_salaries table for position-based compensation
CREATE TABLE IF NOT EXISTS position_salaries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    position VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(100),
    payroll_type VARCHAR(20) NOT NULL DEFAULT 'Monthly',
    base_salary DECIMAL(12, 2) NOT NULL DEFAULT 0,
    daily_rate DECIMAL(12, 2) NOT NULL DEFAULT 0,
    hourly_rate DECIMAL(12, 2) NOT NULL DEFAULT 0,
    sss_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    philhealth_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    pagibig_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    tax_value DECIMAL(12, 2) NOT NULL DEFAULT 0,
    standard_work_hours_per_day DECIMAL(5, 2) NOT NULL DEFAULT 8.00,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT position_salaries_payroll_type_check CHECK (payroll_type IN ('Monthly', 'Daily', 'Hourly'))
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_position_salaries_position ON position_salaries(position, is_active);

-- Add comments
COMMENT ON TABLE position_salaries IS 'Stores salary information by position/role';
COMMENT ON COLUMN position_salaries.position IS 'Job position/title (e.g., Manager, Developer, Staff)';
COMMENT ON COLUMN position_salaries.department IS 'Department the position belongs to';
COMMENT ON COLUMN position_salaries.payroll_type IS 'How salary is calculated: Monthly, Daily, or Hourly';

-- ============================================================================
-- DONE! After running this, go back to your terminal and run:
-- C:\xampp\php\php.exe populate_positions.php
-- ============================================================================
