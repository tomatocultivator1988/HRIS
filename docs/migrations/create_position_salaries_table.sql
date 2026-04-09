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

-- Insert default positions from existing employees (only if they don't exist)
INSERT INTO position_salaries (position, department, base_salary, payroll_type)
SELECT DISTINCT 
    position,
    department,
    30000.00 as base_salary,
    'Monthly' as payroll_type
FROM employees
WHERE position IS NOT NULL 
  AND position != ''
  AND NOT EXISTS (
    SELECT 1 FROM position_salaries ps WHERE ps.position = employees.position
  )
ON CONFLICT (position) DO NOTHING;

COMMENT ON TABLE position_salaries IS 'Stores salary information by position/role';
COMMENT ON COLUMN position_salaries.position IS 'Job position/title (e.g., Manager, Developer, Staff)';
COMMENT ON COLUMN position_salaries.department IS 'Department the position belongs to';
COMMENT ON COLUMN position_salaries.payroll_type IS 'How salary is calculated: Monthly, Daily, or Hourly';
