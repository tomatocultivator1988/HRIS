# Design Document: Employee 201 Files Management

## Overview

The Employee 201 Files Management system enables employees to upload, manage, and access their employment documents while providing administrators with comprehensive document oversight capabilities. This feature integrates seamlessly with the existing HRIS system's authentication, employee management, and audit logging infrastructure.

### Purpose

Provide a secure, user-friendly document management system that:
- Allows employees to maintain their employment records digitally
- Enables administrators to verify and manage employee documents
- Ensures document security through access control and audit logging
- Maintains compliance with data protection requirements

### Scope

**In Scope:**
- Document upload with validation (file type, size, MIME type)
- Document listing, download, and deletion
- Storage quota management (50MB per employee)
- Admin verification workflow
- Row-level security policies
- Comprehensive audit logging
- UI integration with existing employee list and profile pages

**Out of Scope:**
- Document versioning
- Document sharing between employees
- Bulk document operations
- Document templates or forms
- OCR or document content analysis
- Email notifications for document uploads

### Key Design Decisions

1. **File Storage Location**: Files stored outside public root (`storage/201files/`) to prevent direct URL access
2. **Access Control**: Dual-layer security with application-level checks and database RLS policies
3. **File Naming**: UUID-based naming with timestamps to prevent collisions and ensure uniqueness
4. **Storage Quota**: Per-employee limit of 50MB with per-file limit of 10MB
5. **Document Types**: Predefined categories for common Philippine employment documents
6. **Verification Workflow**: Admin-only verification with timestamp and user tracking
7. **No Separate Menu Item**: Integrated into existing employee list and profile pages

## Architecture

### System Context

```
┌─────────────────────────────────────────────────────────────┐
│                     HRIS System                              │
│                                                              │
│  ┌──────────────┐      ┌──────────────┐                    │
│  │   Employee   │      │    Admin     │                    │
│  │   Profile    │      │ Employee List│                    │
│  └──────┬───────┘      └──────┬───────┘                    │
│         │                     │                             │
│         └─────────┬───────────┘                             │
│                   │                                         │
│         ┌─────────▼──────────┐                             │
│         │  DocumentController │                             │
│         └─────────┬──────────┘                             │
│                   │                                         │
│         ┌─────────▼──────────┐                             │
│         │  DocumentService   │                             │
│         └─────────┬──────────┘                             │
│                   │                                         │
│    ┌──────────────┼──────────────┐                         │
│    │              │              │                         │
│ ┌──▼───┐    ┌────▼─────┐   ┌───▼────┐                    │
│ │ File │    │ Database │   │ Audit  │                    │
│ │System│    │   RLS    │   │  Log   │                    │
│ └──────┘    └──────────┘   └────────┘                    │
└─────────────────────────────────────────────────────────────┘
```

### Component Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │ Employee Profile │         │  Admin Modal     │         │
│  │  - Upload UI     │         │  - Document List │         │
│  │  - Document List │         │  - Verification  │         │
│  │  - Storage Quota │         │  - Filtering     │         │
│  └────────┬─────────┘         └────────┬─────────┘         │
└───────────┼──────────────────────────────┼──────────────────┘
            │                              │
┌───────────┼──────────────────────────────┼──────────────────┐
│           │      Application Layer       │                  │
│  ┌────────▼──────────────────────────────▼────────┐         │
│  │         DocumentController                     │         │
│  │  - upload()      - download()                  │         │
│  │  - list()        - delete()                    │         │
│  │  - verify()                                    │         │
│  └────────┬───────────────────────────────────────┘         │
│           │                                                  │
│  ┌────────▼───────────────────────────────────────┐         │
│  │         DocumentService                        │         │
│  │  - validateFile()    - calculateStorageUsed()  │         │
│  │  - storeFile()       - deleteFile()            │         │
│  │  - generateFileName()                          │         │
│  └────────┬───────────────────────────────────────┘         │
└───────────┼──────────────────────────────────────────────────┘
            │
