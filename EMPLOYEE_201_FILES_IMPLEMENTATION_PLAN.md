# Employee 201 Files - Implementation Plan

## Overview
Add 201 files management feature to the HRIS system. Employees can upload their documents, and admins can view them from the employee list without adding a new sidebar menu item.

## User Stories

### Admin
- As an admin, I want to see a "View 201 Files" button on each employee row in the employee list
- As an admin, I want to view all uploaded documents for a specific employee
- As an admin, I want to download individual documents
- As an admin, I want to see document metadata (filename, upload date, file size, type)
- As an admin, I want to delete documents if needed

### Employee
- As an employee, I want to upload my 201 files from my profile page
- As an employee, I want to see my uploaded documents
- As an employee, I want to download my own documents
- As an employee, I want to delete my own documents
- As an employee, I want to see upload progress and file size limits

## Database Schema

### New Table: `employee_documents`

```sql
CREATE TABLE employee_documents (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    employee_id UUID NOT NULL REFERENCES employees(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL, -- 'Resume', 'Birth Certificate', 'TIN', 'SSS', 'PhilHealth', 'Pag-IBIG', 'NBI Clearance', 'Medical Certificate', 'Diploma', 'Transcript', 'Other'
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL, -- Path in storage (e.g., 'uploads/201files/{employee_id}/{filename}')
    file_size INTEGER NOT NULL, -- Size in bytes
    mime_type VARCHAR(100) NOT NULL, -- 'application/pdf', 'image/jpeg', etc.
    uploaded_by UUID NOT NULL REFERENCES users(id),
    uploaded_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    is_verified BOOLEAN DEFAULT FALSE, -- Admin can mark as verified
    verified_by UUID REFERENCES users(id),
    verified_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_employee_documents_employee_id ON employee_documents(employee_id);
CREATE INDEX idx_employee_documents_document_type ON employee_documents(document_type);
CREATE INDEX idx_employee_documents_uploaded_at ON employee_documents(uploaded_at);

-- RLS Policies
ALTER TABLE employee_documents ENABLE ROW LEVEL SECURITY;

-- Admins can see all documents
CREATE POLICY "Admins can view all employee documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );

-- Employees can only see their own documents
CREATE POLICY "Employees can view own documents" ON employee_documents
    FOR SELECT USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.user_id = auth.uid()
        )
    );

-- Employees can upload their own documents
CREATE POLICY "Employees can upload own documents" ON employee_documents
    FOR INSERT WITH CHECK (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.user_id = auth.uid()
        )
    );

-- Employees can delete their own documents
CREATE POLICY "Employees can delete own documents" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.user_id = auth.uid()
        )
    );

-- Admins can delete any document
CREATE POLICY "Admins can delete any document" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );

-- Admins can update documents (verify, add notes)
CREATE POLICY "Admins can update documents" ON employee_documents
    FOR UPDATE USING (
        EXISTS (
            SELECT 1 FROM users 
            WHERE users.id = auth.uid() 
            AND users.role = 'admin'
        )
    );
```

## File Storage

### Storage Location
- **Local Development**: `public/uploads/201files/{employee_id}/{filename}`
- **Production**: Supabase Storage bucket `employee-201-files`

### File Naming Convention
- Format: `{employee_id}_{timestamp}_{original_filename}`
- Example: `550e8400-e29b-41d4-a716-446655440000_1712345678_resume.pdf`

### File Size Limits
- Maximum file size: 10 MB per file
- Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX
- Maximum total storage per employee: 50 MB

## API Endpoints

### 1. Upload Document
```
POST /api/employees/{employee_id}/documents
Content-Type: multipart/form-data

Request Body:
- file: File (required)
- document_type: String (required)
- notes: String (optional)

Response:
{
    "success": true,
    "message": "Document uploaded successfully",
    "data": {
        "document": {
            "id": "uuid",
            "employee_id": "uuid",
            "document_type": "Resume",
            "file_name": "resume.pdf",
            "file_size": 1024000,
            "uploaded_at": "2026-04-11T10:30:00Z"
        }
    }
}
```

### 2. Get Employee Documents
```
GET /api/employees/{employee_id}/documents

Response:
{
    "success": true,
    "data": {
        "documents": [
            {
                "id": "uuid",
                "document_type": "Resume",
                "file_name": "resume.pdf",
                "file_size": 1024000,
                "mime_type": "application/pdf",
                "uploaded_at": "2026-04-11T10:30:00Z",
                "is_verified": false,
                "notes": "Updated resume"
            }
        ],
        "total_size": 1024000,
        "total_count": 1
    }
}
```

