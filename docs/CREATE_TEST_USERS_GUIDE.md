# How to Create Test Admin and Employee Users

## Overview
Ang Supabase naga-separate sang authentication (login) kag database records. So kailangan mo:
1. Create users sa Supabase Authentication (para sa login)
2. Create records sa database (para sa employee/admin data)

---

## PART 1: Create Authentication Users (Supabase Dashboard)

### Step 1: Go to Supabase Authentication
1. Open browser and go to: https://supabase.com/dashboard/project/xtfekjcusnnadfgcrzht
2. Click **"Authentication"** sa left sidebar
3. Click **"Users"** tab

### Step 2: Create Admin User
1. Click **"Add User"** button (green button sa top right)
2. Fill in the form:
   - **Email:** `admin@company.com`
   - **Password:** `Admin123!` (or any password you want)
   - **Auto Confirm User:** ✅ Check this (para indi na mag-verify email)
3. Click **"Create User"**
4. **IMPORTANT:** Copy ang **User ID (UUID)** - makita mo ni sa user list
   - Example: `a1b2c3d4-e5f6-7890-abcd-ef1234567890`
   - Save this UUID - kailangan mo ni later!

### Step 3: Create Employee User
1. Click **"Add User"** button again
2. Fill in the form:
   - **Email:** `employee@company.com`
   - **Password:** `Employee123!` (or any password you want)
   - **Auto Confirm User:** ✅ Check this
3. Click **"Create User"**
4. **IMPORTANT:** Copy ang **User ID (UUID)**
   - Save this UUID - kailangan mo ni later!

---

## PART 2: Create Database Records (SQL Editor)

### Step 1: Open SQL Editor
1. Sa Supabase Dashboard, click **"SQL Editor"** sa left sidebar
2. Click **"New Query"**

### Step 2: Insert Admin Record
Copy-paste this SQL, then **REPLACE** ang `YOUR_ADMIN_UUID_HERE` with the actual UUID from Part 1, Step 2:

```sql
-- Insert Admin Record
INSERT INTO admins (supabase_user_id, name, email, role, is_active) 
VALUES (
    'YOUR_ADMIN_UUID_HERE',  -- ⚠️ REPLACE with actual UUID
    'System Administrator',
    'admin@company.com',
    'admin',
    true
);
```

Example (with actual UUID):
```sql
INSERT INTO admins (supabase_user_id, name, email, role, is_active) 
VALUES (
    'a1b2c3d4-e5f6-7890-abcd-ef1234567890',  -- ✅ Actual UUID
    'System Administrator',
    'admin@company.com',
    'admin',
    true
);
```

Click **"Run"** button.

### Step 3: Insert Employee Record
Copy-paste this SQL, then **REPLACE** ang `YOUR_EMPLOYEE_UUID_HERE` with the actual UUID from Part 1, Step 3:

```sql
-- Insert Employee Record
INSERT INTO employees (
    employee_id,
    supabase_user_id,
    first_name,
    last_name,
    work_email,
    mobile_number,
    department,
    position,
    employment_status,
    date_hired,
    is_active
) VALUES (
    'EMP001',
    'YOUR_EMPLOYEE_UUID_HERE',  -- ⚠️ REPLACE with actual UUID
    'Juan',
    'Dela Cruz',
    'employee@company.com',
    '+63 912 345 6789',
    'IT Department',
    'Software Developer',
    'Regular',
    '2024-01-15',
    true
);
```

Example (with actual UUID):
```sql
INSERT INTO employees (
    employee_id,
    supabase_user_id,
    first_name,
    last_name,
    work_email,
    mobile_number,
    department,
    position,
    employment_status,
    date_hired,
    is_active
) VALUES (
    'EMP001',
    'b2c3d4e5-f6a7-8901-bcde-f12345678901',  -- ✅ Actual UUID
    'Juan',
    'Dela Cruz',
    'employee@company.com',
    '+63 912 345 6789',
    'IT Department',
    'Software Developer',
    'Regular',
    '2024-01-15',
    true
);
```

Click **"Run"** button.

---

## PART 3: Verify Users Were Created

Run this SQL to verify:

```sql
-- Check Admin
SELECT 'Admin User' as type, * FROM admins WHERE email = 'admin@company.com';

-- Check Employee
SELECT 'Employee User' as type, * FROM employees WHERE work_email = 'employee@company.com';
```

You should see:
- ✅ 1 admin record
- ✅ 1 employee record

---

## PART 4: Test Login

### Test Admin Login:
1. Go to: `http://localhost/hris-mvp/dashboard/admin.html`
2. Login with:
   - Email: `admin@company.com`
   - Password: `Admin123!` (or whatever you set)
3. Should redirect to admin dashboard

### Test Employee Login:
1. Go to: `http://localhost/hris-mvp/dashboard/employee.html`
2. Login with:
   - Email: `employee@company.com`
   - Password: `Employee123!` (or whatever you set)
3. Should redirect to employee dashboard

---

## Troubleshooting

### Problem: "User not found" error when logging in
**Solution:** Check if:
- User exists in Supabase Authentication > Users
- Email is correct
- Password is correct

### Problem: "Employee/Admin record not found" after login
**Solution:** Check if:
- You ran the INSERT SQL statements
- The `supabase_user_id` in database matches the UUID from Supabase Auth
- Run the verification SQL to check records

### Problem: Can't find the User ID (UUID)
**Solution:**
1. Go to Supabase Dashboard > Authentication > Users
2. Click on the user email
3. The UUID is shown as "ID" field
4. Click the copy icon to copy it

---

## Quick Copy-Paste Template

After creating users in Supabase Auth, copy their UUIDs and use this template:

```sql
-- Replace UUIDs below with actual values from Supabase Authentication

-- Insert Admin
INSERT INTO admins (supabase_user_id, name, email, role, is_active) 
VALUES ('PASTE_ADMIN_UUID_HERE', 'System Administrator', 'admin@company.com', 'admin', true);

-- Insert Employee
INSERT INTO employees (
    employee_id, supabase_user_id, first_name, last_name, work_email, 
    mobile_number, department, position, employment_status, date_hired, is_active
) VALUES (
    'EMP001', 'PASTE_EMPLOYEE_UUID_HERE', 'Juan', 'Dela Cruz', 'employee@company.com',
    '+63 912 345 6789', 'IT Department', 'Software Developer', 'Regular', '2024-01-15', true
);

-- Verify
SELECT 'Admin' as type, * FROM admins WHERE email = 'admin@company.com'
UNION ALL
SELECT 'Employee' as type, * FROM employees WHERE work_email = 'employee@company.com';
```

---

## Summary

✅ Create 2 users in Supabase Authentication (admin & employee)
✅ Copy their UUIDs
✅ Run INSERT SQL statements with actual UUIDs
✅ Verify records were created
✅ Test login on both portals

**Done!** Your test users are ready! 🎉
