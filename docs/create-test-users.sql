-- ============================================================================
-- HRIS MVP - Create Test Users
-- ============================================================================
-- IMPORTANT: Before running this SQL, you MUST create the users in Supabase Authentication first!
-- 
-- Steps:
-- 1. Go to Supabase Dashboard > Authentication > Users
-- 2. Click "Add User" and create:
--    - Admin: admin@company.com / Admin123!
--    - Employee: employee@company.com / Employee123!
-- 3. Copy the User IDs (UUID) from Supabase Auth
-- 4. Replace the UUIDs below with the actual User IDs
-- 5. Run this SQL in Supabase SQL Editor
-- ============================================================================

-- STEP 1: Create Admin User
-- Replace 'YOUR_ADMIN_UUID_HERE' with the actual UUID from Supabase Authentication
INSERT INTO admins (supabase_user_id, name, email, role, is_active) 
VALUES (
    'YOUR_ADMIN_UUID_HERE',  -- ⚠️ REPLACE THIS with actual UUID from Supabase Auth
    'System Administrator',
    'admin@company.com',
    'admin',
    true
);

-- STEP 2: Create Employee User
-- Replace 'YOUR_EMPLOYEE_UUID_HERE' with the actual UUID from Supabase Authentication
INSERT INTO employees (
    employee_id,
    supabase_user_id,  -- ⚠️ REPLACE THIS with actual UUID from Supabase Auth
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
    'YOUR_EMPLOYEE_UUID_HERE',  -- ⚠️ REPLACE THIS with actual UUID from Supabase Auth
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

-- STEP 3: Verify the records were created
SELECT 'Admin User:' as record_type, * FROM admins WHERE email = 'admin@company.com'
UNION ALL
SELECT 'Employee User:' as record_type, * FROM employees WHERE work_email = 'employee@company.com';

-- ============================================================================
-- ALTERNATIVE: If you want to create users directly via SQL (Advanced)
-- ============================================================================
-- Note: This requires using Supabase Admin API or service role key
-- It's easier to use the Supabase Dashboard UI instead
-- ============================================================================

/*
-- This is for reference only - use Supabase Dashboard UI instead
-- To create auth users via SQL, you need to use Supabase's auth.users table
-- But this requires special permissions and is not recommended for testing

-- Example (DO NOT USE - for reference only):
INSERT INTO auth.users (
    instance_id,
    id,
    aud,
    role,
    email,
    encrypted_password,
    email_confirmed_at,
    created_at,
    updated_at
) VALUES (
    '00000000-0000-0000-0000-000000000000',
    gen_random_uuid(),
    'authenticated',
    'authenticated',
    'admin@company.com',
    crypt('Admin123!', gen_salt('bf')),
    now(),
    now(),
    now()
);
*/

-- ============================================================================
-- QUICK REFERENCE: How to get User IDs from Supabase
-- ============================================================================
-- 1. Go to: https://supabase.com/dashboard/project/xtfekjcusnnadfgcrzht
-- 2. Click "Authentication" in left sidebar
-- 3. Click "Users" tab
-- 4. Find your user (admin@company.com or employee@company.com)
-- 5. Click on the user to see details
-- 6. Copy the "ID" field (this is the UUID you need)
-- 7. Paste it in this SQL file replacing 'YOUR_ADMIN_UUID_HERE' or 'YOUR_EMPLOYEE_UUID_HERE'
-- ============================================================================
