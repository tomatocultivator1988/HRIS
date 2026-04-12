# Implementation Plan: Employee 201 Files Management

## Overview

This implementation plan breaks down the Employee 201 Files Management feature into discrete coding tasks. The feature enables employees to upload and manage their employment documents while providing administrators with document oversight capabilities. Implementation follows the existing HRIS patterns using PHP, Supabase, and Tailwind CSS.

## Tasks

- [x] 1. Create database migration and schema
  - Create migration file `docs/migrations/create_employee_documents_table.sql`
  - Define employee_documents table with all required columns (id, employee_id, document_type, file_name, file_path, file_size, mime_type, uploaded_by, uploaded_at, notes, is_verified, verified_by, verified_at, created_at, updated_at)
  - Add foreign key constraints for employee_id, uploaded_by, and verified_by
  - Create indexes on employee_id, document_type, and uploaded_at
  - Define Row Level Security policies for SELECT, INSERT, UPDATE, DELETE operations
  - Enable RLS on the employee_documents table
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7_

- [x] 2. Create EmployeeDocument model
  - Create `src/Models/EmployeeDocument.php` extending Core\Model
  - Implement find($id) method to retrieve document by ID
  - Implement findAll($filters) method with support for employee_id and document_type filters
  - Implement findByEmployeeId($employeeId) method
  - Implement create($data) method with validation
  - Implement update($id, $data) method
  - Implement delete($id) method
  - Set table name to 'employee_documents'
  - _Requirements: 1.6, 2.1, 3.1, 4.4_

- [x] 3. Create DocumentService with file validation
  - Create `src/Services/DocumentService.php`
  - Define constants: MAX_FILE_SIZE (10MB), MAX_STORAGE_PER_EMPLOYEE (50MB), ALLOWED_EXTENSIONS, ALLOWED_MIME_TYPES, STORAGE_PATH
  - Implement validateFile($file) method to check extension, MIME type, and file size
  - Implement checkStorageQuota($employeeId, $newFileSize) method
  - Implement calculateStorageUsed($employeeId) method
  - Return validation errors in structured format with field-specific messages
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.8, 13.2, 13.3, 15.1, 15.2, 15.3, 15.4_

- [x] 4. Implement file storage operations in DocumentService
  - Implement generateFileName($employeeId, $originalName) method with format: {employeeId}_{timestamp}_{sanitizedName}
  - Implement sanitizeFileName($filename) method to remove special characters and replace spaces
  - Implement storeFile($file, $employeeId) method to move uploaded file to storage directory
  - Create employee subdirectory if it doesn't exist
  - Set file permissions to 644 and directory permissions to 755
  - Implement deleteFile($filePath) method to remove file from storage
  - Handle file system errors with try-catch blocks
  - _Requirements: 1.5, 4.3, 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 5. Create DocumentController with upload endpoint
  - Create `src/Controllers/DocumentController.php` extending Core\Controller
  - Inject DocumentService, EmployeeDocument model, and AuditLogService
  - Implement upload($request, $employeeId) method
  - Verify JWT authentication and ownership/admin access
  - Validate multipart/form-data request with file, document_type, and optional notes
  - Call DocumentService->validateFile() and checkStorageQuota()
  - Call DocumentService->storeFile() to save file
  - Create database record with EmployeeDocument->create()
  - Log operation with AuditLogService->log('document_upload')
  - Return JSON response with document metadata
  - Implement rollback: delete file if database insert fails
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 11.1, 11.2, 11.3, 11.4, 11.5_

- [x] 6. Implement list and download endpoints in DocumentController
  - Implement list($request, $employeeId) method
  - Verify JWT authentication and ownership/admin access
  - Retrieve documents using EmployeeDocument->findByEmployeeId()
  - Calculate storage statistics: total_size, total_count, storage_used_percentage
  - Return JSON response with documents array and storage stats
  - Implement download($request, $employeeId, $documentId) method
  - Verify document exists and user has access
  - Stream file with Content-Type and Content-Disposition headers
  - Log download with AuditLogService->log('document_download')
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.6, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 11.6, 13.5_

