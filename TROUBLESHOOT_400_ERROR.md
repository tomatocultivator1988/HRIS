# Troubleshooting 400 Error on Payroll Period Creation

## The Error
```
POST http://localhost/HRIS/api/payroll/periods 400 (Bad Request)
```

## What 400 Means
The server understood your request but rejected it. This usually means:
1. **Duplicate period code** - You already created a period with that code (e.g., "2026-04")
2. **Overlapping dates** - Another period exists with overlapping dates
3. **Database tables don't exist** - Payroll tables haven't been created yet

## Quick Fixes

### Fix 1: Check if Tables Exist

Run the diagnostic script:
```bash
php check_payroll_setup.php
```

If tables don't exist, create them:
```bash
# Windows (PowerShell)
psql -U postgres -d your_database_name -f docs/migrations/create_payroll_tables.sql

# Or if using pgAdmin, open and run the SQL file manually
```

### Fix 2: Change the Period Code

If you already created a period with code "2026-04", try a different code:
- Change `2026-04` to `2026-05`
- Or use `2026-04-v2`
- Or use `APR-2026`

### Fix 3: Check Existing Periods

Open your database and run:
```sql
SELECT code, start_date, end_date, status 
FROM payroll_periods 
ORDER BY start_date DESC;
```

If you see existing periods, either:
- Use a different code
- Delete the old period: `DELETE FROM payroll_periods WHERE code = '2026-04';`

### Fix 4: Use the Test Page

1. Open `test_payroll_api.html` in your browser
2. Click "Test Auth Token" first
3. If auth works, try "Create Period"
4. You'll see the exact error message

## Common Scenarios

### Scenario 1: "Payroll period code already exists"
**Solution**: Change the code in the form to something else

### Scenario 2: "Payroll period overlaps with an existing period"
**Solution**: Check existing periods and use non-overlapping dates

### Scenario 3: Tables don't exist
**Solution**: Run the migration:
```bash
psql -U postgres -d your_database -f docs/migrations/create_payroll_tables.sql
```

### Scenario 4: No error message shown
**Solution**: 
1. Open browser DevTools (F12)
2. Go to Console tab
3. Try creating period again
4. Look for the actual error message

## Step-by-Step Debug Process

1. **Open test_payroll_api.html** in browser
2. **Click "Test Auth Token"** - Should show your user info
3. **Click "Create Period"** - Will show exact error
4. **Read the error message** - It will tell you exactly what's wrong

## Still Not Working?

Check the PHP error log:
- Windows XAMPP: `C:\xampp\apache\logs\error.log`
- Look for recent errors related to payroll

Or check the browser console (F12 → Console tab) for JavaScript errors.

## Quick Test Query

Run this in your database to see if everything is set up:
```sql
-- Check if tables exist
SELECT table_name 
FROM information_schema.tables 
WHERE table_name LIKE 'payroll%';

-- Check existing periods
SELECT * FROM payroll_periods;

-- Check employee compensation
SELECT COUNT(*) FROM employee_compensation WHERE is_active = TRUE;
```

If any of these fail, you need to run the migrations.
