# Employee Creation Guide - Automatic Auth User Creation

## Overview
Ang employee creation system nag-automatic na naga-create sang Supabase authentication user! Wala na kinahanglan manual creation sa Supabase Dashboard.

---

## How It Works

### When Admin Creates Employee:

1. **Admin fills employee form** sa admin dashboard
2. **System automatically creates:**
   - ✅ Supabase authentication user (for login)
   - ✅ Employee database record (for employee data)
   - ✅ Leave credits (auto-initialized)
3. **System generates temporary password**
4. **Admin receives the password** (shown once only)
5. **Employee can login immediately** using:
   - Email: work_email from form
   - Password: temporary password

---

## Employee Creation Flow

### Option 1: Auto-Generate Password (Recommended)
Admin creates employee WITHOUT providing password:
- System generates secure random password
- Format: 8 random chars + year (e.g., `aBc12XyZ2024`)
- Password shown to admin after creation
- Employee should change password after first login

### Option 2: Custom Password
Admin creates employee WITH custom password:
- Admin provides password in form
- Password must meet requirements (min 6 characters)
- Employee uses this password to login

---

## API Request Format

### Create Employee (Auto-Generate Password)
```json
POST /api/employees/create.php

{
  "employee_id": "EMP002",
  "first_name": "Maria",
  "last_name": "Santos",
  "work_email": "maria.santos@company.com",
  "mobile_number": "+63 917 123 4567",
  "department": "HR Department",
  "position": "HR Manager",
  "employment_status": "Regular",
  "date_hired": "2024-02-01"
}
```

### Response (Success)
```json
{
  "success": true,
  "data": {
    "id": "uuid-here",
    "employee_id": "EMP002",
    "first_name": "Maria",
    "last_name": "Santos",
    "work_email": "maria.santos@company.com",
    "temporary_password": "aBc12XyZ2024",
    "password_message": "This is a temporary password. Employee should change it after first login."
  },
  "message": "Employee created successfully"
}
```

### Create Employee (Custom Password)
```json
POST /api/employees/create.php

{
  "employee_id": "EMP003",
  "first_name": "Pedro",
  "last_name": "Reyes",
  "work_email": "pedro.reyes@company.com",
  "department": "Sales",
  "position": "Sales Representative",
  "employment_status": "Regular",
  "date_hired": "2024-03-01",
  "password": "CustomPass123!"
}
```

---

## What Happens Behind the Scenes

### 1. Validation
- Check required fields
- Validate email format
- Check for duplicate employee_id
- Check for duplicate work_email

### 2. Create Supabase Auth User
```php
// System calls Supabase Admin API
POST https://xtfekjcusnnadfgcrzht.supabase.co/auth/v1/admin/users
{
  "email": "maria.santos@company.com",
  "password": "aBc12XyZ2024",
  "email_confirm": true
}
```

### 3. Create Employee Record
```sql
INSERT INTO employees (
  employee_id, supabase_user_id, first_name, last_name, 
  work_email, department, position, ...
) VALUES (
  'EMP002', 'uuid-from-supabase', 'Maria', 'Santos', ...
);
```

### 4. Initialize Leave Credits
```sql
-- Automatically creates leave credits for all leave types
INSERT INTO leave_credits (employee_id, leave_type_id, total_credits, year)
SELECT 'employee-uuid', id, days_allowed, 2024
FROM leave_types;
```

---

## Employee Deletion

### When Admin Deletes Employee:

1. **Soft delete** - Sets `is_active = false`
2. **Disables Supabase auth user** - Employee cannot login
3. **Preserves all data** - Attendance, leave history retained
4. **Audit trail** - Logs who deleted and when

---

## Security Features

### Password Generation
- ✅ Cryptographically secure random generation
- ✅ Minimum 12 characters (8 random + 4 year)
- ✅ Mix of uppercase, lowercase, numbers
- ✅ Unique per employee

### Auth User Creation
- ✅ Uses Supabase Service Role Key (admin privileges)
- ✅ Auto-confirms email (no verification needed)
- ✅ Stores metadata (created_by, created_at)
- ✅ Atomic operation (rollback on failure)

### Password Security
- ✅ Password shown only once to admin
- ✅ Not stored in database (only in Supabase Auth)
- ✅ Employee should change after first login
- ✅ Supabase handles password hashing

---

## Error Handling

### Common Errors:

**Email already exists:**
```json
{
  "success": false,
  "error": "Failed to create Supabase authentication user: User already registered"
}
```
**Solution:** Use different email address

**Invalid email format:**
```json
{
  "success": false,
  "errors": {
    "work_email": "Invalid email format"
  }
}
```
**Solution:** Provide valid email

**Duplicate employee_id:**
```json
{
  "success": false,
  "errors": {
    "employee_id": "Employee ID already exists"
  }
}
```
**Solution:** Use unique employee_id

---

## Testing

### Test Employee Creation:

1. Login as admin: `http://localhost/hris-mvp/dashboard/admin.html`
2. Go to "Employees" section
3. Click "Add Employee"
4. Fill form:
   - Employee ID: EMP002
   - First Name: Test
   - Last Name: Employee
   - Email: test.employee@company.com
   - Department: IT
   - Position: Developer
   - Status: Regular
5. Click "Create"
6. **Copy the temporary password** shown in success message
7. Logout
8. Login as employee: `http://localhost/hris-mvp/dashboard/employee.html`
   - Email: test.employee@company.com
   - Password: [temporary password from step 6]
9. ✅ Should login successfully!

---

## Admin Dashboard Updates Needed

The employee creation form should:
1. ✅ Show success message with temporary password
2. ✅ Provide "Copy Password" button
3. ✅ Warn admin to save password (shown once only)
4. ✅ Optionally allow admin to set custom password

---

## Summary

✅ **Automatic** - No manual Supabase user creation needed
✅ **Secure** - Strong password generation
✅ **Immediate** - Employee can login right away
✅ **Complete** - Auth user + database record + leave credits
✅ **Auditable** - All actions logged
✅ **Reversible** - Soft delete preserves data

**No more manual user creation in Supabase Dashboard!** 🎉