┌───────────┼──────────────────────────────────────────────────┐
│           │         Data Layer                               │
│  ┌────────▼───────────────────────────────────────┐         │
│  │      EmployeeDocument Model                    │         │
│  │  - find()        - create()                    │         │
│  │  - findAll()     - update()                    │         │
│  │  - delete()                                    │         │
│  └────────┬───────────────────────────────────────┘         │
│           │                                                  │
│  ┌────────▼───────────────────────────────────────┐         │
│  │    Supabase Database (employee_documents)      │         │
│  │    + Row Level Security Policies               │         │
│  └────────────────────────────────────────────────┘         │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

**Document Upload Flow:**
```
Employee → Upload UI → DocumentController.upload()
    → AuthService (verify JWT)
    → DocumentService.validateFile()
        → Check file extension
        → Check MIME type
        → Check file size
        → Check storage quota
    → DocumentService.storeFile()
        → Generate unique filename
        → Create directory if needed
        → Move file to storage
    → EmployeeDocument.create()
        → Insert database record
        → RLS policy enforces ownership
    → AuditLogService.log()
    → Return success response
```

**Document Download Flow:**
```
User → Download Button → DocumentController.download()
    → AuthService (verify JWT)
    → EmployeeDocument.find()
        → RLS policy enforces access control
    → Check file exists
    → Stream file with headers
    → AuditLogService.log()
```

**Admin Verification Flow:**
```
Admin → Verify Checkbox → DocumentController.verify()
    → AuthService (verify JWT + admin role)
    → EmployeeDocument.update()
        → Set is_verified, verified_by, verified_at
        → RLS policy allows admin update
    → AuditLogService.log()
    → Return success response
```

## Components and Interfaces

### Backend Components

#### 1. DocumentController

**Responsibility**: Handle HTTP requests for document operations

**Methods:**
- `upload(Request $request, string $employeeId): Response`
  - Validates multipart/form-data request
  - Calls DocumentService for validation and storage
  - Returns document metadata or error response

- `list(Request $request, string $employeeId): Response`
  - Retrieves all documents for an employee
  - Calculates storage statistics
  - Returns documents array with metadata

- `download(Request $request, string $employeeId, string $documentId): Response`
  - Verifies access permissions
  - Streams file with appropriate headers
  - Logs download operation

- `delete(Request $request, string $employeeId, string $documentId): Response`
  - Verifies ownership or admin role
  - Deletes file and database record
  - Logs deletion operation

- `verify(Request $request, string $employeeId, string $documentId): Response`
  - Admin-only endpoint
  - Updates verification status
  - Logs verification operation

**Dependencies:**
- `DocumentService`: Business logic and file operations
- `AuthService`: JWT authentication
- `AuditLogService`: Operation logging
- `EmployeeDocument`: Data access

#### 2. DocumentService

**Responsibility**: Business logic for document management

**Methods:**
- `validateFile(array $file): array`
  - Validates file extension against whitelist
  - Validates MIME type matches extension
  - Validates file size ≤ 10MB
  - Returns validation result with errors

- `checkStorageQuota(string $employeeId, int $newFileSize): bool`
  - Calculates total storage used by employee
  - Checks if adding new file exceeds 50MB limit
  - Returns true if within quota

- `storeFile(array $file, string $employeeId): array`
  - Generates unique filename
  - Creates employee directory if needed
  - Moves uploaded file to storage
  - Sets file permissions (644)
  - Returns file path and metadata

- `deleteFile(string $filePath): bool`
  - Deletes physical file from storage
  - Returns success status

- `generateFileName(string $employeeId, string $originalName): string`
  - Format: `{employeeId}_{timestamp}_{sanitizedName}`
  - Sanitizes filename (removes special chars, replaces spaces)
  - Returns unique filename

- `calculateStorageUsed(string $employeeId): int`
  - Sums file_size for all employee documents
  - Returns total bytes used

**Constants:**
```php
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_STORAGE_PER_EMPLOYEE = 50 * 1024 * 1024; // 50MB
const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
const ALLOWED_MIME_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
const STORAGE_PATH = 'storage/201files/';
```

#### 3. EmployeeDocument Model

**Responsibility**: Data access for employee_documents table