### 3. Download Document
```
GET /api/employees/{employee_id}/documents/{document_id}/download

Response: File stream with appropriate headers
```

### 4. Delete Document
```
DELETE /api/employees/{employee_id}/documents/{document_id}

Response:
{
    "success": true,
    "message": "Document deleted successfully"
}
```

### 5. Verify Document (Admin only)
```
PUT /api/employees/{employee_id}/documents/{document_id}/verify

Request Body:
{
    "is_verified": true,
    "notes": "Verified by HR"
}

Response:
{
    "success": true,
    "message": "Document verified successfully"
}
```

## UI Components

### 1. Admin View - Employee List Enhancement

**Location**: `src/Views/employees/list.php`

**Changes**:
- Add "View 201 Files" button to each employee row
- Button shows document count badge (e.g., "View 201 Files (5)")
- Clicking opens a modal with document list

**Button HTML**:
```html
<button onclick="view201Files('{employee_id}')" 
        class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg transition-all flex items-center">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    201 Files
    <span class="ml-2 px-2 py-0.5 bg-purple-800 rounded-full text-xs" id="doc-count-{employee_id}">0</span>
</button>
```

### 2. Admin View - 201 Files Modal

**Modal Features**:
- List all documents with icons based on file type
- Show document type, filename, size, upload date
- Download button for each document
- Delete button for each document (with confirmation)
- Verify checkbox for each document
- Filter by document type
- Sort by upload date or document type
- Empty state when no documents

**Modal HTML Structure**:
```html
<div id="files-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-white">201 Files - {Employee Name}</h3>
                <p class="text-purple-100 text-sm">Employee ID: {employee_id}</p>
            </div>
            <button onclick="closeFilesModal()" class="text-white hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Filters -->
        <div class="p-4 border-b border-slate-700 bg-slate-750">
            <div class="flex items-center space-x-4">
                <select id="filter-document-type" class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white">
                    <option value="">All Document Types</option>
                    <option value="Resume">Resume</option>
                    <option value="Birth Certificate">Birth Certificate</option>
                    <option value="TIN">TIN</option>
                    <!-- More options -->
                </select>
                <div class="flex-1"></div>
                <span class="text-slate-400 text-sm">Total: <span id="total-docs">0</span> files (<span id="total-size">0 MB</span>)</span>
            </div>
        </div>
        
        <!-- Document List -->
        <div class="p-6 overflow-y-auto max-h-[60vh]" id="documents-list">
            <!-- Documents will be loaded here -->
        </div>
    </div>
</div>
```

### 3. Employee View - Profile Page Enhancement

**Location**: `src/Views/employees/profile.php`

**Changes**:
- Add new section "My 201 Files" below employment information
- Upload button with file picker
- List of uploaded documents
- Download and delete buttons for each document

**Section HTML**:
```html
<div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="p-6 border-b border-slate-700 flex items-center justify-between">
        <div>
            <h4 class="text-xl font-semibold text-white">My 201 Files</h4>
            <p class="text-slate-400 text-sm mt-1">Upload and manage your employment documents</p>
        </div>
        <button onclick="openUploadModal()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg transition-all shadow-lg">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Upload Document
        </button>
    </div>
    <div class="p-6">
        <div id="my-documents-list" class="space-y-3">
            <!-- Documents will be loaded here -->
        </div>
        <div id="empty-state" class="text-center py-12 text-slate-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p>No documents uploaded yet</p>
            <p class="text-sm mt-2">Click "Upload Document" to add your 201 files</p>
        </div>
    </div>
</div>
```

### 4. Upload Modal

**Features**:
- File picker with drag-and-drop support
- Document type selector
- Notes field
- File size validation
- File type validation
- Upload progress bar
- Preview for images

## Backend Implementation

### 1. Model: `EmployeeDocument.php`

**Location**: `src/Models/EmployeeDocument.php`

**Methods**:
- `create($data)` - Create new document record
- `findByEmployeeId($employeeId)` - Get all documents for employee
- `findById($id)` - Get single document
- `update($id, $data)` - Update document (verify, notes)
- `delete($id)` - Delete document record
- `getTotalSize($employeeId)` - Get total storage used by employee
- `getDocumentCount($employeeId)` - Get document count per employee

### 2. Service: `DocumentService.php`

**Location**: `src/Services/DocumentService.php`

