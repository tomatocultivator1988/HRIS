# Payroll Quick Start Guide

## Problem: Can't Create Payroll Period

You're getting 422 errors because the form validation is failing. Here's how to fix it:

## Step 1: Make Sure Database Tables Exist

Run these migrations in order:

```bash
# 1. Create payroll tables
psql -U your_user -d your_database -f docs/migrations/create_payroll_tables.sql

# 2. Seed default data for all employees
psql -U your_user -d your_database -f docs/migrations/seed_payroll_defaults.sql
```

## Step 2: How to Use the Payroll UI

### Create Payroll Period (First Time Setup)

1. Go to `/payroll` page
2. Fill in ALL fields in "Create Payroll Period" section:
   - **Code**: Type something like `2026-04` (year-month format)
   - **Start Date**: Click the date picker and select April 1, 2026
   - **End Date**: Click the date picker and select April 30, 2026
   - **Pay Date**: Click the date picker and select May 5, 2026
3. Click "Create Period"
4. Check the "API Output" section at the bottom - you should see success message

### Generate Payroll (Calculate Everyone's Pay)

1. Copy the Period ID from the output above (it's a UUID like `123e4567-e89b-12d3-a456-426614174000`)
2. Paste it in the "Payroll Period ID" field under "Generate Payroll Run"
3. Keep "Include overtime" checked
4. Leave "Employee IDs" empty (to calculate for everyone)
5. Click "Generate Run"
6. Check output - you'll see everyone's calculated pay

### View Results

1. Copy the Run ID from the output
2. Paste it in "Payroll Run ID" under "Run Actions"
3. Click "Get Run"
4. You'll see all employees and their calculated pay

## Step 3: Employees View Their Payslips

Employees can go to `/payslips` and see their pay history.

## Common Errors

### 422 Error
- **Cause**: Empty date fields or missing code
- **Fix**: Make sure ALL 4 fields are filled before clicking "Create Period"

### 404 Error on /api/payroll/runs/:1
- **Cause**: Empty Run ID field
- **Fix**: You need to create a period and generate a run first, then copy the Run ID

### 400 Error "Payroll period code already exists"
- **Cause**: You already created a period with that code
- **Fix**: Use a different code (e.g., `2026-05` instead of `2026-04`)

## What Each Button Does (Simplified)

- **Create Period**: Define the pay period (do this once per month)
- **Generate Run**: Calculate everyone's payroll (do this once per period)
- **Get Run**: View the results
- **Recompute**: Recalculate one employee (if you fixed their attendance)
- **Finalize/Approve/Pay**: Optional workflow steps (you can ignore these for now)
- **Adjust**: Add manual bonus or deduction to one employee

## Minimum Steps to Get Paid

1. Create Period (once per month)
2. Generate Run (once per period)
3. Done! Employees can view payslips

You can ignore: Finalize, Approve, Pay, Reverse buttons unless you need approval workflow.
