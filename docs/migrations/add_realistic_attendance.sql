-- Add Realistic Attendance for April 2026
-- This creates varied attendance records to show different payroll scenarios

-- Delete existing April 2026 attendance
DELETE FROM attendance WHERE date >= '2026-04-01' AND date <= '2026-04-30';

-- Employee 1: Perfect attendance, no overtime (22 days, 8 hours each)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    d.date + TIME '17:00:00',
    'Present',
    8.00,
    'Perfect attendance',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Juan' AND e.last_name = 'Dela Cruz'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);

-- Employee 2: Has overtime (22 days, 10 hours on 5 days)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (5, 10, 15, 20, 25) THEN d.date + TIME '19:00:00'
        ELSE d.date + TIME '17:00:00'
    END,
    'Present',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (5, 10, 15, 20, 25) THEN 10.00
        ELSE 8.00
    END,
    'Has overtime on some days',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'First' AND e.last_name = 'Last'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);

-- Employee 3: Has 2 absences (20 days only)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    d.date + TIME '17:00:00',
    'Present',
    8.00,
    'Has 2 absences',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Testt' AND e.last_name = 'Testt'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6)
  AND EXTRACT(DAY FROM d.date) NOT IN (8, 22);

-- Employee 4: Has undertime (22 days, but 6 hours on 3 days)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (7, 14, 21) THEN d.date + TIME '15:00:00'
        ELSE d.date + TIME '17:00:00'
    END,
    'Present',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (7, 14, 21) THEN 6.00
        ELSE 8.00
    END,
    'Has undertime on some days',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Kian' AND e.last_name = 'Piodena'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);

-- Employee 5: Mix of everything (19 days, some OT, some undertime, 3 absences)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (3, 17) THEN d.date + TIME '20:00:00'
        WHEN EXTRACT(DAY FROM d.date) IN (11, 25) THEN d.date + TIME '14:00:00'
        ELSE d.date + TIME '17:00:00'
    END,
    'Present',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (3, 17) THEN 11.00
        WHEN EXTRACT(DAY FROM d.date) IN (11, 25) THEN 6.00
        ELSE 8.00
    END,
    'Mixed attendance',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Fixtest' AND e.last_name = 'Employee'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6)
  AND EXTRACT(DAY FROM d.date) NOT IN (16, 23, 29);

-- Employee 6: Lots of overtime (22 days, 9-10 hours most days)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) % 2 = 0 THEN d.date + TIME '18:00:00'
        ELSE d.date + TIME '19:00:00'
    END,
    'Present',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) % 2 = 0 THEN 9.00
        ELSE 10.00
    END,
    'Frequent overtime',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Force' AND e.last_name = 'Force'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);

-- Employee 7: Part-time (11 days, 4 hours each)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    d.date + TIME '08:00:00',
    d.date + TIME '12:00:00',
    'Present',
    4.00,
    'Part-time employee',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Password' AND e.last_name = 'Password'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6)
  AND EXTRACT(DAY FROM d.date) % 2 = 1;

-- Employee 8: Late arrivals (22 days, but marked as Late status on some days)
INSERT INTO attendance (employee_id, date, time_in, time_out, status, work_hours, remarks, created_at)
SELECT 
    e.id,
    d.date,
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (2, 9, 16, 23, 30) THEN d.date + TIME '09:30:00'
        ELSE d.date + TIME '08:00:00'
    END,
    d.date + TIME '17:00:00',
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (2, 9, 16, 23, 30) THEN 'Late'
        ELSE 'Present'
    END,
    CASE 
        WHEN EXTRACT(DAY FROM d.date) IN (2, 9, 16, 23, 30) THEN 7.50
        ELSE 8.00
    END,
    'Has late arrivals',
    NOW()
FROM employees e
CROSS JOIN (
    SELECT generate_series('2026-04-01'::date, '2026-04-30'::date, '1 day'::interval)::date AS date
) d
WHERE e.first_name = 'Pass' AND e.last_name = 'Pass'
  AND EXTRACT(DOW FROM d.date) NOT IN (0, 6);

-- Verify the attendance records
SELECT 
    e.first_name,
    e.last_name,
    COUNT(*) as days_present,
    SUM(a.work_hours) as total_hours,
    ROUND(AVG(a.work_hours), 2) as avg_hours_per_day,
    SUM(CASE WHEN a.work_hours > 8 THEN a.work_hours - 8 ELSE 0 END) as overtime_hours
FROM attendance a
JOIN employees e ON e.id = a.employee_id
WHERE a.date >= '2026-04-01' AND a.date <= '2026-04-30'
GROUP BY e.id, e.first_name, e.last_name
ORDER BY e.last_name;
