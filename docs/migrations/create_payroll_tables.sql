CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE IF NOT EXISTS payroll_periods (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(20) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    pay_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    created_by UUID NULL,
    finalized_by UUID NULL,
    finalized_at TIMESTAMP NULL,
    paid_by UUID NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT payroll_periods_date_range_check CHECK (start_date <= end_date),
    CONSTRAINT payroll_periods_pay_date_check CHECK (pay_date >= end_date),
    CONSTRAINT payroll_periods_status_check CHECK (status IN ('Draft', 'Processing', 'Finalized', 'Paid', 'Cancelled')),
    CONSTRAINT payroll_periods_unique_range UNIQUE (start_date, end_date)
);

CREATE TABLE IF NOT EXISTS employee_compensation (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    employee_id UUID NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    payroll_type VARCHAR(20) NOT NULL DEFAULT 'Monthly',
    base_salary NUMERIC(12,2) NULL,
    daily_rate NUMERIC(12,2) NULL,
    hourly_rate NUMERIC(12,2) NULL,
    standard_work_hours_per_day NUMERIC(5,2) NOT NULL DEFAULT 8.00,
    tax_mode VARCHAR(20) NOT NULL DEFAULT 'Flat',
    tax_value NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    sss_employee_share NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    philhealth_employee_share NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    pagibig_employee_share NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    effective_start_date DATE NOT NULL,
    effective_end_date DATE NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT employee_compensation_payroll_type_check CHECK (payroll_type IN ('Monthly', 'Daily', 'Hourly')),
    CONSTRAINT employee_compensation_tax_mode_check CHECK (tax_mode IN ('Flat', 'Bracketed', 'Exempt')),
    CONSTRAINT employee_compensation_non_negative_check CHECK (
        COALESCE(base_salary, 0) >= 0
        AND COALESCE(daily_rate, 0) >= 0
        AND COALESCE(hourly_rate, 0) >= 0
        AND standard_work_hours_per_day > 0
        AND tax_value >= 0
        AND sss_employee_share >= 0
        AND philhealth_employee_share >= 0
        AND pagibig_employee_share >= 0
    ),
    CONSTRAINT employee_compensation_effective_dates_check CHECK (
        effective_end_date IS NULL OR effective_start_date <= effective_end_date
    )
);

CREATE TABLE IF NOT EXISTS payroll_runs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payroll_period_id UUID NOT NULL REFERENCES payroll_periods(id) ON DELETE RESTRICT,
    run_number INT NOT NULL DEFAULT 1,
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    total_gross NUMERIC(14,2) NOT NULL DEFAULT 0.00,
    total_deductions NUMERIC(14,2) NOT NULL DEFAULT 0.00,
    total_net NUMERIC(14,2) NOT NULL DEFAULT 0.00,
    employee_count INT NOT NULL DEFAULT 0,
    generated_by UUID NULL,
    generated_at TIMESTAMP NULL,
    finalized_by UUID NULL,
    finalized_at TIMESTAMP NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT payroll_runs_status_check CHECK (status IN ('Draft', 'Computed', 'Finalized', 'Paid')),
    CONSTRAINT payroll_runs_totals_non_negative_check CHECK (
        total_gross >= 0 AND total_deductions >= 0 AND total_net >= 0 AND employee_count >= 0
    ),
    CONSTRAINT payroll_runs_unique_period_run UNIQUE (payroll_period_id, run_number)
);

CREATE TABLE IF NOT EXISTS payroll_line_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payroll_run_id UUID NOT NULL REFERENCES payroll_runs(id) ON DELETE CASCADE,
    employee_id UUID NOT NULL REFERENCES employees(id) ON DELETE RESTRICT,
    attendance_days NUMERIC(6,2) NOT NULL DEFAULT 0.00,
    attendance_hours NUMERIC(8,2) NOT NULL DEFAULT 0.00,
    late_minutes INT NOT NULL DEFAULT 0,
    undertime_minutes INT NOT NULL DEFAULT 0,
    overtime_hours NUMERIC(8,2) NOT NULL DEFAULT 0.00,
    basic_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    overtime_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    leave_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    allowance_total NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    adjustment_earnings NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    gross_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    tax_amount NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    sss_amount NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    philhealth_amount NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    pagibig_amount NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    loan_deductions NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    adjustment_deductions NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    total_deductions NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    net_pay NUMERIC(12,2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'Unpaid',
    payment_reference VARCHAR(100) NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT payroll_line_items_non_negative_check CHECK (
        attendance_days >= 0
        AND attendance_hours >= 0
        AND late_minutes >= 0
        AND undertime_minutes >= 0
        AND overtime_hours >= 0
        AND basic_pay >= 0
        AND overtime_pay >= 0
        AND leave_pay >= 0
        AND allowance_total >= 0
        AND adjustment_earnings >= 0
        AND gross_pay >= 0
        AND tax_amount >= 0
        AND sss_amount >= 0
        AND philhealth_amount >= 0
        AND pagibig_amount >= 0
        AND loan_deductions >= 0
        AND adjustment_deductions >= 0
        AND total_deductions >= 0
        AND net_pay >= 0
    ),
    CONSTRAINT payroll_line_items_payment_status_check CHECK (payment_status IN ('Unpaid', 'Paid', 'Hold')),
    CONSTRAINT payroll_line_items_unique_run_employee UNIQUE (payroll_run_id, employee_id)
);

CREATE TABLE IF NOT EXISTS payroll_adjustments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payroll_line_item_id UUID NOT NULL REFERENCES payroll_line_items(id) ON DELETE CASCADE,
    adjustment_type VARCHAR(20) NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount NUMERIC(12,2) NOT NULL,
    reason TEXT NOT NULL,
    created_by UUID NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT payroll_adjustments_type_check CHECK (adjustment_type IN ('Earning', 'Deduction')),
    CONSTRAINT payroll_adjustments_amount_check CHECK (amount >= 0)
);

CREATE INDEX IF NOT EXISTS idx_payroll_period_status ON payroll_periods(status);
CREATE INDEX IF NOT EXISTS idx_payroll_period_dates ON payroll_periods(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_comp_employee_active ON employee_compensation(employee_id, is_active, effective_start_date);
CREATE INDEX IF NOT EXISTS idx_payroll_runs_period ON payroll_runs(payroll_period_id, status);
CREATE INDEX IF NOT EXISTS idx_payroll_line_items_run ON payroll_line_items(payroll_run_id);
CREATE INDEX IF NOT EXISTS idx_payroll_line_items_employee ON payroll_line_items(employee_id);
CREATE INDEX IF NOT EXISTS idx_payroll_adjustments_line_item ON payroll_adjustments(payroll_line_item_id);
