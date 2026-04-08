# Password Management System

## Overview
Comprehensive password management system for HRIS with force password change on first login (employees only), change password functionality, and admin password reset capabilities.

## Features

### 1. Force Password Change on First Login (Employees Only)
- Employees can be flagged to change their password on next login
- Automatically redirects to change password page after login
- Prevents access to other pages until password is changed
- Only applies to employees, not admins

### 2. Change Password Page
- Available to all users (admins and employees)
- Requires current password verification
- Password strength validation:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number
- Real-time password validation
- Show/hide password toggle
- Accessible from user profile or direct URL

### 3. Admin Password Reset
- Admins can reset employee passwords
- Option to force password change on next login
- Audit logging for all password resets
- No current password required (admin privilege)

## Database Schema

### Migration File
Location: `docs/migrations/add_password_management.sql`

```sql
-- Add to employees table
ALTER TABLE employees 
ADD COLUMN IF NOT EXISTS force_password_change BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP;

-- Add to admins table
ALTER TABLE admins 
ADD COLUMN IF NOT EXISTS password_changed_at TIMESTAMP;
```

### New Columns

#### employees table
- `force_password_change` (BOOLEAN): Flag to force password change on next login
- `password_changed_at` (TIMESTAMP): Last password change timestamp

#### admins table
- `password_changed_at` (TIMESTAMP): Last password change timestamp

## API Endpoints

### 1. Change Password
**Endpoint:** `POST /api/password/change`  
**Authentication:** Required (all users)  
**Request Body:**
```json
{
  "current_password": "OldPass123",
  "new_password": "NewPass123",
  "confirm_password": "NewPass123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

### 2. Admin Reset Password
**Endpoint:** `POST /api/password/admin-reset`  
**Authentication:** Required (admin only)  
**Request Body:**
```json
{
  "employee_id": "123",
  "new_password": "TempPass123",
  "force_change": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "employee_id": "123",
    "force_change": true
  },
  "message": "Password reset successfully"
}
```

### 3. Check Force Password Change
**Endpoint:** `GET /api/password/check-force-change`  
**Authentication:** Required  
**Response:**
```json
{
  "success": true,
  "data": {
    "force_password_change": true,
    "password_changed_at": "2024-01-15 10:30:00"
  }
}
```

## Web Routes

### Change Password Form
**URL:** `/password/change`  
**Authentication:** Required  
**Description:** Shows password change form with validation

## Implementation Details

### Controllers

#### PasswordController
Location: `src/Controllers/PasswordController.php`

Methods:
- `changePasswordForm()` - Display change password form
- `changePassword()` - Handle password change request
- `adminResetPassword()` - Admin reset employee password
- `checkForcePasswordChange()` - Check if user needs to change password

### Services

#### AuthService Updates
Location: `src/Services/AuthService.php`

New Methods:
- `changePassword()` - Change user password via Supabase
- `adminResetPassword()` - Admin reset password via Supabase Admin API

### Models

#### User Model Updates
Location: `src/Models/User.php`

New Methods:
- `updateForcePasswordChange()` - Update force password change flag
- `updatePasswordChangedAt()` - Update password changed timestamp
- `needsPasswordChange()` - Check if employee needs to change password

### Views

#### Change Password Form
Location: `src/Views/auth/change-password.php`

Features:
- Current password field
- New password field with strength indicator
- Confirm password field
- Show/hide password toggles
- Real-time validation
- Force change warning banner

### Frontend

#### AuthManager Updates
Location: `public/assets/js/auth.js`

Updates:
- Check for `force_password_change` flag after login
- Redirect to change password page if flag is set
- Store force change status in session

## Usage Examples

### 1. Force Employee to Change Password (Admin)

```javascript
// Admin resets employee password and forces change
const response = await fetch('/api/password/admin-reset', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    employee_id: '123',
    new_password: 'TempPass123',
    force_change: true
  })
});
```

### 2. Employee Changes Password

```javascript
// Employee changes their own password
const response = await fetch('/api/password/change', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    current_password: 'OldPass123',
    new_password: 'NewPass123',
    confirm_password: 'NewPass123'
  })
});
```

### 3. Check Force Password Change Status

```javascript
// Check if user needs to change password
const response = await fetch('/api/password/check-force-change', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
if (data.data.force_password_change) {
  window.location.href = '/password/change';
}
```

## Security Features

1. **Password Strength Validation**
   - Minimum 8 characters
   - Mixed case requirement
   - Number requirement
   - Client and server-side validation

2. **Current Password Verification**
   - Users must provide current password to change
   - Prevents unauthorized password changes

3. **Admin Audit Logging**
   - All password resets logged
   - Includes admin ID, employee ID, and timestamp
   - Tracked in system_audit_log table

4. **Force Password Change**
   - Prevents access until password is changed
   - Only applies to employees
   - Automatically cleared after successful change

5. **Supabase Integration**
   - Uses Supabase Auth API for password management
   - Secure password hashing
   - Token-based authentication

## Testing

### Manual Testing Steps

1. **Test Force Password Change**
   - Admin resets employee password with force_change=true
   - Employee logs in
   - Verify redirect to change password page
   - Change password
   - Verify redirect to dashboard

2. **Test Change Password**
   - Navigate to /password/change
   - Enter current password
   - Enter new password (test validation)
   - Confirm new password
   - Submit and verify success

3. **Test Admin Reset**
   - Login as admin
   - Reset employee password
   - Verify employee can login with new password
   - Verify force change flag if set

## Migration Steps

1. **Run Database Migration**
   ```sql
   -- Execute the migration file
   psql -h your-supabase-host -U postgres -d your-database -f docs/migrations/add_password_management.sql
   ```

2. **Update Supabase Configuration**
   - Ensure service_key is configured in config/supabase.php
   - Required for admin password reset functionality

3. **Test the Feature**
   - Create test employee account
   - Test all three features
   - Verify audit logging

## Future Enhancements

1. Password expiration policy
2. Password history (prevent reuse)
3. Password complexity rules configuration
4. Email notification on password change
5. Two-factor authentication
6. Password reset via email link
7. Account lockout after failed attempts

## Troubleshooting

### Issue: Admin reset not working
**Solution:** Verify service_key is correctly configured in config/supabase.php

### Issue: Force password change not triggering
**Solution:** Check that force_password_change column exists in employees table

### Issue: Password validation failing
**Solution:** Ensure password meets all requirements (8+ chars, uppercase, lowercase, number)

### Issue: Redirect loop on change password page
**Solution:** Verify force_password_change flag is cleared after successful change

## Support

For issues or questions, contact the development team or refer to the main HRIS documentation.