**Methods:**
- `find(string $id): ?array`
- `findAll(array $filters): array`
- `findByEmployeeId(string $employeeId): array`
- `create(array $data): array`
- `update(string $id, array $data): bool`
- `delete(string $id): bool`

**Properties:**
- `id`: UUID (primary key)
- `employee_id`: UUID (foreign key)
- `document_type`: string
- `file_name`: string
- `file_path`: string
- `file_size`: integer
- `mime_type`: string
- `uploaded_by`: UUID
- `uploaded_at`: timestamp
- `notes`: text
- `is_verified`: boolean
- `verified_by`: UUID
- `verified_at`: timestamp
- `created_at`: timestamp
- `updated_at`: timestamp

### Frontend Components

#### 1. Admin Document Modal (employees/list.php)

**Features:**
- Triggered by "View 201 Files" button on employee row
- Displays all documents for selected employee
- Filter by document type dropdown
- Document list with icons based on file type
- Download button per document
- Delete button per document (with confirmation)
- Verification checkbox per document
- Storage statistics display

**Key Functions:**
- `view201Files(employeeId, employeeName)`: Opens modal and loads documents
- `loadDocuments(employeeId)`: Fetches documents via API
- `displayDocuments(documents)`: Renders document list
- `filterDocuments()`: Filters by selected document type
- `downloadDocument(employeeId, documentId)`: Initiates download
- `toggleVerify(documentId, isVerified)`: Updates verification status
- `deleteDocument(documentId)`: Deletes document with confirmation

#### 2. Employee Upload Section (employees/profile.php)

**Features:**
- "My 201 Files" section in profile page
- Upload button opens modal
- Drag-and-drop file upload area
- Document type selection dropdown
- Optional notes field
- Upload progress bar
- Document list with download/delete actions
- Storage quota display with progress bar

**Key Functions:**
- `openUploadModal()`: Opens upload modal
- `handleFileSelect(file)`: Validates and displays selected file
- `uploadDocument()`: Uploads file via API with progress
- `loadMyDocuments()`: Fetches employee's documents
- `displayMyDocuments(documents)`: Renders document list
- `downloadMyDocument(documentId)`: Initiates download
- `deleteMyDocument(documentId)`: Deletes document with confirmation

### API Endpoints

#### POST /api/employees/{employeeId}/documents
- **Purpose**: Upload a new document
- **Auth**: JWT token (employee must own record or be admin)
- **Request**: multipart/form-data with file, document_type, notes
- **Response**: Document metadata or validation errors

#### GET /api/employees/{employeeId}/documents
- **Purpose**: List all documents for an employee
- **Auth**: JWT token (employee must own record or be admin)
- **Response**: Documents array, storage statistics

#### GET /api/employees/{employeeId}/documents/{documentId}/download
- **Purpose**: Download a specific document
- **Auth**: JWT token (employee must own record or be admin)
- **Response**: File stream with appropriate headers

#### DELETE /api/employees/{employeeId}/documents/{documentId}
- **Purpose**: Delete a document
- **Auth**: JWT token (employee must own record or be admin)
- **Response**: Success or error message

#### PUT /api/employees/{employeeId}/documents/{documentId}/verify
- **Purpose**: Verify/unverify a document (admin only)
- **Auth**: JWT token with admin role
- **Request**: JSON with is_verified, notes
- **Response**: Updated document metadata

## Data Models

### Database Schema

#### employee_documents Table

```sql
CREATE TABLE employee_documents (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    employee_id UUID NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by UUID NOT NULL REFERENCES users(id),
    uploaded_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by UUID REFERENCES users(id),
    verified_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```

**Indexes:**
- `idx_employee_documents_employee_id` on `employee_id` (for fast employee document lookups)
- `idx_employee_documents_document_type` on `document_type` (for filtering)
- `idx_employee_documents_uploaded_at` on `uploaded_at` (for sorting)

**Constraints:**
- `employee_id` references `employees(id)` with CASCADE delete
- `uploaded_by` references `users(id)` (cannot be null)
- `verified_by` references `users(id)` (nullable)
- `file_size` must be positive integer
- `document_type` must be one of predefined types

