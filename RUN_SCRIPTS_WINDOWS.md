# Running Scripts on Windows (XAMPP)

## Problem
PowerShell doesn't recognize `php` or `psql` commands because they're not in your PATH.

## Solution: Use Full Paths

### 1. Find Your Paths

**PHP Location (XAMPP):**
```
C:\xampp\php\php.exe
```

**PostgreSQL psql Location:**
```
C:\Program Files\PostgreSQL\16\bin\psql.exe
```
(Version number may vary: 14, 15, 16, etc.)

---

## Running PHP Scripts

### Test Deduction Calculator
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\HRIS\test_deduction_calculator.php
```

### Update Employee Deductions
```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\HRIS\update_correct_deductions.php
```

---

## Running SQL Scripts

### Fix Employee Salaries
```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d your_database_name -f C:\xampp\htdocs\HRIS\docs\migrations\fix_employee_salaries.sql
```

### Add Sample Attendance
```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d your_database_name -f C:\xampp\htdocs\HRIS\docs\migrations\add_sample_attendance.sql
```

### Reset Payroll (Testing Only)
```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d your_database_name -f C:\xampp\htdocs\HRIS\docs\migrations\reset_payroll_for_testing.sql
```

---

## Replace These Values

1. **your_database_name** - Your actual database name (e.g., `hris`, `hris_db`)
2. **PostgreSQL version** - Check your version (14, 15, 16, etc.)
3. **HRIS path** - If your project is in a different location

---

## Easier Method: Use pgAdmin

Instead of command line, you can use pgAdmin:

1. Open pgAdmin
2. Connect to your database
3. Right-click on your database → Query Tool
4. Open the SQL file (File → Open)
5. Click Execute (F5)

---

## Or Add to PATH (One-Time Setup)

To use `php` and `psql` without full paths:

### Add PHP to PATH:
1. Open System Properties → Environment Variables
2. Edit "Path" variable
3. Add: `C:\xampp\php`
4. Click OK
5. Restart PowerShell

### Add PostgreSQL to PATH:
1. Same steps
2. Add: `C:\Program Files\PostgreSQL\16\bin`
3. Click OK
4. Restart PowerShell

After this, you can use:
```powershell
php test_deduction_calculator.php
psql -U postgres -d your_db -f docs/migrations/fix_employee_salaries.sql
```

---

## Quick Commands (Copy-Paste Ready)

**Test Calculator:**
```powershell
C:\xampp\php\php.exe test_deduction_calculator.php
```

**Update Deductions:**
```powershell
C:\xampp\php\php.exe update_correct_deductions.php
```

**Fix Salaries (SQL):**
```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d hris -f docs\migrations\fix_employee_salaries.sql
```

**Add Attendance (SQL):**
```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d hris -f docs\migrations\add_sample_attendance.sql
```

Replace `hris` with your actual database name!
