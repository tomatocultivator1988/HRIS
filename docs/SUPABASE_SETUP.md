# Supabase Setup Guide for HRIS MVP

This guide walks you through setting up Supabase for the HRIS MVP system.

## Step 1: Create Supabase Project

1. Go to https://supabase.com
2. Sign up or log in
3. Click "New Project"
4. Fill in:
   - **Project Name**: HRIS MVP (or your preferred name)
   - **Database Password**: Create a strong password (save this!)
   - **Region**: Choose closest to your location
   - **Pricing Plan**: Free tier is sufficient for testing
5. Click "Create new project"
6. Wait 2-3 minutes for project to be ready

## Step 2: Get Your API Keys

Once your project is ready:

1. Go to **Project Settings** (gear icon in sidebar)
2. Click **API** in the left menu
3. You'll see these keys:

### Keys You Need:

```
Project URL: https://xxxxxxxxxxxxx.supabase.co
anon public key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
service_role key: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

**IMPORTANT**: 
- The `anon public` key is safe to use in frontend code
- The `service_role` key should NEVER be exposed in frontend - only use in backend PHP files
- Keep these keys secure!

## Step 3: Configure the HRIS System

### Update config/supabase.php

Open `config/supabase.php` and replace the placeholder values:

```php
<?php
// Supabase Configuration
define('SUPABASE_URL', 'https://xxxxxxxxxxxxx.supabase.co'); // Your Project URL
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'); // Your anon public key
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'); // Your service_role key

// Environment
define('ENVIRONMENT', 'development'); // Change to 'production' when deploying

// Table names
define('TABLE_EMPLOYEES', 'employees');
define('TABLE_ADMINS', 'admins');
define('TABLE_ATTENDANCE', 'attendance');
define('TABLE_LEAVE_TYPES', 'leave_types');
define('TABLE_LEAVE_REQUESTS', 'leave_requests');
define('TABLE_LEAVE_CREDITS', 'leave_credits');
define('TABLE_ANNOUNCEMENTS', 'announcements');
define('TABLE_WORK_CALENDAR', 'work_calendar');
define('TABLE_SYSTEM_AUDIT_LOG', 'system_audit_log');
define('TABLE_LEAVE_CREDIT_AUDIT', 'leave_credit_audit');
define('TABLE_USER_SESSIONS', 'user_sessions');
?>
```

## Step 4: Set Up Database Schema

1. In Supabase dashboard, click **SQL Editor** in the sidebar
2. Click **New Query**
3. Copy the entire contents of `docs/database-schema.sql`
4. Paste into the SQL editor
5. Click **Run** (or press Ctrl+Enter)
6. Wait for "Success. No rows returned" message

This creates all 11 tables with proper relationships.

## Step 5: Enable Authentication

1. Go to **Authentication** in the sidebar
2. Click **Providers**
3. Ensure **Email** is enabled (it should be by default)
4. Configure email settings:
   - **Enable email confirmations**: OFF (for development)
   - **Enable email change confirmations**: OFF (for development)
   - **Secure email change**: OFF (for development)

### For Production:
- Enable email confirmations
- Configure SMTP settings for custom email templates
- Set up password recovery

## Step 6: Create Your First Admin User

### Option A: Via Supabase Dashboard (Recommended for first user)

1. Go to **Authentication** > **Users**
2. Click **Add user** > **Create new user**
3. Fill in:
   - **Email**: admin@yourcompany.com
   - **Password**: Create a strong password
   - **Auto Confirm User**: YES (check this box)
4. Click **Create user**
5. Copy the **User UID** (you'll need this next)

### Option B: Via SQL (After first admin exists)

```sql
-- This will be done through the application once you have an admin
```

## Step 7: Link Admin User to Admins Table

1. Go to **Table Editor** in sidebar
2. Select **admins** table
3. Click **Insert** > **Insert row**
4. Fill in:
   - **supabase_user_id**: Paste the User UID from Step 6
   - **email**: admin@yourcompany.com (same as auth email)
   - **name**: Your Name
   - **role**: Super Admin
   - **is_active**: true
5. Click **Save**

## Step 8: Initialize Leave Types

1. Go to **Table Editor** > **leave_types**
2. Insert these default leave types:

```sql
INSERT INTO leave_types (name, code, default_credits, description, is_active) VALUES
('Annual Leave', 'AL', 15, 'Annual vacation leave', true),
('Sick Leave', 'SL', 10, 'Medical and health-related leave', true),
('Emergency Leave', 'EL', 5, 'Emergency and urgent matters', true),
('Maternity Leave', 'ML', 60, 'Maternity leave for female employees', true),
('Paternity Leave', 'PL', 7, 'Paternity leave for male employees', true);
```

Or insert manually via the UI:
- Click **Insert row** for each leave type
- Fill in the fields as shown above

## Step 9: Set Up Work Calendar (Optional)

For holidays and non-working days:

1. Go to **Table Editor** > **work_calendar**
2. Insert holidays:

```sql
INSERT INTO work_calendar (date, day_type, description, is_working_day) VALUES
('2024-01-01', 'Holiday', 'New Year''s Day', false),
('2024-12-25', 'Holiday', 'Christmas Day', false);
-- Add more holidays as needed
```

## Step 10: Configure Row Level Security (RLS)

For production security, enable RLS:

1. Go to **Authentication** > **Policies**
2. For each table, create policies:

### Example for employees table:

```sql
-- Allow admins to read all employees
CREATE POLICY "Admins can view all employees"
ON employees FOR SELECT
TO authenticated
USING (
  EXISTS (
    SELECT 1 FROM admins 
    WHERE admins.supabase_user_id = auth.uid() 
    AND admins.is_active = true
  )
);

