-- ============================================
-- RESET PAYROLL & ADD SAMPLE ATTENDANCE
-- ============================================
-- This script:
-- 1. Deletes all payroll data
-- 2. Adds sample attendance for your 2 employees
-- 3. So you can generate payroll with actual net pay
-- ============================================

-- STEP 1: Delete all payroll data
DELETE FROM payroll_adjustments;
DELETE FROM payroll_line_items;
DELETE FROM payroll_runs;
DELETE FROM payroll_periods;

-- STEP 2: Get your 2 employee IDs
DO $$
DECLARE
    emp1_id UUID;
    emp2_id UUID;
    current_month_start DATE;
    current_month_end DATE;
    attendance_date DATE;
BEGIN
    -- Get first 2 employees (adjust if you want specific employees)
    SELECT id INTO emp1_id FROM employees ORDER BY created_at LIMIT 1 OFFSET 0;
    SELECT id INTO emp2_id FROM employees ORDER BY created_at LIMIT 1 OFFSET 1;
    
    -- Calculate current month dates
    current_month_start := DATE_TRUNC('month', CURRENT_DATE)::DATE;
    current_month_end := (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month - 1 day')::DATE;
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'ADDING SAMPLE ATTENDANCE';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Employee 1 ID: %', emp1_id;
    RAISE NOTICE 'Employee 2 ID: %', emp2_id;
    RAISE NOTICE 'Period: % to %', current_month_start, current_month_end;
    RAISE NOTICE '';
    
    -- STEP 3: Insert attendance for Employee 1 (full month, 8 hours per day)
    attendance_date := current_month_start;
    WHILE attendance_date <= current_month_end LOOP
        -- Only insert for working days (Monday-Friday)
        IF EXTRACT(DOW FROM attendance_date) BETWEEN 1 AND 5 THEN
            INSERT INTO attendance (
                employee_id,
                date,
                time_in,
                time_out,
                status,
                work_hours
            ) VALUES (
                emp1_id,
                attendance_date,
                (attendance_date || ' 08:00:00')::TIMESTAMP,
                (attendance_date || ' 17:00:00')::TIMESTAMP,
                'Present',
                8.0
            )
            ON CONFLICT (employee_id, date) DO NOTHING;
        END IF;
        
        attendance_date := attendance_date + INTERVAL '1 day';
    END LOOP;
    
    RAISE NOTICE 'Employee 1: Added attendance records';
    
    -- STEP 4: Insert attendance for Employee 2 (full month, 8 hours per day)
    attendance_date := current_month_start;
    WHILE attendance_date <= current_month_end LOOP
        -- Only insert for working days (Monday-Friday)
        IF EXTRACT(DOW FROM attendance_date) BETWEEN 1 AND 5 THEN
            INSERT INTO attendance (
                employee_id,
                date,
                time_in,
                time_out,
                status,
                work_hours
            ) VALUES (
                emp2_id,
                attendance_date,
                (attendance_date || ' 08:30:00')::TIMESTAMP,  -- Employee 2 comes in 30 min late
                (attendance_date || ' 17:30:00')::TIMESTAMP,
                'Late',
                8.0
            )
            ON CONFLICT (employee_id, date) DO NOTHING;
        END IF;
        
        attendance_date := attendance_date + INTERVAL '1 day';
    END LOOP;
    
    RAISE NOTICE 'Employee 2: Added attendance records';
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'DONE!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Next steps:';
    RAISE NOTICE '1. Go to Payroll module';
    RAISE NOTICE '2. Create payroll period for current month';
    RAISE NOTICE '3. Generate payroll run';
    RAISE NOTICE '4. You should see net pay calculated!';
    
END $$;

-- Verify attendance was added
SELECT 
    e.employee_id,
    e.first_name,
    e.last_name,
    COUNT(a.id) as attendance_days,
    SUM(a.work_hours) as total_hours,
    COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_days,
    COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as late_days
FROM employees e
LEFT JOIN attendance a ON e.id = a.employee_id
WHERE e.id != (SELECT id FROM admins LIMIT 1)  -- Exclude admin
GROUP BY e.id, e.employee_id, e.first_name, e.last_name
ORDER BY e.created_at;

-- Show sample attendance records
SELECT 
    e.employee_id,
    e.first_name || ' ' || e.last_name as employee_name,
    a.date,
    a.time_in,
    a.time_out,
    a.work_hours,
    a.status
FROM attendance a
JOIN employees e ON a.employee_id = e.id
ORDER BY a.date DESC, e.employee_id
LIMIT 10;
