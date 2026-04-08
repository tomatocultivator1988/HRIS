# HRIS MVP Configuration Checklist

Use this checklist to ensure everything is properly configured.

## Pre-Installation Checklist

- [ ] XAMPP installed
- [ ] Apache can start successfully
- [ ] PHP version 7.4 or higher
- [ ] Supabase account created
- [ ] Modern web browser available

## Supabase Configuration

### Project Setup
- [ ] Supabase project created
- [ ] Project is fully initialized (not showing "Setting up...")
- [ ] Project URL copied
- [ ] Anon public key copied
- [ ] Service role key copied

### Database Setup
- [ ] SQL Editor opened in Supabase
- [ ] `database-schema.sql` content copied
- [ ] SQL executed successfully (no errors)
- [ ] All 11 tables created:
  - [ ] employees
  - [ ] admins
  - [ ] attendance
  - [ ] leave_types
  - [ ] leave_requests
  - [ ] leave_credits
  - [ ] announcements
  - [ ] work_calendar
  - [ ] system_audit_log
  - [ ] leave_credit_audit
  - [ ] user_sessions

### Authentication Setup
- [ ] Email authentication enabled
- [ ] First admin user created in Authentication > Users
- [ ] User is confirmed (auto-confirm checked)
- [ ] User UID copied

### Initial Data
- [ ] Admin record created in `admins` table
- [ ] Admin `supabase_user_id` matches user UID
- [ ] Admin `is_active` set to true
- [ ] Leave types inserted (5 default types)
- [ ] Work calendar initialized (optional)

## HRIS System Configuration

### File Setup
- [ ] Files extracted to `C:\xampp\htdocs\hris-mvp\`
- [ ] All folders present (api, assets, config, dashboard, modules, docs)
- [ ] `config/supabase.php` file exists

### Configuration File
- [ ] `config/supabase.php` opened in text editor
- [ ] `SUPABASE_URL` updated with your Project URL
- [ ] `SUPABASE_ANON_KEY` updated with your anon key
- [ ] `SUPABASE_SERVICE_KEY` updated with your service_role key
- [ ] No extra spaces in keys
- [ ] Keys wrapped in quotes
- [ ] File saved

### Directory Permissions
- [ ] `logs/` directory created
- [ ] `logs/` directory writable (755 permissions)

## Apache Configuration

### XAMPP Setup
- [ ] Apache started in XAMPP Control Panel
- [ ] Apache shows green "Running" status
- [ ] Port 80 available (or configured to use 8080)
- [ ] `mod_rewrite` enabled
- [ ] `mod_headers` enabled

### .htaccess
- [ ] `.htaccess` file exists in root directory
- [ ] File is readable by Apache
- [ ] Clean URLs working (test by accessing `/dashboard/admin`)

## Testing Checklist

### Basic Connectivity
- [ ] Can access `http://localhost/hris-mvp`
- [ ] Login page loads without errors
- [ ] No console errors in browser (F12)
- [ ] Tailwind CSS styles loading correctly

### Authentication
- [ ] Can login with admin credentials
- [ ] Redirects to admin dashboard after login
- [ ] Dashboard shows metrics (even if zeros)
- [ ] Can logout successfully
- [ ] Cannot access dashboard when logged out

### Admin Functions
- [ ] Dashboard loads with charts
- [ ] Can navigate to Manage Employees
- [ ] Can create a test employee
- [ ] Can view employee list
- [ ] Can edit employee
- [ ] Can view employee profile

### Attendance
- [ ] Can access attendance module
- [ ] Can record time-in
- [ ] Can record time-out
- [ ] Can view daily attendance
- [ ] Can view attendance history

### Leave Management
- [ ] Can access leave module
- [ ] Can view leave types
- [ ] Can view pending requests
- [ ] Can approve/deny requests
- [ ] Can manage leave credits

### Reports
- [ ] Can access reports module
- [ ] Can generate attendance report
- [ ] Can generate leave report
- [ ] Can generate headcount report
- [ ] Can export reports to CSV

### Announcements
- [ ] Can access announcements
- [ ] Can create announcement
- [ ] Can edit announcement
- [ ] Can deactivate announcement
- [ ] Announcements show on dashboard

## Security Checklist

### Development
- [ ] ENVIRONMENT set to 'development'
- [ ] Error logging enabled
- [ ] Can see detailed error messages

