-- Fix existing employees to force password change
-- Only update employees who are using default passwords (never changed password)

-- Update existing employees who never changed password
-- (password_changed_at is NULL means they never changed it)
UPDATE employees 
SET force_password_change = TRUE 
WHERE password_changed_at IS NULL 
  AND force_password_change = FALSE
  AND is_active = TRUE;

-- Verify the changes
SELECT 
    employee_id,
    first_name,
    last_name,
    work_email,
    force_password_change,
    password_changed_at,
    created_at
FROM employees
WHERE is_active = TRUE
ORDER BY created_at DESC
LIMIT 20;

-- Summary
SELECT 
    force_password_change,
    COUNT(*) as employee_count
FROM employees
WHERE is_active = TRUE
GROUP BY force_password_change;
