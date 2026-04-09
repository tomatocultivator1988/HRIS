INSERT INTO employee_compensation (
    employee_id,
    payroll_type,
    base_salary,
    daily_rate,
    hourly_rate,
    standard_work_hours_per_day,
    tax_mode,
    tax_value,
    sss_employee_share,
    philhealth_employee_share,
    pagibig_employee_share,
    effective_start_date,
    is_active
)
SELECT
    e.id,
    'Monthly',
    30000.00,  -- ₱30,000 monthly salary
    1363.64,   -- Daily rate (30000 / 22 working days)
    170.45,    -- Hourly rate (1363.64 / 8 hours)
    8.00,
    'Flat',
    2500.00,   -- ₱2,500 tax
    581.30,    -- SSS employee share
    450.00,    -- PhilHealth employee share
    100.00,    -- PagIBIG employee share
    CURRENT_DATE,
    TRUE
FROM employees e
WHERE e.is_active = TRUE
AND NOT EXISTS (
    SELECT 1
    FROM employee_compensation ec
    WHERE ec.employee_id = e.id
      AND ec.is_active = TRUE
      AND ec.effective_end_date IS NULL
);

INSERT INTO payroll_periods (
    code,
    start_date,
    end_date,
    pay_date,
    status,
    created_at,
    updated_at
)
SELECT
    TO_CHAR(CURRENT_DATE, 'YYYY-MM'),
    DATE_TRUNC('month', CURRENT_DATE)::DATE,
    (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month - 1 day')::DATE,
    (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month + 4 day')::DATE,
    'Draft',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM payroll_periods pp
    WHERE pp.code = TO_CHAR(CURRENT_DATE, 'YYYY-MM')
);
