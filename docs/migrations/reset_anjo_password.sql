-- ============================================
-- RESET PASSWORD FOR anjo@gmail.com
-- ============================================
-- This script resets the password for anjo@gmail.com
-- using Supabase Auth functions
-- 
-- New Password: NewPassword123
-- ============================================

DO $$
DECLARE
    user_id UUID;
    employee_record RECORD;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'RESETTING PASSWORD FOR anjo@gmail.com';
    RAISE NOTICE '========================================';
    
    -- Find the employee
    SELECT * INTO employee_record
    FROM employees
    WHERE work_email = 'anjo@gmail.com'
    LIMIT 1;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Employee with email anjo@gmail.com not found!';
    END IF;
    
    RAISE NOTICE 'Found employee:';
    RAISE NOTICE '  Name: % %', employee_record.first_name, employee_record.last_name;
    RAISE NOTICE '  Email: %', employee_record.work_email;
    RAISE NOTICE '  Employee ID: %', employee_record.employee_id;
    RAISE NOTICE '';
    
    -- Get the Supabase user ID
    user_id := employee_record.supabase_user_id;
    
    IF user_id IS NULL THEN
        RAISE EXCEPTION 'No Supabase user ID found for this employee!';
    END IF;
    
    RAISE NOTICE 'Supabase User ID: %', user_id;
    RAISE NOTICE '';
    
    -- Update the password in Supabase auth.users table
    -- Note: This requires direct access to auth schema
    -- The password will be hashed automatically by Supabase
    RAISE NOTICE 'Updating password in Supabase Auth...';
    
    -- Update auth.users table (requires superuser or service role)
    UPDATE auth.users
    SET 
        encrypted_password = crypt('NewPassword123', gen_salt('bf')),
        updated_at = NOW()
    WHERE id = user_id;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Failed to update password in auth.users table!';
    END IF;
    
    RAISE NOTICE '✅ Password updated in Supabase Auth';
    RAISE NOTICE '';
    
    -- Update force_password_change flag to FALSE (so user won't be forced to change again)
    UPDATE employees
    SET 
        force_password_change = FALSE,
        password_changed_at = NOW(),
        updated_at = NOW()
    WHERE id = employee_record.id;
    
    RAISE NOTICE '✅ Updated employee record:';
    RAISE NOTICE '  - force_password_change = FALSE';
    RAISE NOTICE '  - password_changed_at = NOW()';
    RAISE NOTICE '';
    
    RAISE NOTICE '========================================';
    RAISE NOTICE 'PASSWORD RESET COMPLETE!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Login Credentials:';
    RAISE NOTICE '  Email: anjo@gmail.com';
    RAISE NOTICE '  Password: NewPassword123';
    RAISE NOTICE '';
    RAISE NOTICE 'User will NOT be forced to change password.';
    RAISE NOTICE '========================================';
    
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE '';
        RAISE NOTICE '❌ ERROR: %', SQLERRM;
        RAISE NOTICE '';
        RAISE NOTICE 'Possible reasons:';
        RAISE NOTICE '1. Employee not found with email anjo@gmail.com';
        RAISE NOTICE '2. No Supabase user ID linked to employee';
        RAISE NOTICE '3. Insufficient permissions to update auth.users';
        RAISE NOTICE '';
        RAISE NOTICE 'If you get permission error, you need to:';
        RAISE NOTICE '1. Go to Supabase Dashboard';
        RAISE NOTICE '2. SQL Editor';
        RAISE NOTICE '3. Make sure you are using service_role key';
        RAISE;
END $$;