**Methods**:
- `uploadDocument($employeeId, $file, $documentType, $notes, $uploadedBy)` - Handle file upload
- `getEmployeeDocuments($employeeId)` - Get all documents with metadata
- `downloadDocument($documentId)` - Stream file for download
- `deleteDocument($documentId)` - Delete file and record
- `verifyDocument($documentId, $verifiedBy, $notes)` - Mark as verified
- `validateFile($file)` - Validate file size and type
- `generateFileName($employeeId, $originalName)` - Generate unique filename
- `getStoragePath($employeeId)` - Get storage directory path

### 3. Controller: `DocumentController.php`

**Location**: `src/Controllers/DocumentController.php`

**Methods**:
- `upload()` - POST /api/employees/{id}/documents
- `list()` - GET /api/employees/{id}/documents
- `download()` - GET /api/employees/{id}/documents/{docId}/download
- `delete()` - DELETE /api/employees/{id}/documents/{docId}
- `verify()` - PUT /api/employees/{id}/documents/{docId}/verify

## Security Considerations

### 1. File Upload Security
- Validate file extensions (whitelist: pdf, jpg, jpeg, png, doc, docx)
- Validate MIME types
- Scan for malicious content (if possible)
- Rename files to prevent directory traversal
- Store files outside web root or with .htaccess protection

### 2. Access Control
- Employees can only upload/view/delete their own documents
- Admins can view/delete any documents
- Use RLS policies in Supabase
- Verify user permissions in backend

### 3. File Storage
- Store files with unique names
- Use employee_id in path for organization
- Set proper file permissions (644 for files, 755 for directories)
- Implement file size limits per employee

## Implementation Steps

### Phase 1: Database Setup
1. Create `employee_documents` table
2. Set up RLS policies
3. Create indexes
4. Test with sample data

### Phase 2: Backend Development
1. Create `EmployeeDocument` model
2. Create `DocumentService` with file handling
3. Create `DocumentController` with all endpoints
4. Add routes to `config/routes.php`
5. Test API endpoints with Postman

### Phase 3: Admin UI
1. Add "View 201 Files" button to employee list
2. Create 201 files modal
3. Implement document list with filters
4. Add download functionality
5. Add delete functionality with confirmation
6. Add verify functionality
7. Test with different file types

### Phase 4: Employee UI
1. Add "My 201 Files" section to profile page
2. Create upload modal with file picker
3. Implement drag-and-drop upload
4. Add upload progress indicator
5. Display uploaded documents
6. Add download and delete functionality
7. Test upload limits and validations

### Phase 5: Testing & Polish
1. Test file upload/download/delete flows
2. Test access control (employee vs admin)
3. Test file size limits
4. Test file type validation
5. Test error handling
6. Add loading states
7. Add success/error notifications
8. Test on different browsers

## File Structure

```
HRIS/
├── public/
│   └── uploads/
│       └── 201files/
│           └── {employee_id}/
│               └── {filename}
├── src/
│   ├── Controllers/
│   │   └── DocumentController.php
│   ├── Models/
│   │   └── EmployeeDocument.php
│   ├── Services/
│   │   └── DocumentService.php
│   └── Views/
│       └── employees/
│           ├── list.php (add button)
│           └── profile.php (add section)
└── docs/
    └── migrations/
        └── create_employee_documents_table.sql
```

## Document Types

Standard 201 file document types:
1. Resume / CV
2. Birth Certificate
3. TIN ID
4. SSS ID
5. PhilHealth ID
6. Pag-IBIG ID
7. NBI Clearance
8. Police Clearance
9. Medical Certificate
10. Diploma
11. Transcript of Records
12. Certificate of Employment (previous)
13. Character Reference
14. Marriage Certificate (if applicable)
15. Other Documents

## Success Metrics

- Employees can upload documents within 2 minutes
- Admins can view employee documents in 1 click
- File upload success rate > 95%
- No security vulnerabilities in file handling
- Page load time < 2 seconds with 50 documents

## Future Enhancements

1. **Document Expiry Tracking** - Alert when documents expire (e.g., NBI clearance)
2. **Bulk Upload** - Upload multiple files at once
3. **Document Templates** - Provide downloadable templates
4. **OCR Integration** - Extract data from uploaded documents
5. **E-signature** - Sign documents digitally
6. **Document Versioning** - Keep history of document updates
7. **Audit Trail** - Track who viewed/downloaded documents
8. **Email Notifications** - Notify when documents are verified or need update
