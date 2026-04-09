# Payroll Debugging Tools

I've created several tools to help you debug the 400 error:

## 1. Test Page (test_payroll_api.html)

**What it does**: Tests the payroll API directly in your browser with detailed error messages

**How to use**:
1. Open `http://localhost/HRIS/test_payroll_api.html` in your browser
2. Click "Test Auth Token" to verify you're logged in
3. Click "Create Period" to see the exact error message
4. The response will show you exactly what's wrong

**Why it's useful**: Shows the actual error message from the server, not just "400"

## 2. Diagnostic Script (check_payroll_setup.php)

**What it does**: Checks if payroll tables exist and shows existing data

**How to use**:
```bash
php check_payroll_setup.php
```

**What it checks**:
- Database connection
- If payroll tables exist
- Existing payroll periods
- Employee compensation records
- Active employees count

**Why it's useful**: Tells you if you need to run migrations

## 3. Troubleshooting Guide (TROUBLESHOOT_400_ERROR.md)

**What it does**: Step-by-step guide to fix the 400 error

**Covers**:
- What 400 means
- Common causes
- Quick fixes
- SQL queries to check your data

## 4. Simple Payroll UI (src/Views/payroll/simple.php)

**What it does**: Simplified payroll interface with better error messages

**How to use**:
1. Go to `http://localhost/HRIS/payroll/simple`
2. Dates are pre-filled for you
3. Click "Create Period"
4. Error messages now show more detail

## Most Likely Causes of Your 400 Error

### 1. Duplicate Period Code (90% chance)
You already created a period with code "2026-04"

**Fix**: Change the code to "2026-05" or "APR-2026"

### 2. Tables Don't Exist (5% chance)
Payroll tables haven't been created

**Fix**: Run `psql -U postgres -d your_db -f docs/migrations/create_payroll_tables.sql`

### 3. Overlapping Dates (5% chance)
Another period exists with the same dates

**Fix**: Use different dates or delete the old period

## Quick Debug Steps

1. Open `test_payroll_api.html`
2. Click "Test Auth Token" (should succeed)
3. Click "Create Period" (will show exact error)
4. Read the error message
5. Follow the fix in TROUBLESHOOT_400_ERROR.md

## What to Check in Database

```sql
-- See existing periods
SELECT code, start_date, end_date, status FROM payroll_periods;

-- If you see a period with code "2026-04", that's your problem!
-- Either delete it or use a different code
```

## Need More Help?

1. Run `php check_payroll_setup.php` and share the output
2. Open `test_payroll_api.html` and share the error message
3. Check browser console (F12) for JavaScript errors