### Document Types Enumeration

```php
const DOCUMENT_TYPES = [
    'Resume',
    'Birth Certificate',
    'TIN',
    'SSS',
    'PhilHealth',
    'Pag-IBIG',
    'NBI Clearance',
    'Medical Certificate',
    'Diploma',
    'Transcript',
    'Other'
];
```

### Row Level Security Policies

#### SELECT Policies

**Admin View All:**
```sql
CREATE POLICY "Admins can view all employee documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );
```

**Employee View Own:**
```sql
CREATE POLICY "Employees can view own documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );
```

#### INSERT Policy

**Employee Upload Own:**
```sql
CREATE POLICY "Employees can upload own documents" ON employee_documents
    FOR INSERT WITH CHECK (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );
```

#### UPDATE Policy

**Admin Update All:**
```sql
CREATE POLICY "Admins can update documents" ON employee_documents
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );
```

#### DELETE Policies

**Employee Delete Own:**
```sql
CREATE POLICY "Employees can delete own documents" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );
```

**Admin Delete All:**
```sql
CREATE POLICY "Admins can delete any document" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );
```

### File Storage Structure

```
storage/
└── 201files/
    ├── {employee_id_1}/
    │   ├── {employee_id_1}_1712345678_resume.pdf
    │   ├── {employee_id_1}_1712345890_birth_certificate.jpg
    │   └── {employee_id_1}_1712346000_tin.pdf
    ├── {employee_id_2}/
    │   ├── {employee_id_2}_1712347000_resume.pdf
    │   └── {employee_id_2}_1712347100_sss.pdf
    └── ...
```

**File Permissions:**
- Directories: 755 (rwxr-xr-x)
- Files: 644 (rw-r--r--)

## Error Handling

### Validation Errors

**File Extension Invalid:**
```json
{
    "success": false,
    "message": "Invalid file type",
    "errors": {
        "file": "Only PDF, JPG, PNG, DOC, DOCX files are allowed"
    }
}
```

**File Size Exceeded:**
```json
{
    "success": false,
    "message": "File too large",
    "errors": {
        "file": "File size exceeds 10MB limit"
    }
}
```

**MIME Type Mismatch:**
```json
{
    "success": false,
    "message": "Invalid file type detected",
    "errors": {
        "file": "File type does not match extension"
    }
}
```

**Storage Quota Exceeded:**
```json
{
    "success": false,
    "message": "Storage quota exceeded",
    "errors": {
        "storage": "Storage quota exceeded. Maximum 50MB per employee"
    }
}
```

### Access Control Errors

**Unauthorized Access:**
```json
{
    "success": false,
    "message": "You do not have permission to access this document"
}
```

**Document Not Found:**
```json
{
    "success": false,
    "message": "Document not found"
}
```

### File System Errors

**Storage Directory Not Writable:**
```json
{
    "success": false,
    "message": "Storage error",
    "errors": {
        "storage": "Unable to write to storage directory"
    }
}
```

**File Upload Failed:**
```json
{
    "success": false,
    "message": "Upload failed",
    "errors": {
        "file": "Failed to upload file. Please try again."
    }
}
```

### Error Handling Strategy

1. **Validation Layer**: Catch validation errors early in DocumentService
2. **Try-Catch Blocks**: Wrap file operations in try-catch for I/O errors
3. **Database Errors**: Let RLS policies enforce access control, catch exceptions
4. **Logging**: Log all errors with context for debugging
5. **User-Friendly Messages**: Return clear, actionable error messages
6. **Rollback**: If database insert fails after file upload, delete the file

