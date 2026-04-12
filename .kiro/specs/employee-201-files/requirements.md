# Requirements Document

## Introduction

The Employee 201 Files Management feature enables employees to upload and manage their employment documents (201 files) while allowing administrators to view, verify, and manage these documents. This feature integrates with the existing HRIS system's employee management, authentication, and audit logging capabilities.

## Glossary

- **Employee_201_Files_System**: The document management system for employee 201 files
- **Document**: A file uploaded by an employee (PDF, JPG, PNG, DOC, DOCX)
- **Document_Type**: Category of document (Resume, Birth Certificate, TIN, SSS, PhilHealth, Pag-IBIG, NBI Clearance, Medical Certificate, Diploma, Transcript, Other)
- **Storage_Quota**: Maximum storage space allocated per employee (50MB)
- **File_Validator**: Component that validates file type, size, and MIME type
- **Document_Verifier**: Admin function to mark documents as verified
- **Upload_Interface**: UI component for uploading documents
- **Document_List**: UI component displaying uploaded documents
- **Audit_Logger**: Component that logs all document operations

## Requirements

### Requirement 1: Document Upload

**User Story:** As an employee, I want to upload my 201 files from my profile page, so that I can maintain my employment records in the system.

#### Acceptance Criteria

1. WHEN an employee selects a file to upload, THE File_Validator SHALL verify the file extension is one of: pdf, jpg, jpeg, png, doc, docx
2. WHEN an employee selects a file to upload, THE File_Validator SHALL verify the MIME type matches the file extension
3. WHEN an employee selects a file to upload, THE File_Validator SHALL verify the file size does not exceed 10MB
4. WHEN an employee uploads a document, THE Employee_201_Files_System SHALL verify the employee's total storage does not exceed 50MB
5. WHEN a file passes validation, THE Employee_201_Files_System SHALL store the file in storage/201files/{employee_id}/ directory
6. WHEN a file is stored, THE Employee_201_Files_System SHALL create a database record with file metadata
7. WHEN a document upload completes, THE Audit_Logger SHALL log the upload operation with employee ID, document type, and timestamp
8. IF file validation fails, THEN THE Employee_201_Files_System SHALL return a descriptive error message
9. IF storage quota is exceeded, THEN THE Employee_201_Files_System SHALL return an error message indicating quota exceeded

### Requirement 2: Document Listing

**User Story:** As an employee, I want to see my uploaded documents, so that I can track which files I have submitted.

#### Acceptance Criteria

1. WHEN an employee views their profile page, THE Document_List SHALL display all documents uploaded by that employee
2. THE Document_List SHALL display document type, filename, file size, and upload date for each document
3. THE Document_List SHALL display total storage used and remaining quota
4. THE Document_List SHALL display a visual progress bar showing storage usage percentage
5. WHEN no documents are uploaded, THE Document_List SHALL display an empty state message
6. THE Document_List SHALL sort documents by upload date in descending order

### Requirement 3: Document Download

**User Story:** As an employee, I want to download my own documents, so that I can access my files when needed.

#### Acceptance Criteria

1. WHEN an employee clicks download on their document, THE Employee_201_Files_System SHALL verify the employee owns the document
2. WHEN download is authorized, THE Employee_201_Files_System SHALL stream the file with appropriate Content-Type header
3. WHEN download is authorized, THE Employee_201_Files_System SHALL set Content-Disposition header to attachment with original filename
4. WHEN a document is downloaded, THE Audit_Logger SHALL log the download operation with employee ID and document ID
5. IF the document does not exist, THEN THE Employee_201_Files_System SHALL return a 404 error
6. IF the employee does not own the document, THEN THE Employee_201_Files_System SHALL return a 403 error

### Requirement 4: Document Deletion

**User Story:** As an employee, I want to delete my own documents, so that I can remove outdated or incorrect files.

#### Acceptance Criteria

1. WHEN an employee clicks delete on their document, THE Upload_Interface SHALL display a confirmation modal
2. WHEN deletion is confirmed, THE Employee_201_Files_System SHALL verify the employee owns the document
3. WHEN deletion is authorized, THE Employee_201_Files_System SHALL delete the file from storage
4. WHEN the file is deleted, THE Employee_201_Files_System SHALL delete the database record
5. WHEN a document is deleted, THE Audit_Logger SHALL log the deletion operation with employee ID and document ID
6. WHEN deletion completes, THE Document_List SHALL refresh to show updated list
7. IF the employee does not own the document, THEN THE Employee_201_Files_System SHALL return a 403 error

