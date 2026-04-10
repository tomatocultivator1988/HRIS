-- Check if the trigger exists
SELECT 
    trigger_name, 
    event_manipulation, 
    event_object_table, 
    action_statement
FROM information_schema.triggers
WHERE trigger_name = 'trigger_update_leave_credits';

-- Check if leave_credits records exist for kiancabalumcabalum@gmail.com
SELECT 
    e.first_name,
    e.last_name,
    e.work_email,
    lt.name as leave_type,
    lc.total_credits,
    lc.used_credits,
    lc.remaining_credits,
    lc.year
FROM employees e
LEFT JOIN leave_credits lc ON e.id = lc.employee_id
LEFT JOIN leave_types lt ON lc.leave_type_id = lt.id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com'
ORDER BY lt.name, lc.year;

-- Check approved leave requests for this employee
SELECT 
    lr.id,
    lr.start_date,
    lr.end_date,
    lr.total_days,
    lr.status,
    lr.reviewed_at,
    lt.name as leave_type,
    EXTRACT(YEAR FROM lr.start_date) as leave_year
FROM leave_requests lr
JOIN employees e ON lr.employee_id = e.id
JOIN leave_types lt ON lr.leave_type_id = lt.id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com'
  AND lr.status = 'Approved'
ORDER BY lr.start_date DESC;

-- Check if leave_credit_audit table exists
SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_name = 'leave_credit_audit'
) as audit_table_exists;
