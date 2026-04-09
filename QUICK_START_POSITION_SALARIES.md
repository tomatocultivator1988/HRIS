# Quick Start: Position-Based Salaries

## 🎯 What Changed?

**BEFORE:** You had to set salary for each employee individually
**NOW:** You set salary once per position, all employees inherit it

Example:
- Set "Manager" position salary to ₱50,000
- All employees with position "Manager" automatically get ₱50,000

## 🚀 Quick Setup (3 Steps)

### Step 1: Run SQL Migration (2 minutes)

1. Open Supabase Dashboard: https://supabase.com/dashboard
2. Go to SQL Editor
3. Copy this entire SQL and run it:

```sql
-- Create position_salaries table for position-based compensation
CREATE TABLE IF NOT EXISTS position_salaries (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    position VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(100),
    payroll_type VARCHAR(20) NOT NULL DEFAULT 'Monthly',
    base_salary DECIMAL(12, 2) NOT NULL DEFAULT 0,
    daily_rate DECIMAL(12, 2) NOT NULL DEFAULT 0,
    hourly_rate DECIMAL(12, 2) NOT NULL DEFAULT 0,
    sss_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    philhealth_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    pagibig_employee_share DECIMAL(12, 2) NOT NULL DEFAULT 0,
    tax_value DECIMAL(12, 2) NOT NULL DEFAULT 0,
    standard_work_hours_per_day DECIMAL(5, 2) NOT NULL DEFAULT 8.00,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    CONSTRAINT position_salaries_payroll_type_check CHECK (payroll_type IN ('Monthly', 'Daily', 'Hourly'))
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_position_salaries_position ON position_salaries(position, is_active);

-- Insert default positions from existing employees
INSERT INTO position_salaries (position, department, base_salary, payroll_type)
SELECT DISTINCT 
    position,
    department,
    30000.00 as base_salary,
    'Monthly' as payroll_type
FROM employees
WHERE position IS NOT NULL 
  AND position != ''
  AND NOT EXISTS (
    SELECT 1 FROM position_salaries ps WHERE ps.position = employees.position
  );

COMMENT ON TABLE position_salaries IS 'Stores salary information by position/role';
COMMENT ON COLUMN position_salaries.position IS 'Job position/title (e.g., Manager, Developer, Staff)';
COMMENT ON COLUMN position_salaries.department IS 'Department the position belongs to';
COMMENT ON COLUMN position_salaries.payroll_type IS 'How salary is calculated: Monthly, Daily, or Hourly';
```

4. Click "Run" - you should see "Success"

### Step 2: Set Position Salaries (5 minutes)

1. Log in to HRIS as admin
2. Click "Manage Salaries" in sidebar
3. You'll see all positions (Manager, Developer, Staff, etc.)
4. Click "Edit" on each position
5. Set the salary and deductions
6. Click "Save Changes"

### Step 3: Test Payroll (2 minutes)

1. Go to "Payroll" in sidebar
2. Create a new period
3. Calculate payroll
4. Verify employees get correct salaries

## ✅ Done!

That's it! Your system now uses position-based salaries.

## 📊 Example

Let's say you have:
- 5 Developers
- 3 Managers  
- 10 Staff

**Old way:** Set salary 18 times (once per employee)
**New way:** Set salary 3 times (once per position)

When you give Developers a raise:
**Old way:** Update 5 employee records
**New way:** Update 1 position record

## 🔍 How to Verify It's Working

1. Go to "Manage Salaries" - you should see positions, not employees
2. Edit a position and save
3. Generate payroll
4. Check that employees with that position got the correct salary

## ❓ Troubleshooting

**Problem:** "Manage Salaries" page is blank
- **Solution:** Run the SQL migration in Step 1

**Problem:** Positions show ₱0 salary
- **Solution:** Click "Edit" and set the salary values

**Problem:** Payroll shows ₱0 for employees
- **Solution:** Make sure their position has a salary set in "Manage Salaries"

**Problem:** SQL migration fails
- **Solution:** Check if table already exists. If yes, you're good to go!

## 💡 Pro Tips

1. Set realistic default salaries for each position
2. Update position salaries when giving company-wide raises
3. Use the same position names consistently across employees
4. Review position salaries quarterly

## 🎉 Benefits

✅ Faster salary management
✅ Consistent pay across same positions
✅ Easy to give position-wide raises
✅ Less data entry errors
✅ Industry standard approach

---

Need help? Check the detailed guide in `POSITION_SALARY_SETUP.md`