**Example Error Handling in Controller:**
```php
try {
    // Validate file
    $validation = $this->documentService->validateFile($_FILES['file']);
    if (!$validation['valid']) {
        return $this->jsonResponse([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validation['errors']
        ], 400);
    }
    
    // Check quota
    if (!$this->documentService->checkStorageQuota($employeeId, $_FILES['file']['size'])) {
        return $this->jsonResponse([
            'success' => false,
            'message' => 'Storage quota exceeded',
            'errors' => ['storage' => 'Storage quota exceeded. Maximum 50MB per employee']
        ], 400);
    }
    
    // Store file
    $fileData = $this->documentService->storeFile($_FILES['file'], $employeeId);
    
    // Create database record
    $document = $this->employeeDocument->create([
        'employee_id' => $employeeId,
        'document_type' => $request->input('document_type'),
        'file_name' => $_FILES['file']['name'],
        'file_path' => $fileData['path'],
        'file_size' => $_FILES['file']['size'],
        'mime_type' => $_FILES['file']['type'],
        'uploaded_by' => $user['id'],
        'notes' => $request->input('notes')
    ]);
    
    // Log operation
    $this->auditLogService->log('document_upload', [
        'employee_id' => $employeeId,
        'document_id' => $document['id'],
        'document_type' => $document['document_type'],
        'file_name' => $document['file_name']
    ]);
    
    return $this->jsonResponse([
        'success' => true,
        'message' => 'Document uploaded successfully',
        'data' => ['document' => $document]
    ]);
    
} catch (Exception $e) {
    // Rollback: delete file if it was uploaded
    if (isset($fileData['path'])) {
        $this->documentService->deleteFile($fileData['path']);
    }
    
    error_log("Document upload error: " . $e->getMessage());
    
    return $this->jsonResponse([
        'success' => false,
        'message' => 'Upload failed. Please try again.'
    ], 500);
}
```

## Testing Strategy

### Why Property-Based Testing Does Not Apply

This feature is **not suitable for property-based testing** because it primarily involves:

1. **File I/O Operations**: Uploading, storing, and streaming files are side-effect operations with no pure function properties to test
2. **CRUD Operations**: Simple database create, read, update, delete operations without complex transformation logic
3. **UI Interactions**: Drag-and-drop, modals, progress bars are UI behaviors not amenable to universal properties
4. **Integration Points**: File system, database, and authentication are external dependencies

Property-based testing works best for pure functions with universal properties (e.g., parsers, serializers, algorithms). This feature requires **example-based unit tests** and **integration tests** instead.

### Unit Testing Approach

#### DocumentService Tests

**File Validation Tests:**
- Test valid file extensions (pdf, jpg, jpeg, png, doc, docx) are accepted
- Test invalid file extensions (exe, zip, txt) are rejected
- Test MIME type matching (pdf file must have application/pdf MIME type)
- Test MIME type mismatch detection (pdf extension with image/jpeg MIME type)
- Test file size validation (10MB limit)
- Test edge case: exactly 10MB file is accepted
- Test edge case: 10MB + 1 byte file is rejected

**Storage Quota Tests:**
- Test employee with 0MB used can upload 10MB file
- Test employee with 45MB used can upload 5MB file
- Test employee with 45MB used cannot upload 6MB file
- Test employee with 50MB used cannot upload any file
- Test calculation of total storage used

**File Naming Tests:**
- Test generated filename format: `{employeeId}_{timestamp}_{sanitizedName}`
- Test filename sanitization removes special characters
- Test filename sanitization replaces spaces with underscores
- Test filename preserves extension

**File Storage Tests (with mocks):**
- Test file is moved to correct directory
- Test directory is created if it doesn't exist
- Test file permissions are set to 644
- Test directory permissions are set to 755

#### DocumentController Tests

**Upload Endpoint Tests:**
- Test successful upload returns 200 with document metadata
- Test missing file returns 400 with error message
- Test missing document_type returns 400 with error message
- Test unauthorized access returns 403
- Test employee can upload to own record
- Test employee cannot upload to another employee's record
- Test admin can upload to any employee's record

**List Endpoint Tests:**
- Test returns all documents for employee
- Test returns storage statistics (total_size, total_count, storage_used_percentage)
- Test employee can only see own documents
- Test admin can see all employee documents
- Test empty list returns empty array

**Download Endpoint Tests:**
- Test successful download returns file stream with correct headers
- Test Content-Type header matches MIME type
- Test Content-Disposition header includes original filename
- Test employee can download own documents
- Test employee cannot download another employee's documents
- Test admin can download any document
- Test non-existent document returns 404