- [x] 7. Implement delete and verify endpoints in DocumentController
  - Implement delete($request, $employeeId, $documentId) method
  - Verify JWT authentication and ownership/admin access
  - Retrieve document record to get file_path
  - Delete file using DocumentService->deleteFile()
  - Delete database record using EmployeeDocument->delete()
  - Log deletion with AuditLogService->log('document_delete')
  - Return JSON response
  - Implement verify($request, $employeeId, $documentId) method (admin only)
  - Update is_verified, verified_by, verified_at fields
  - Log verification with AuditLogService->log('document_verify')
  - _Requirements: 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 6.2, 6.3, 6.4, 6.5, 7.3, 7.4, 7.5, 10.3, 10.4_

- [x] 8. Add API routes to config/routes.php
  - Add POST route: /api/employees/{employeeId}/documents -> DocumentController@upload
  - Add GET route: /api/employees/{employeeId}/documents -> DocumentController@list
  - Add GET route: /api/employees/{employeeId}/documents/{documentId}/download -> DocumentController@download
  - Add DELETE route: /api/employees/{employeeId}/documents/{documentId} -> DocumentController@delete
  - Add PUT route: /api/employees/{employeeId}/documents/{documentId}/verify -> DocumentController@verify
  - Apply AuthMiddleware to all routes
  - _Requirements: 8.6_

- [x] 9. Create storage directory structure
  - Create `storage/201files/` directory in project root
  - Set directory permissions to 755
  - Add .gitignore file to storage/201files/ to exclude uploaded files from git
  - Verify directory is writable by web server
  - _Requirements: 8.1, 8.2, 8.5_

- [x] 10. Update employee list page with "View 201 Files" button
  - Open `src/Views/employees/list.php`
  - Add "View 201 Files" button to each employee row in the table
  - Add document count badge next to button (fetch count via data attribute or API)
  - Style button with existing slate theme (slate-700 background, hover:slate-600)
  - Add onclick handler to call view201Files(employeeId, employeeName)
  - _Requirements: 5.1, 5.2, 12.1, 12.2, 12.3, 12.4_

- [x] 11. Create admin document modal in employee list page
  - Add modal HTML structure to `src/Views/employees/list.php`
  - Include modal header with employee name and close button
  - Add document type filter dropdown with all document types
  - Create document list container with table structure
  - Add columns: Type, Filename, Size, Upload Date, Verified, Actions
  - Add download and delete buttons for each document
  - Add verification checkbox for each document
  - Display storage statistics (total documents, total size)
  - Style modal with slate-800 background and slate-700 borders
  - _Requirements: 5.3, 5.4, 5.5, 5.6, 6.1, 6.6, 7.1, 7.2, 12.1, 12.2, 12.3, 12.4_

- [x] 12. Implement admin modal JavaScript functions
  - Add view201Files(employeeId, employeeName) function to open modal
  - Add loadDocuments(employeeId) function to fetch documents via API
  - Add displayDocuments(documents) function to render document list with file type icons
  - Add filterDocuments() function to filter by selected document type
  - Add downloadDocument(employeeId, documentId) function to initiate download
  - Add toggleVerify(documentId, isVerified) function to update verification status
  - Add deleteDocument(documentId) function with confirmation modal
  - Use existing utility functions: showError, showSuccess, showLoading, showConfirm
  - _Requirements: 5.3, 5.4, 5.5, 6.2, 6.3, 6.5, 7.1, 7.2, 7.6, 12.5, 12.6, 14.5_

- [x] 13. Update employee profile page with "My 201 Files" section
  - Open `src/Views/employees/profile.php`
  - Add "My 201 Files" section after existing profile sections
  - Add upload button to open upload modal
  - Add document list container with table structure
  - Display columns: Type, Filename, Size, Upload Date, Actions
  - Add download and delete buttons for each document
  - Display storage quota with progress bar showing usage percentage
  - Style section with slate-800 background and slate-700 borders
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 13.5, 13.6, 14.4_

