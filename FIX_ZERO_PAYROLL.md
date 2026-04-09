# Fix Zero Payroll Issue

## The Problem

Everyone got ₱0.00 because:
1. **Salaries are ₱0.00** - The seed script created compensation with zero values
2. **No attendance for April 2026** - It's a future date with no attendance records

## Quick Fix (Run These in Order)

### Step 1: Fix Employee Salaries

Run this in your database:
```bash
psql -U postgres -d your_database -f docs/migrations/fix_employee_salaries.sql
```

Or manually in pgAdmin/SQL:
```sql
UPDATE employee_compensation
SET 
    base_salary = 30000.00,
    daily_rate = 1363.64,
    hourly_rate = 170.45,
    tax_value = 2500.00,
    sss_employee_share = 581.30,
    philhealth_employee_share = 450.00,
    pagibig_employee_share = 100.00
WHERE is_active = TRUE
  AND base_salary = 0.00;
```

### Step 2: Add Sample Attendance

Run this in your database:
```bash
psql -U postgres -d your_database -f docs/migrations/add_sample_attendance.sql
```

This creates attendance records for all employees for April 2026 (22 working days, 8 hours each).

### Step 3: Regenerate Payroll

1. Go back to `/payroll/simple`
2. Use the same Period ID: `7dc7690e-d8f8-4ffb-b8bd-0846101a69ad`
3. Click "Calculate Everyone's Payroll" again
4. Now you should see real amounts!

## Expected Results After Fix

Each employee should get approximately:
- **Basic Pay**: ₱30,000 (for 22 days)
- **Deductions**: ₱3,631.30 (tax + SSS + PhilHealth + PagIBIG)
- **Net Pay**: ₱26,368.70

## Alternative: Use Current Month

Instead of April 2026 (future), use the current month:

1. Create a new period for the current month
2. Make sure employees have attendance records for this month
3. Generate payroll

## Verify the Fix

After running the SQL scripts, check:

```sql
-- Check salaries
SELECT first_name, last_name, base_salary 
FROM employee_compensation ec
JOIN employees e ON e.id = ec.employee_id
WHERE ec.is_active = TRUE;

-- Check attendance
SELECT COUNT(*) as attendance_records
FROM attendance
WHERE date >= '2026-04-01' AND date <= '2026-04-30';
```

Both should show non-zero values.

## Why This Happened

The original `seed_payroll_defaults.sql` was designed to create placeholder records with ₱0.00 values, expecting you to manually set salaries later. I've now fixed it to use realistic default values.
