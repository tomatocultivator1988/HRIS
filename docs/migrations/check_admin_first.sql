-- ============================================
-- CHECK ADMIN ACCOUNT FIRST
-- ============================================
-- Run this to see what admin accounts you have
-- ============================================

-- Check employees table
SELECT 
    'EMPLOYEES TABLE' as source,
    id,
    employee_id,
    first_name,
    last_name,
    work_email,
    is_active
FROM employees
ORDER BY created_at
LIMIT 10;

-- Check admins table
SELECT 
    'ADMINS TABLE' as source,
    id,
    supabase_user_id,
    name,
    email,
    role,
    is_active
FROM admins
ORDER BY created_at
LIMIT 10;

-- Check auth.users
SELECT 
    'AUTH.USERS TABLE' as source,
    id,
    email,
    created_at
FROM auth.users
ORDER BY created_at
LIMIT 10;

-- Count all records
SELECT 
    'employees' as table_name,
    COUNT(*) as total_count
FROM employees
UNION ALL
SELECT 'admins', COUNT(*) FROM admins
UNION ALL
SELECT 'auth.users', COUNT(*) FROM auth.users;
