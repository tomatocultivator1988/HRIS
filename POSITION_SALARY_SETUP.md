# Position-Based Salary System Setup

## Overview
The system has been updated to use position-based salaries instead of per-employee compensation. This means:
- You set the salary once for each position (e.g., Manager, Developer, Staff)
- All employees with that position automatically inherit those salary settings
- Much easier to manage and maintain

## Setup Instructions

### Step 1: Run the Database Migration

You need to create the `position_salaries` table in your Supabase database.

1. Go to your Supabase Dashboard: https://supabase.com/dashboard
2. Select your project
3. Go to the SQL Editor
4. Copy and paste the contents of `docs/migrations/create_position_salaries_table.sql`
5. Click "Run" to execute the migration

The migration will:
- Create the `position_salaries` table
- Automatically populate it with positions from your existing employees
- Set a default salary of ₱30,000 for each position (you can change this)

### Step 2: Set Salaries for Each Position

1. Log in to the HRIS as admin
2. Go to "Manage Salaries" in the sidebar
3. You'll see a list of all positions in your organization
4. Click "Edit" on each position to set:
   - Payroll Type (Monthly/Daily/Hourly)
   - Base Salary
   - Daily Rate
   - Hourly Rate
   - Government Deductions (SSS, PhilHealth, Pag-IBIG)
   - Withholding Tax
5. Click "Save Changes"

### Step 3: Generate Payroll

Once you've set up the position salaries:
1. Go to "Payroll" in the sidebar
2. Create a new payroll period
3. Calculate payroll
4. The system will automatically use the salary settings from each employee's position

## How It Works

When you generate payroll:
1. System looks at each employee's position
2. Fetches the salary settings for that position from `position_salaries` table
3. Calculates their pay based on attendance and those settings
4. All employees with the same position get the same base salary and deductions

## Benefits

- **Easier Management**: Update salary once for all employees in a position
- **Consistency**: All employees in the same position have the same compensation structure
- **Scalability**: Add new employees without setting up individual compensation
- **Standard Practice**: This is how most HRIS/payroll systems work

## Backward Compatibility

The old per-employee compensation system is still supported as a fallback:
- If a position salary is not found, the system will look for employee-specific compensation
- This ensures existing payroll data continues to work
- However, creating new per-employee compensation is now disabled

## Need Help?

If you encounter any issues:
1. Check that the migration ran successfully in Supabase
2. Verify that positions are showing up in the "Manage Salaries" page
3. Make sure each position has salary values set (not zero)
4. Check the browser console for any error messages