-- Allow employees to view their own record
CREATE POLICY "Employees can view own record"
ON employees FOR SELECT
TO authenticated
USING (supabase_user_id = auth.uid());
```

**Note**: For development, you can skip RLS initially. Enable it before production deployment.

## Step 11: Test the Connection

1. Start XAMPP Apache
2. Navigate to `http://localhost/hris-mvp`
3. Try logging in with your admin credentials
4. If successful, you should see the admin dashboard

## Troubleshooting

### "Invalid API key" error
- Double-check you copied the correct keys from Supabase
- Ensure no extra spaces in the config file
- Verify the keys are wrapped in quotes

### "Table does not exist" error
- Verify the SQL schema ran successfully
- Check table names match exactly (case-sensitive)
- Refresh the Supabase dashboard

### "Authentication failed" error
- Verify user exists in Authentication > Users
- Check user is confirmed (green checkmark)
- Ensure user has corresponding record in admins table
- Verify supabase_user_id matches exactly

### "CORS error" in browser console
- This is normal for local development
- The backend handles authentication, not frontend
- Errors should only appear if trying to call Supabase directly from JavaScript

## Security Checklist for Production

Before deploying to production:

- [ ] Change ENVIRONMENT to 'production' in config/supabase.php
- [ ] Enable Row Level Security (RLS) on all tables
- [ ] Create appropriate RLS policies for each table
- [ ] Enable email confirmations in Auth settings
- [ ] Set up custom SMTP for emails
- [ ] Use environment variables for sensitive keys (not hardcoded)
- [ ] Enable HTTPS and update security headers
- [ ] Set up database backups
- [ ] Configure rate limiting appropriately
- [ ] Review and test all security policies
- [ ] Enable audit logging
- [ ] Set up monitoring and alerts

## Additional Configuration

### Email Templates (Production)

1. Go to **Authentication** > **Email Templates**
2. Customize:
   - Confirmation email
   - Password recovery email
   - Email change confirmation

### API Rate Limiting

Supabase has built-in rate limiting:
- Free tier: 500 requests per second
- Adjust in Project Settings if needed

### Database Backups

1. Go to **Database** > **Backups**
2. Enable automatic backups (Pro plan feature)
3. For free tier, manually export database periodically

## Summary of What You Need

From Supabase, you need these 3 things:

1. **Project URL**: `https://xxxxxxxxxxxxx.supabase.co`
2. **Anon Key**: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...` (long string)
3. **Service Role Key**: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...` (different long string)

Put these in `config/supabase.php` and you're ready to go!

## Next Steps

After Supabase is configured:

1. Create employee records through the admin interface
2. Set up leave credits for employees
3. Configure work calendar with your company holidays
4. Customize leave types if needed
5. Test all functionality with different user roles
6. Set up regular database backups
7. Monitor system logs and usage

## Support Resources

- Supabase Documentation: https://supabase.com/docs
- Supabase Discord: https://discord.supabase.com
- Project Issues: Check browser console and PHP error logs

---

**Need Help?** Check the main README.md for troubleshooting tips or contact your system administrator.
