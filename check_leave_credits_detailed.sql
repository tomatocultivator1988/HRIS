-- Show all employees with their leave credits (grouped by employee)
SELECT 
    e.employee_id,
    e.first_name || ' ' || e.last_name as full_name,
    e.work_email,
    e.is_active,
    COUNT(lc.id) as credit_records_count,
    STRING_AGG(
        lt.name || ': ' || lc.total_credits || ' total, ' || 
        lc.used_credits || ' used, ' || 
        lc.remaining_credits || ' remaining',
        ' | '
    ) as credits_summary
FROM employees e
LEFT JOIN leave_credits lc ON e.id = lc.employee_id AND lc.year = 2026
LEFT JOIN leave_types lt ON lc.leave_type_id = lt.id
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, e.work_email, e.is_active
ORDER BY e.is_active DESC, e.last_name, e.first_name;

-- Show leave types and their days_allowed
SELECT 
    name,
    days_allowed
FROM leave_types
ORDER BY name;

-- Show detailed leave credits per employee per type
SELECT 
    e.employee_id,
    e.first_name || ' ' || e.last_name as employee_name,
    e.is_active,
    lt.name as leave_type,
    lc.total_credits,
    lc.used_credits,
    lc.remaining_credits,
    lc.year
FROM leave_credits lc
JOIN employees e ON lc.employee_id = e.id
JOIN leave_types lt ON lc.leave_type_id = lt.id
WHERE lc.year = 2026
ORDER BY e.is_active DESC, e.last_name, e.first_name, lt.name;
