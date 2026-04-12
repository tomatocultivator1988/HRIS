-- Disable RLS on employee_documents table
-- The application handles all security checks via JWT authentication
-- RLS policies are designed for Supabase Auth which this system doesn't use
-- Date: 2026-04-12

-- Disable Row Level Security
ALTER TABLE employee_documents DISABLE ROW LEVEL SECURITY;

-- Drop all RLS policies
DROP POLICY IF EXISTS "Admins can view all employee documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can view own documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can upload own documents" ON employee_documents;
DROP POLICY IF EXISTS "Employees can delete own documents" ON employee_documents;
DROP POLICY IF EXISTS "Admins can delete any document" ON employee_documents;
DROP POLICY IF EXISTS "Admins can update documents" ON employee_documents;

-- Add comment explaining security model
COMMENT ON TABLE employee_documents IS 'Stores employee 201 files metadata. Security is enforced at application level via JWT authentication and DocumentController access checks.';
