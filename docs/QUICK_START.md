# HRIS MVP - Quick Start Guide

Get your HRIS system up and running in 15 minutes!

## Prerequisites Checklist

- [ ] XAMPP installed (with Apache and PHP 7.4+)
- [ ] Supabase account created
- [ ] Modern web browser (Chrome, Firefox, Edge, Safari)

## Quick Setup (15 minutes)

### 1. Download & Extract (2 minutes)

1. Extract the HRIS MVP files to XAMPP's htdocs folder:
   ```
   C:\xampp\htdocs\hris-mvp\
   ```

### 2. Create Supabase Project (3 minutes)

1. Go to https://supabase.com
2. Click "New Project"
3. Enter project name: "HRIS MVP"
4. Choose a region close to you
5. Create a strong database password
6. Click "Create new project"
7. Wait 2-3 minutes for setup

### 3. Get Your Keys (1 minute)

1. In Supabase, go to **Settings** (gear icon) > **API**
2. Copy these three values:

```
Project URL: https://xxxxx.supabase.co
anon public: eyJhbGci...
service_role: eyJhbGci...
```

### 4. Configure HRIS (2 minutes)

1. Open `config/supabase.php` in a text editor
2. Replace the placeholder values with your keys:

```php
define('SUPABASE_URL', 'https://xxxxx.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGci...');
define('SUPABASE_SERVICE_KEY', 'eyJhbGci...');
```

3. Save the file

### 5. Create Database (3 minutes)

1. In Supabase, click **SQL Editor** in sidebar
2. Click **New Query**
3. Open `docs/database-schema.sql` from the HRIS files
4. Copy ALL the SQL code
5. Paste into Supabase SQL Editor
6. Click **Run** (or Ctrl+Enter)
7. Wait for "Success. No rows returned"

### 6. Create Admin User (2 minutes)

1. In Supabase, go to **Authentication** > **Users**
2. Click **Add user** > **Create new user**
3. Enter:
   - Email: `admin@company.com`
   - Password: `Admin123!` (change this later!)
   - Check "Auto Confirm User"
4. Click **Create user**
5. **IMPORTANT**: Copy the User UID (long string like `a1b2c3d4-...`)

### 7. Link Admin to System (2 minutes)

1. In Supabase, go to **Table Editor** > **admins** table
2. Click **Insert row**
3. Fill in:
   - **supabase_user_id**: Paste the User UID from step 6
   - **email**: `admin@company.com`
   - **name**: `System Administrator`
   - **role**: `Super Admin`
   - **is_active**: `true` (check the box)
4. Click **Save**

### 8. Start XAMPP & Test (1 minute)

1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Wait for Apache to turn green
4. Open browser and go to: `http://localhost/hris-mvp`
5. Login with:
   - Email: `admin@company.com`
   - Password: `Admin123!`

## ✅ Success!

If you see the admin dashboard, you're all set! 

## What's Next?

### Immediate Tasks:
1. **Change your password** (click your name > Profile)
2. **Add leave types** (already done if you ran the full schema)
3. **Create your first employee**:
   - Go to Manage Employees
   - Click "Add Employee"
   - Fill in the form
   - Save

### Recommended Setup:
1. Add more admin users if needed
2. Set up company holidays in work calendar
3. Configure leave types for your company
4. Import employee data
5. Set up leave credits for employees

## Common Issues & Quick Fixes

### Can't login?
- Check that you copied the keys correctly (no extra spaces)
- Verify user exists in Supabase Authentication > Users
- Ensure user has record in admins table with matching supabase_user_id

### "Table does not exist" error?
- Go back to Supabase SQL Editor
- Re-run the database-schema.sql
- Check for any error messages in red

### Apache won't start?
- Check if port 80 is already in use
- Try stopping Skype or other programs using port 80
- In XAMPP, click Config > Apache > httpd.conf
- Change `Listen 80` to `Listen 8080`
- Restart Apache
- Access via `http://localhost:8080/hris-mvp`

### Blank page or errors?
- Check XAMPP Apache logs
- Ensure PHP version is 7.4 or higher
- Verify all files were extracted correctly

## File Locations Reference

```
C:\xampp\htdocs\hris-mvp\
├── config/supabase.php          ← Edit this with your keys
├── docs/database-schema.sql     ← Run this in Supabase
├── index.html                   ← Login page
└── dashboard/admin.html         ← Admin dashboard
```

## Default Credentials

**Admin Login:**
- Email: `admin@company.com`
- Password: `Admin123!`

**⚠️ IMPORTANT**: Change this password immediately after first login!

## Testing Checklist

After setup, test these features:

- [ ] Login as admin
- [ ] View dashboard (should show metrics)
- [ ] Create a test employee
- [ ] Record attendance (time-in/time-out)
- [ ] Submit a leave request
- [ ] Approve the leave request
- [ ] Generate a report
- [ ] Create an announcement
- [ ] Logout and login again

## Need More Help?

- **Detailed Setup**: See `docs/SUPABASE_SETUP.md`
- **Full Documentation**: See `README.md`
- **Troubleshooting**: Check browser console (F12) for errors

## Production Deployment

Before going live:

1. Change admin password
2. Update `ENVIRONMENT` to `'production'` in config/supabase.php
3. Enable HTTPS
4. Set up Row Level Security in Supabase
5. Configure email settings
6. Set up database backups
7. Review security checklist in SUPABASE_SETUP.md

---

**Congratulations!** Your HRIS system is ready to use. 🎉

For questions or issues, refer to the full documentation in README.md or SUPABASE_SETUP.md.