### Before Production
- [ ] Change default admin password
- [ ] ENVIRONMENT changed to 'production'
- [ ] Detailed errors disabled
- [ ] HTTPS enabled
- [ ] SSL certificate installed
- [ ] Row Level Security (RLS) enabled in Supabase
- [ ] RLS policies created for all tables
- [ ] Email confirmations enabled
- [ ] SMTP configured for emails
- [ ] Rate limiting tested
- [ ] CSRF protection verified
- [ ] Input sanitization working
- [ ] Session security configured
- [ ] Audit logging enabled
- [ ] Database backups configured

## Common Issues Resolution

### Issue: Can't login
- [ ] Verified keys in config/supabase.php
- [ ] Checked user exists in Supabase Auth
- [ ] Confirmed user has admin record
- [ ] Verified supabase_user_id matches
- [ ] Checked user is_active = true

### Issue: Tables not found
- [ ] Re-ran database-schema.sql
- [ ] Checked for SQL errors
- [ ] Verified all 11 tables exist
- [ ] Confirmed table names match config

### Issue: Apache won't start
- [ ] Checked port 80 availability
- [ ] Stopped Skype/other services
- [ ] Changed to port 8080 if needed
- [ ] Checked Apache error logs

### Issue: Blank pages
- [ ] Checked PHP error logs
- [ ] Verified PHP version 7.4+
- [ ] Confirmed all files extracted
- [ ] Checked file permissions

### Issue: Charts not showing
- [ ] Verified Chart.js CDN accessible
- [ ] Checked browser console for errors
- [ ] Confirmed API returning data
- [ ] Tested with sample data

## Performance Checklist

- [ ] Apache compression enabled
- [ ] Browser caching configured
- [ ] Static assets cached
- [ ] Database queries optimized
- [ ] Images optimized
- [ ] Unnecessary logs disabled

## Documentation Checklist

- [ ] README.md reviewed
- [ ] SUPABASE_SETUP.md followed
- [ ] QUICK_START.md completed
- [ ] SUPABASE_KEYS_GUIDE.md referenced
- [ ] Admin credentials documented (securely)
- [ ] System architecture understood

## User Setup Checklist

### Admin Users
- [ ] All admin users created
- [ ] Admin passwords changed from defaults
- [ ] Admin roles assigned correctly
- [ ] Admin access tested

### Employee Users
- [ ] Employee records created
- [ ] Supabase auth accounts created
- [ ] Employee IDs assigned
- [ ] Departments assigned
- [ ] Positions assigned
- [ ] Hire dates entered
- [ ] Leave credits initialized

### Leave Configuration
- [ ] Leave types reviewed
- [ ] Default credits adjusted if needed
- [ ] Leave policies documented
- [ ] Approval workflow tested

### Work Calendar
- [ ] Company holidays added
- [ ] Non-working days configured
- [ ] Special working days noted
- [ ] Calendar tested with leave requests

## Maintenance Checklist

### Daily
- [ ] Check error logs
- [ ] Monitor system performance
- [ ] Review failed login attempts

### Weekly
- [ ] Review audit logs
- [ ] Check database size
- [ ] Verify backups running
- [ ] Test critical functions

### Monthly
- [ ] Update leave credits
- [ ] Generate usage reports
- [ ] Review user accounts
- [ ] Clean up old sessions
- [ ] Archive old data

### Quarterly
- [ ] Review security settings
- [ ] Update documentation
- [ ] Test disaster recovery
- [ ] Review and update policies

## Final Verification

Before going live:

- [ ] All checklists above completed
- [ ] System tested by multiple users
- [ ] All user roles tested
- [ ] Security audit performed
- [ ] Backup and recovery tested
- [ ] Performance tested under load
- [ ] Documentation complete
- [ ] Training materials prepared
- [ ] Support process established
- [ ] Monitoring configured
- [ ] Incident response plan ready

## Sign-Off

Configuration completed by: ___________________

Date: ___________________

Verified by: ___________________

Date: ___________________

---

**Congratulations!** If all items are checked, your HRIS system is fully configured and ready for use.

For ongoing support, refer to:
- README.md for general documentation
- SUPABASE_SETUP.md for database issues
- Browser console (F12) for frontend errors
- Apache error logs for backend errors
- Supabase dashboard for database monitoring
