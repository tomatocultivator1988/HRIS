-- Check leave credits and approved leaves for kiancabalumcabalum@gmail.com
SELECT 
    e.employee_number,
    e.first_name,
    e.last_name,
    e.work_email,
    lc.leave_type,
    lc.total_credits,
    lc.used_credits,
    lc.remaining_credits
FROM employees e
LEFT JOIN leave_credits lc ON e.id = lc.employee_id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com';

-- Check approved leave requests
SELECT 
    lr.id,
    lr.leave_type,
    lr.start_date,
    lr.end_date,
    lr.total_days,
    lr.status,
    lr.created_at
FROM leave_requests lr
JOIN employees e ON lr.employee_id = e.id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com'
AND lr.status = 'Approved'
ORDER BY lr.created_at DESC;
