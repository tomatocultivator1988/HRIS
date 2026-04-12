-- Fix employee_documents table for JWT authentication system
-- Run this in Supabase SQL Editor
-- Date: 2026-04-12

-- 1. Make uploaded_by nullable (allows uploads when supabase_user_id is not available)
ALTER TABLE employee_documents 
ALTER COLUMN uploaded_by DROP NOT NULL;

-- 2. Disable Row Level Security (application handles all security via JWT)
ALTER TABLE employee_documents DISABLE ROW LEVEL SECURITY;

-- 3. Drop all RLS policies (not needed with JWT auth)
DROP POLICY IF EXISTS "Admins can view all employee documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can view own documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can upload own documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can delete own documents" ON employee_documents;
DROP POLICY IF EXISTS "Admins can delete any document" ON employee_documents;
DROP POLICY IF EXISTS "Admins can update documents" ON employee_documents;

-- 4. Update comments
COMMENT ON TABLE employee_documents IS 'Stores employee 201 files metadata. Security is enforced at application level via JWT authentication and DocumentController access checks.';
COMMENT ON COLUMN employee_documents.uploaded_by IS 'Supabase user ID of uploader - nullable for system uploads';

-- Verify changes
SELECT 
    column_name, 
    data_type, 
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'employee_documents' 
AND column_name IN ('uploaded_by', 'verified_by')
ORDER BY ordinal_position;