- [x] 14. Create employee upload modal in profile page
  - Add upload modal HTML structure to `src/Views/employees/profile.php`
  - Include modal header with title and close button
  - Add drag-and-drop file upload area with visual feedback
  - Add file input with accept attribute for allowed file types
  - Add document type dropdown (required field)
  - Add optional notes textarea
  - Add upload button with loading state
  - Display upload progress bar during file upload
  - Display selected file name and size before upload
  - Style modal with slate-800 background and purple-600 gradient button
  - _Requirements: 1.1, 12.1, 12.2, 12.3, 12.7, 12.8, 14.1, 14.2, 14.3_

- [x] 15. Implement employee profile JavaScript functions
  - Add openUploadModal() function to show upload modal
  - Add handleFileSelect(file) function to validate and display selected file
  - Add uploadDocument() function to upload file via API with progress tracking
  - Add loadMyDocuments() function to fetch employee's documents
  - Add displayMyDocuments(documents) function to render document list
  - Add downloadMyDocument(documentId) function to initiate download
  - Add deleteMyDocument(documentId) function with confirmation modal
  - Add updateStorageQuota(usedBytes, limitBytes) function to update progress bar
  - Use existing utility functions: showError, showSuccess, showLoading, showConfirm
  - Handle validation errors and display field-specific error messages
  - _Requirements: 1.8, 1.9, 2.6, 3.4, 4.1, 4.6, 12.5, 12.8, 13.5, 13.6, 15.1, 15.2, 15.3, 15.4, 15.5, 15.6, 15.7_

- [ ] 16. Checkpoint - Test core functionality
  - Run database migration in Supabase
  - Verify employee_documents table created with correct schema
  - Verify RLS policies are active
  - Test file upload as employee (valid file types, size limits)
  - Test storage quota enforcement (upload files until quota reached)
  - Test file download as employee
  - Test file deletion as employee
  - Verify files are stored in correct directory structure
  - Verify audit logs are created for all operations
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 17. Test admin functionality
  - Test "View 201 Files" button appears on employee list
  - Test admin modal opens and displays employee documents
  - Test document type filtering in admin modal
  - Test admin can download any employee document
  - Test admin can verify/unverify documents
  - Test admin can delete any employee document
  - Verify verification status updates correctly
  - Verify audit logs include admin user ID for admin operations

- [ ] 18. Test access control and security
  - Test employee cannot access another employee's documents
  - Test employee cannot verify documents (admin-only endpoint)
  - Test files are not accessible via direct URL
  - Test RLS policies prevent unauthorized database access
  - Test JWT authentication is required for all endpoints
  - Test file validation rejects invalid file types and sizes
  - Test MIME type validation prevents file type spoofing

- [ ] 19. Test error handling and edge cases
  - Test upload with invalid file extension returns appropriate error
  - Test upload with file size > 10MB returns appropriate error
  - Test upload exceeding storage quota returns appropriate error
  - Test upload with MIME type mismatch returns appropriate error
  - Test download of non-existent document returns 404
  - Test delete of non-existent document returns 404
  - Test unauthorized access returns 403
  - Verify all error messages are clear and actionable

- [ ] 20. Final checkpoint and UI/UX verification
  - Verify UI matches existing HRIS design (slate theme, purple buttons)
  - Test responsive design on mobile devices
  - Test drag-and-drop file upload functionality
  - Test upload progress bar displays correctly
  - Test storage quota progress bar updates correctly
  - Test file type icons display correctly (PDF, image, document icons)
  - Test confirmation modals for destructive actions
  - Test loading states during API operations
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- All tasks build on existing HRIS infrastructure (AuthService, AuditLogService, Core\Model, Core\Controller)
- File storage is outside public root for security
- RLS policies provide database-level access control
- Each task references specific requirements for traceability
- Testing tasks validate both functionality and security
- UI integration follows existing patterns from employee list and profile pages
