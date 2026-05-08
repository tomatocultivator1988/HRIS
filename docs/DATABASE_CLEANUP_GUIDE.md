# 🧹 Database Cleanup Guide

## Option 1: Clean All Data But Keep Admin (Recommended)

Gamiton ini kung gusto mo lang i-delete ang test data pero ibilin ang admin account.

### Steps:

1. **Open Supabase Dashboard**
   - Go to your Supabase project
   - Click "SQL Editor" sa sidebar

2. **Run Cleanup Script**
   - Open `docs/migrations/cleanup_all_data_keep_admin.sql`
   - Copy ang tanan nga SQL code
   - Paste sa Supabase SQL Editor
   - Click "Run"

3. **Verify**
   - Check ang results - dapat may 1 employee lang (admin)
   - Try login using: `admin@company.com`

### What Gets Deleted:
- ✅ All test employees (except admin)
- ✅ All attendance records (except admin's)
- ✅ All leave requests (except admin's)
- ✅ All payroll data
- ✅ All recruitment data (job postings, applicants, evaluations)
- ✅ All announcements
- ✅ All documents (except admin's)
- ✅ All user sessions (except admin's)

### What Gets Kept:
- ✅ Admin employee account
- ✅ Admin user in auth.users
- ✅ Admin's attendance/leave/documents
- ✅ Database structure (tables, columns)
- ✅ Leave types
- ✅ Position salaries

---

## Option 2: Full Project Reset (Nuclear Option)

Gamiton ini kung gusto mo mag-start from scratch completely.

### Steps:

1. **Backup Important Data (Optional)**
   - Export any data you want to keep
   - Save your `.env` file

2. **Reset Supabase Project**
   - Go to Supabase Dashboard
   - Settings > General
   - Scroll down to "Danger Zone"
   - Click "Pause Project" then "Delete Project"
   - Create new project with same name

3. **Run All Migrations**
   - Open `RUN_THIS_SQL_IN_SUPABASE.sql`
   - Copy all SQL code
   - Paste sa Supabase SQL Editor
   - Click "Run"

4. **Create Admin User**
   - Go to Authentication > Users
   - Click "Add User"
   - Email: `admin@company.com`
   - Password: `Admin123!` (or your choice)
   - Click "Create User"
   - **COPY THE UUID** (important!)

5. **Insert Admin Employee**
   - Open `docs/migrations/insert_fresh_admin.sql`
   - Find line: `'YOUR-SUPABASE-USER-UUID-HERE'::uuid`
   - Replace with the UUID you copied
   - Run the script in SQL Editor

6. **Update .env File**
   - Update `SUPABASE_URL` if changed
   - Update `SUPABASE_ANON_KEY` if changed
   - Update `SUPABASE_SERVICE_ROLE_KEY` if changed

7. **Test Login**
   - Go to your HRIS system
   - Login with: `admin@company.com` / `Admin123!`

---

## Option 3: Manual Cleanup (Selective)

Kung gusto mo selective lang ang delete:

### Delete Specific Data:

```sql
-- Delete all employees except admin
DELETE FROM employees 
WHERE work_email != 'admin@company.com';

-- Delete all attendance records
DELETE FROM attendance;

-- Delete all leave requests
DELETE FROM leave_requests;

-- Delete all payroll data
DELETE FROM payroll_adjustments;
DELETE FROM payroll_line_items;
DELETE FROM payroll_runs;
DELETE FROM payroll_periods;

-- Delete all recruitment data
DELETE FROM evaluations;
DELETE FROM applicants;
DELETE FROM job_postings;

-- Delete all announcements
DELETE FROM announcements;
```

---

## Quick Reference

### Admin Default Credentials:
- **Email:** `admin@company.com`
- **Password:** `Admin123!` (or what you set)

### Important Files:
- `docs/migrations/cleanup_all_data_keep_admin.sql` - Clean but keep admin
- `docs/migrations/insert_fresh_admin.sql` - Create fresh admin
- `RUN_THIS_SQL_IN_SUPABASE.sql` - Full database setup

### Common Issues:

**Problem:** "Cannot delete because of foreign key constraint"
**Solution:** Run the cleanup script - it deletes in correct order

**Problem:** "Admin user not found after cleanup"
**Solution:** Check if email is correct in the script (line 15)

**Problem:** "Cannot login after reset"
**Solution:** Make sure you created the auth user first, then ran insert_fresh_admin.sql

---

## Before Production Checklist

Before launching to real clients:

- [ ] Run cleanup script to remove test data
- [ ] Verify admin can login
- [ ] Test creating a new employee
- [ ] Test attendance tracking
- [ ] Test leave request workflow
- [ ] Test payroll generation
- [ ] Check all reports work
- [ ] Verify document upload works
- [ ] Test recruitment module
- [ ] Check security (non-admin cannot access admin features)

---

## Need Help?

If may problema ka:
1. Check ang Supabase logs (Logs > Postgres Logs)
2. Verify ang foreign key relationships
3. Make sure nag-run ang script in correct order
4. Check if admin user exists in auth.users

**Tip:** Always backup before running cleanup scripts! 🔒
