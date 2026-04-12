-- Create RLS policies for employee-documents storage bucket
-- Run this in Supabase SQL Editor after creating the bucket

-- Policy: Employees can upload their own documents
CREATE POLICY "Employees can upload own documents"
ON storage.objects FOR INSERT
TO authenticated
WITH CHECK (
  bucket_id = 'employee-documents' AND
  (storage.foldername(name))[1] = auth.uid()::text
);

-- Policy: Employees can view their own documents
CREATE POLICY "Employees can view own documents"
ON storage.objects FOR SELECT
TO authenticated
USING (
  bucket_id = 'employee-documents' AND
  (storage.foldername(name))[1] = auth.uid()::text
);

-- Policy: Employees can delete their own documents
CREATE POLICY "Employees can delete own documents"
ON storage.objects FOR DELETE
TO authenticated
USING (
  bucket_id = 'employee-documents' AND
  (storage.foldername(name))[1] = auth.uid()::text
);

-- Policy: Admins can view all documents
CREATE POLICY "Admins can view all documents"
ON storage.objects FOR SELECT
TO authenticated
USING (
  bucket_id = 'employee-documents' AND
  EXISTS (
    SELECT 1 FROM admins
    WHERE admins.supabase_user_id = auth.uid()
    AND admins.is_active = true
  )
);

-- Policy: Admins can delete any document
CREATE POLICY "Admins can delete any document"
ON storage.objects FOR DELETE
TO authenticated
USING (
  bucket_id = 'employee-documents' AND
  EXISTS (
    SELECT 1 FROM admins
    WHERE admins.supabase_user_id = auth.uid()
    AND admins.is_active = true
  )
);