### Requirement 5: Admin Document Access

**User Story:** As an admin, I want to see a "View 201 Files" button on each employee row in the employee list, so that I can quickly access employee documents.

#### Acceptance Criteria

1. WHEN an admin views the employee list, THE Employee_201_Files_System SHALL display a "View 201 Files" button for each employee
2. THE Employee_201_Files_System SHALL display a badge showing document count for each employee
3. WHEN an admin clicks "View 201 Files", THE Employee_201_Files_System SHALL open a modal displaying all documents for that employee
4. THE Document_List SHALL display document type, filename, file size, upload date, and verification status
5. THE Document_List SHALL allow filtering by document type
6. THE Document_List SHALL display total document count and total storage used

### Requirement 6: Admin Document Verification

**User Story:** As an admin, I want to verify documents, so that I can indicate which documents have been reviewed and approved.

#### Acceptance Criteria

1. WHEN an admin views a document in the modal, THE Document_Verifier SHALL display a verification checkbox
2. WHEN an admin checks the verification checkbox, THE Employee_201_Files_System SHALL update the document's is_verified status to true
3. WHEN an admin unchecks the verification checkbox, THE Employee_201_Files_System SHALL update the document's is_verified status to false
4. WHEN verification status changes, THE Employee_201_Files_System SHALL record the admin's user ID and timestamp
5. WHEN verification status changes, THE Audit_Logger SHALL log the verification operation
6. THE Document_List SHALL visually indicate which documents are verified

### Requirement 7: Admin Document Management

**User Story:** As an admin, I want to download and delete employee documents, so that I can manage employee records.

#### Acceptance Criteria

1. WHEN an admin clicks download on any employee document, THE Employee_201_Files_System SHALL stream the file
2. WHEN an admin clicks delete on any employee document, THE Upload_Interface SHALL display a confirmation modal
3. WHEN deletion is confirmed, THE Employee_201_Files_System SHALL delete the file from storage
4. WHEN the file is deleted, THE Employee_201_Files_System SHALL delete the database record
5. WHEN an admin deletes a document, THE Audit_Logger SHALL log the deletion with admin user ID and employee ID
6. WHEN deletion completes, THE Document_List SHALL refresh to show updated list

### Requirement 8: File Storage Security

**User Story:** As a system administrator, I want files stored securely outside the public root, so that documents cannot be accessed directly via URL.

#### Acceptance Criteria

1. THE Employee_201_Files_System SHALL store all files in storage/201files/ directory outside the public root
2. THE Employee_201_Files_System SHALL organize files in subdirectories by employee_id
3. THE Employee_201_Files_System SHALL generate unique filenames using format: {employee_id}_{timestamp}_{sanitized_filename}
4. THE Employee_201_Files_System SHALL sanitize filenames by removing special characters and replacing spaces with underscores
5. THE Employee_201_Files_System SHALL set file permissions to 644 for files and 755 for directories
6. THE Employee_201_Files_System SHALL require authentication for all file access operations
7. THE Employee_201_Files_System SHALL enforce access control at both application and database levels

### Requirement 9: Access Control

**User Story:** As a system administrator, I want access control enforced at the database level, so that unauthorized access is prevented even if application logic fails.

#### Acceptance Criteria

1. THE Employee_201_Files_System SHALL implement Row Level Security policies on the employee_documents table
2. THE Employee_201_Files_System SHALL allow employees to view only their own documents via RLS policy
3. THE Employee_201_Files_System SHALL allow employees to insert only their own documents via RLS policy
4. THE Employee_201_Files_System SHALL allow employees to delete only their own documents via RLS policy
5. THE Employee_201_Files_System SHALL allow admins to view all documents via RLS policy
6. THE Employee_201_Files_System SHALL allow admins to update all documents via RLS policy
7. THE Employee_201_Files_System SHALL allow admins to delete all documents via RLS policy

### Requirement 10: Audit Logging

**User Story:** As a system administrator, I want all document operations logged, so that I can track document access and modifications for compliance.

#### Acceptance Criteria

