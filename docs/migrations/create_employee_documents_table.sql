-- Employee 201 Files - Database Migration
-- Creates employee_documents table with RLS policies for secure document management
-- Date: 2026-04-12

-- Create employee_documents table
CREATE TABLE IF NOT EXISTS employee_documents (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    employee_id UUID NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by UUID NOT NULL,
    uploaded_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by UUID,
    verified_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_employee_documents_employee_id ON employee_documents(employee_id);
CREATE INDEX IF NOT EXISTS idx_employee_documents_document_type ON employee_documents(document_type);
CREATE INDEX IF NOT EXISTS idx_employee_documents_uploaded_at ON employee_documents(uploaded_at);

-- Enable Row Level Security
ALTER TABLE employee_documents ENABLE ROW LEVEL SECURITY;

-- RLS Policy: Admins can view all employee documents
CREATE POLICY "Admins can view all employee documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM admins 
            WHERE admins.supabase_user_id = auth.uid() 
            AND admins.is_active = true
        )
    );

-- RLS Policy: Employees can view own documents
CREATE POLICY "Employees can view own documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );

-- RLS Policy: Employees can upload own documents
CREATE POLICY "Employees can upload own documents" ON employee_documents
    FOR INSERT WITH CHECK (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );

-- RLS Policy: Employees can delete own documents
CREATE POLICY "Employees can delete own documents" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );

-- RLS Policy: Admins can delete any document
CREATE POLICY "Admins can delete any document" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM admins 
            WHERE admins.supabase_user_id = auth.uid() 
            AND admins.is_active = true
        )
    );

-- RLS Policy: Admins can update documents (verify, add notes)
CREATE POLICY "Admins can update documents" ON employee_documents
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM admins 
            WHERE admins.supabase_user_id = auth.uid() 
            AND admins.is_active = true
        )
    );

-- Add comment to table
COMMENT ON TABLE employee_documents IS 'Stores employee 201 files metadata with secure access control';

-- Add comments to important columns
COMMENT ON COLUMN employee_documents.document_type IS 'Type of document: Resume, Birth Certificate, TIN, SSS, PhilHealth, Pag-IBIG, NBI Clearance, Medical Certificate, Diploma, Transcript, Other';
COMMENT ON COLUMN employee_documents.file_path IS 'Path to file in storage directory (e.g., storage/201files/{employee_id}/{filename})';
COMMENT ON COLUMN employee_documents.file_size IS 'File size in bytes';
COMMENT ON COLUMN employee_documents.is_verified IS 'Admin verification status';
COMMENT ON COLUMN employee_documents.uploaded_by IS 'Supabase user ID of uploader (from auth.uid())';
COMMENT ON COLUMN employee_documents.verified_by IS 'Supabase user ID of admin who verified (from auth.uid())';
