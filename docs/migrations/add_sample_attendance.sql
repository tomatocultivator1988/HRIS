-- Add Sample Attendance for April 2026
-- This creates attendance records so payroll has data to calculate from

-- Delete any existing April 2026 attendance first
DELETE FROM attendance 
WHERE date >= '2026-04-01' AND date <= '2026-04-30';

-- Add attendance for all active employees for April 2026 (weekdays only)
-- This assumes 22 working days in April 2026
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    d.date + TIME '17:00:00',
    'Present',
    8.00,
    'Sample attendance for payroll testing',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series(
        '2026-04-01'::date,
        '2026-04-30'::date,
        '1 day'::interval
    )::date AS date
) d
WHERE e.is_active = TRUE
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6) -- Exclude weekends (0=Sunday, 6=Saturday)
ORDER BY e.id, d.date;

-- Verify the attendance records
SELECT 
    e.first_name,
    e.last_name,
    COUNT(*) as days_present,
    SUM(work_hours) as total_hours
FROM attendance a
JOIN employees e ON e.id = a.employee_id
WHERE a.date >= '2026-04-01' AND a.date <= '2026-04-30'
GROUP BY e.id, e.first_name, e.last_name
ORDER BY e.last_name;