1. WHEN a document is uploaded, THE Audit_Logger SHALL log the operation with action "document_upload"
2. WHEN a document is downloaded, THE Audit_Logger SHALL log the operation with action "document_download"
3. WHEN a document is deleted, THE Audit_Logger SHALL log the operation with action "document_delete"
4. WHEN a document is verified, THE Audit_Logger SHALL log the operation with action "document_verify"
5. THE Audit_Logger SHALL include employee_id, document_id, user_id, and timestamp in all log entries
6. THE Audit_Logger SHALL include document_type and file_name in upload log entries
7. THE Audit_Logger SHALL integrate with the existing AuditLogService

### Requirement 11: API Response Format

**User Story:** As a frontend developer, I want consistent API response formats, so that I can handle responses uniformly across the application.

#### Acceptance Criteria

1. THE Employee_201_Files_System SHALL return responses with success, message, and data fields
2. WHEN an operation succeeds, THE Employee_201_Files_System SHALL set success to true
3. WHEN an operation fails, THE Employee_201_Files_System SHALL set success to false
4. WHEN validation fails, THE Employee_201_Files_System SHALL include an errors object with field-specific error messages
5. WHEN a document is uploaded, THE Employee_201_Files_System SHALL return the document metadata in the data field
6. WHEN documents are listed, THE Employee_201_Files_System SHALL return documents array, total_size, total_count, storage_limit, and storage_used_percentage

### Requirement 12: User Interface Integration

**User Story:** As a user, I want the 201 files interface to match the existing HRIS design, so that the experience is consistent.

#### Acceptance Criteria

1. THE Upload_Interface SHALL use Tailwind CSS with the slate color theme
2. THE Upload_Interface SHALL use slate-800 background for cards with slate-700 borders
3. THE Upload_Interface SHALL use purple-600 to purple-700 gradient for primary action buttons
4. THE Upload_Interface SHALL use existing modal patterns from the employee list page
5. THE Upload_Interface SHALL use existing utility functions: showError, showSuccess, showLoading, showConfirm
6. THE Document_List SHALL display file type icons (PDF icon for PDFs, image icon for images, document icon for others)
7. THE Upload_Interface SHALL support drag-and-drop file selection
8. THE Upload_Interface SHALL display upload progress during file upload

### Requirement 13: Storage Quota Enforcement

**User Story:** As a system administrator, I want storage quotas enforced per employee, so that storage resources are managed fairly.

#### Acceptance Criteria

1. THE Employee_201_Files_System SHALL enforce a 50MB storage limit per employee
2. WHEN an employee attempts to upload a file, THE Employee_201_Files_System SHALL calculate total storage used by that employee
3. WHEN total storage plus new file size exceeds 50MB, THE Employee_201_Files_System SHALL reject the upload
4. WHEN upload is rejected due to quota, THE Employee_201_Files_System SHALL return an error message indicating quota exceeded
5. THE Document_List SHALL display current storage usage and remaining quota
6. THE Document_List SHALL display storage usage as a percentage with a visual progress bar

### Requirement 14: Document Type Management

**User Story:** As an employee, I want to categorize my documents by type, so that they are organized and easy to find.

#### Acceptance Criteria

1. THE Upload_Interface SHALL provide a dropdown to select document type
2. THE Employee_201_Files_System SHALL support these document types: Resume, Birth Certificate, TIN, SSS, PhilHealth, Pag-IBIG, NBI Clearance, Medical Certificate, Diploma, Transcript, Other
3. WHEN an employee uploads a document, THE Employee_201_Files_System SHALL require document type selection
4. THE Document_List SHALL display document type for each document
5. WHEN an admin views documents, THE Document_List SHALL allow filtering by document type
6. THE Employee_201_Files_System SHALL store document_type in the database

### Requirement 15: Error Handling

**User Story:** As a user, I want clear error messages when operations fail, so that I understand what went wrong and how to fix it.

#### Acceptance Criteria

1. WHEN file extension is invalid, THE Employee_201_Files_System SHALL return error message "Only PDF, JPG, PNG, DOC, DOCX files are allowed"
2. WHEN file size exceeds 10MB, THE Employee_201_Files_System SHALL return error message "File size exceeds 10MB limit"
3. WHEN storage quota is exceeded, THE Employee_201_Files_System SHALL return error message "Storage quota exceeded. Maximum 50MB per employee"
4. WHEN MIME type does not match extension, THE Employee_201_Files_System SHALL return error message "Invalid file type detected"
5. WHEN document is not found, THE Employee_201_Files_System SHALL return error message "Document not found"
6. WHEN access is denied, THE Employee_201_Files_System SHALL return error message "You do not have permission to access this document"
7. THE Upload_Interface SHALL display error messages using the existing showError utility function
