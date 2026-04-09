-- Fix Employee Salaries
-- This updates all employees to have realistic salary data

-- Update all active employees with default salaries
UPDATE employee_compensation
SET 
    base_salary = 30000.00,
    daily_rate = 1363.64,
    hourly_rate = 170.45,
    tax_value = 2500.00,
    sss_employee_share = 581.30,
    philhealth_employee_share = 450.00,
    pagibig_employee_share = 100.00
WHERE is_active = TRUE
  AND base_salary = 0.00;

-- Verify the update
SELECT 
    e.first_name,
    e.last_name,
    ec.payroll_type,
    ec.base_salary,
    ec.daily_rate,
    ec.tax_value
FROM employee_compensation ec
JOIN employees e ON e.id = ec.employee_id
WHERE ec.is_active = TRUE
ORDER BY e.last_name;
