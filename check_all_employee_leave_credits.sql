-- Check all employees' leave credits for 2026
SELECT 
    e.employee_id,
    e.first_name,
    e.last_name,
    e.work_email,
    lt.name as leave_type,
    lc.total_credits,
    lc.used_credits,
    lc.remaining_credits,
    lc.year
FROM employees e
LEFT JOIN leave_credits lc ON e.id = lc.employee_id AND lc.year = 2026
LEFT JOIN leave_types lt ON lc.leave_type_id = lt.id
WHERE e.is_active = true
ORDER BY e.last_name, e.first_name, lt.name;

-- Check what the leave_types table has for days_allowed
SELECT *
FROM leave_types
ORDER BY name;

-- Count employees with leave credits vs without
SELECT 
    'Employees with leave credits' as status,
    COUNT(DISTINCT lc.employee_id) as count
FROM leave_credits lc
WHERE lc.year = 2026
UNION ALL
SELECT 
    'Active employees total' as status,
    COUNT(*) as count
FROM employees
WHERE is_active = true;