**Delete Endpoint Tests:**
- Test successful deletion removes file and database record
- Test employee can delete own documents
- Test employee cannot delete another employee's documents
- Test admin can delete any document
- Test non-existent document returns 404

**Verify Endpoint Tests:**
- Test admin can verify documents
- Test non-admin cannot verify documents
- Test verification sets is_verified, verified_by, verified_at
- Test unverification clears verification fields

#### EmployeeDocument Model Tests

**CRUD Tests:**
- Test create inserts record and returns data
- Test find returns document by ID
- Test findByEmployeeId returns all employee documents
- Test update modifies record
- Test delete removes record

### Integration Testing Approach

#### End-to-End Upload Flow:**
1. Authenticate as employee
2. Upload valid PDF file with document_type
3. Verify file exists in storage directory
4. Verify database record created
5. Verify audit log entry created
6. Verify storage quota updated

**End-to-End Download Flow:**
1. Authenticate as employee
2. Upload document
3. Download document
4. Verify file content matches uploaded file
5. Verify audit log entry created

**End-to-End Delete Flow:**
1. Authenticate as employee
2. Upload document
3. Delete document
4. Verify file removed from storage
5. Verify database record removed
6. Verify audit log entry created

**Admin Verification Flow:**
1. Authenticate as admin
2. Employee uploads document
3. Admin verifies document
4. Verify is_verified = true
5. Verify verified_by = admin user ID
6. Verify verified_at timestamp set
7. Verify audit log entry created

**RLS Policy Tests:**
1. Employee A uploads document
2. Employee B attempts to access Employee A's document
3. Verify access denied (403)
4. Admin attempts to access Employee A's document
5. Verify access granted (200)

### Test Data

**Valid Test Files:**
- `test_resume.pdf` (1MB)
- `test_certificate.jpg` (500KB)
- `test_document.docx` (2MB)

**Invalid Test Files:**
- `test_large.pdf` (11MB) - exceeds size limit
- `test_invalid.exe` - invalid extension
- `test_mismatch.pdf` (actually a JPEG) - MIME type mismatch

**Test Employees:**
- Employee with 0MB storage used
- Employee with 45MB storage used
- Employee with 50MB storage used

### Testing Tools

- **PHPUnit**: Unit and integration tests
- **Mockery**: Mocking dependencies (file system, database)
- **Supabase Test Database**: Isolated test environment
- **Test Storage Directory**: `storage/test/201files/`

### Test Coverage Goals

- **Unit Tests**: 80%+ code coverage
- **Integration Tests**: All critical paths covered
- **Edge Cases**: File size limits, storage quotas, access control
- **Error Scenarios**: Invalid files, unauthorized access, storage errors

### Manual Testing Checklist

**Employee Upload:**
- [ ] Can upload PDF, JPG, PNG, DOC, DOCX files
- [ ] Cannot upload invalid file types
- [ ] Cannot upload files > 10MB
- [ ] Cannot exceed 50MB storage quota
- [ ] Drag-and-drop works
- [ ] Upload progress bar displays
- [ ] Success message displays
- [ ] Document appears in list immediately

**Employee Document Management:**
- [ ] Can see own documents
- [ ] Can download own documents
- [ ] Can delete own documents
- [ ] Cannot see other employees' documents
- [ ] Storage quota displays correctly
- [ ] Progress bar updates correctly

**Admin Document Access:**
- [ ] "View 201 Files" button appears on employee list
- [ ] Document count badge displays correctly
- [ ] Modal opens with employee documents
- [ ] Can filter by document type
- [ ] Can download any employee document
- [ ] Can delete any employee document
- [ ] Can verify/unverify documents
- [ ] Verification status displays correctly

**Security:**
- [ ] Files not accessible via direct URL
- [ ] JWT authentication required for all endpoints
- [ ] RLS policies enforce access control
- [ ] Audit logs created for all operations

**UI/UX:**
- [ ] Matches existing HRIS design (slate theme)
- [ ] Responsive on mobile devices
- [ ] Error messages are clear and actionable
- [ ] Loading states display during operations
- [ ] Confirmation modals for destructive actions

