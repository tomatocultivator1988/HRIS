-- Fix employee_documents table to allow NULL for uploaded_by
-- This allows document uploads even when supabase_user_id is not available
-- Date: 2026-04-12

-- Make uploaded_by nullable
ALTER TABLE employee_documents 
ALTER COLUMN uploaded_by DROP NOT NULL;

-- Update comment
COMMENT ON COLUMN employee_documents.uploaded_by IS 'Supabase user ID of uploader (from auth.uid()) - nullable for system uploads';
