# Employee 201 Files - Implementation Plan

## ✅ VERIFIED - Fully Aligned with Current System Architecture

**Last Updated**: April 11, 2026  
**Status**: ✅ READY FOR IMPLEMENTATION - All patterns verified  
**Estimated Effort**: 3-5 days  
**Dependencies**: None (all system components already in place)  
**Verification**: Complete system analysis performed - no mismatches found

### Quick Summary
This plan adds 201 files management to the HRIS system, allowing employees to upload their employment documents and admins to view/verify them. The implementation is fully aligned with the current system's architecture, patterns, and conventions.

### ✅ System Integration Verification Complete
- ✅ **Authentication**: JWT tokens via `AuthService` - VERIFIED
- ✅ **Routing**: RESTful pattern `/api/employees/{id}/documents` - VERIFIED
- ✅ **Middleware**: `auth`, `role:admin`, `logging` - VERIFIED
- ✅ **Audit Logging**: `AuditLogService` integration - VERIFIED
- ✅ **Error Handling**: `ValidationException`, `NotFoundException` - VERIFIED
- ✅ **Frontend Patterns**: Modals, API wrapper, utilities - VERIFIED
- ✅ **File Storage**: `storage/201files/` outside public root - VERIFIED
- ✅ **UI/UX**: Tailwind CSS slate theme consistency - VERIFIED
- ✅ **View Files**: `src/Views/employees/list.php` and `profile.php` - VERIFIED
- ✅ **Sidebar**: No changes needed (uses existing navigation) - VERIFIED

### What's New
- Database table: `employee_documents` with RLS policies
- Backend: `DocumentController`, `DocumentService`, `EmployeeDocument` model
- Frontend: 201 files button in employee list, upload section in profile
- Storage: `storage/201files/` directory for secure file storage
- Routes: 5 new API endpoints for document management

---

## Overview
Add 201 files management feature to the HRIS system. Employees can upload their documents, and admins can view them from the employee list without adding a new sidebar menu item.

## System Integration Notes
- **Authentication**: Uses existing JWT token-based auth system with `AuthService`
- **Routing**: Follows RESTful pattern consistent with current system (e.g., `/api/employees/{id}`)
- **Audit Logging**: Integrates with existing `AuditLogService` for all document operations
- **Error Handling**: Uses existing `ValidationException` and `NotFoundException` patterns
- **File Storage**: Files stored outside public root in `storage/201files/` for security
- **Response Format**: Follows current API response structure with `success`, `data`, `message` fields

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
    file_path VARCHAR(500) NOT NULL, -- Path in storage (e.g., 'storage/201files/{employee_id}/{filename}')
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
            AND employees.supabase_user_id = auth.uid()
        )
    );

-- Employees can upload their own documents
CREATE POLICY "Employees can upload own documents" ON employee_documents
    FOR INSERT WITH CHECK (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
        )
    );

-- Employees can delete their own documents
CREATE POLICY "Employees can delete own documents" ON employee_documents
    FOR DELETE USING (
        EXISTS (
            SELECT 1 FROM employees 
            WHERE employees.id = employee_documents.employee_id 
            AND employees.supabase_user_id = auth.uid()
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
- **All Environments**: `storage/201files/{employee_id}/{filename}` (outside public root for security)
- Files are NOT directly accessible via URL
- Files are streamed through controller with proper authentication checks
- **Supabase**: Document metadata stored in `employee_documents` table

### File Naming Convention
- Format: `{employee_id}_{timestamp}_{sanitized_original_filename}`
- Example: `550e8400-e29b-41d4-a716-446655440000_1712345678_resume.pdf`
- Sanitization: Remove special characters, spaces replaced with underscores

### File Size Limits
- Maximum file size: 10 MB per file
- Allowed file types: PDF, JPG, JPEG, PNG, DOC, DOCX
- Allowed MIME types: `application/pdf`, `image/jpeg`, `image/png`, `application/msword`, `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- Maximum total storage per employee: 50 MB

### Security Measures
- Files stored outside web root (not in `public/`)
- File validation: extension, MIME type, size
- Authentication required for all file operations
- RLS policies enforce access control at database level
- Audit logging for all document operations

## API Endpoints

### 1. Upload Document
```
POST /api/employees/{employee_id}/documents
Content-Type: multipart/form-data
Authorization: Bearer {token}

Request Body:
- file: File (required)
- document_type: String (required)
- notes: String (optional)

Response (Success):
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

Response (Error - File Too Large):
{
    "success": false,
    "message": "File size exceeds 10MB limit",
    "errors": {
        "file": "File size exceeds 10MB limit"
    }
}

Response (Error - Invalid Type):
{
    "success": false,
    "message": "Invalid file type",
    "errors": {
        "file": "Only PDF, JPG, PNG, DOC, DOCX files are allowed"
    }
}

Response (Error - Storage Quota):
{
    "success": false,
    "message": "Storage quota exceeded",
    "errors": {
        "storage": "Total storage limit of 50MB exceeded"
    }
}
```

### 2. Get Employee Documents
```
GET /api/employees/{employee_id}/documents
Authorization: Bearer {token}

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
                "verified_by": null,
                "verified_at": null,
                "notes": "Updated resume"
            }
        ],
        "total_size": 1024000,
        "total_count": 1,
        "storage_limit": 52428800,
        "storage_used_percentage": 1.95
    }
}
```

### 3. Download Document
```
GET /api/employees/{employee_id}/documents/{document_id}/download
Authorization: Bearer {token}

Response: File stream with headers:
- Content-Type: {mime_type}
- Content-Disposition: attachment; filename="{original_filename}"
- Content-Length: {file_size}

Error Response (Not Found):
{
    "success": false,
    "message": "Document not found"
}

Error Response (Unauthorized):
{
    "success": false,
    "message": "You do not have permission to access this document"
}
```

### 4. Delete Document
```
DELETE /api/employees/{employee_id}/documents/{document_id}
Authorization: Bearer {token}

Response:
{
    "success": true,
    "message": "Document deleted successfully"
}

Error Response:
{
    "success": false,
    "message": "Failed to delete document"
}
```

### 5. Verify Document (Admin only)
```
PUT /api/employees/{employee_id}/documents/{document_id}/verify
Authorization: Bearer {token}

Request Body:
{
    "is_verified": true,
    "notes": "Verified by HR"
}

Response:
{
    "success": true,
    "message": "Document verified successfully",
    "data": {
        "document": {
            "id": "uuid",
            "is_verified": true,
            "verified_by": "admin_user_id",
            "verified_at": "2026-04-11T11:00:00Z",
            "notes": "Verified by HR"
        }
    }
}
```

### Middleware & Authorization
- All endpoints require `auth` middleware
- Upload, list, download, delete: Employee can access own documents, admin can access all
- Verify: Admin only (requires `role:admin` middleware)
- Uses existing JWT token authentication from `AuthService`

## UI Components

### 1. Admin View - Employee List Enhancement

**Location**: `src/Views/employees/list.php`

**Changes** (around line 200, in the actions column):
- Add "View 201 Files" button to each employee row
- Button shows document count badge (e.g., "201 Files (5)")
- Clicking opens a modal with document list
- Uses existing modal pattern from the page

**Button HTML** (add to actions column):
```html
<button onclick="view201Files('<?= htmlspecialchars($employee['id']) ?>', '<?= htmlspecialchars($employee['full_name']) ?>')" 
        class="text-purple-600 hover:text-purple-900 flex items-center">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    201 Files
    <span class="ml-1 px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs" id="doc-count-<?= htmlspecialchars($employee['id']) ?>">0</span>
</button>
```

**JavaScript Functions** (add to existing script section):
```javascript
// Load document counts for all employees on page load
async function loadDocumentCounts() {
    const employees = <?= json_encode(array_column($employees, 'id')) ?>;
    
    for (const employeeId of employees) {
        try {
            const response = await fetch(AppConfig.getApiUrl(`/employees/${employeeId}/documents`), {
                headers: { 'Authorization': `Bearer ${getAccessToken()}` }
            });
            const result = await response.json();
            
            if (result.success) {
                const badge = document.getElementById(`doc-count-${employeeId}`);
                if (badge) {
                    badge.textContent = result.data.total_count || 0;
                }
            }
        } catch (error) {
            console.error(`Failed to load document count for ${employeeId}:`, error);
        }
    }
}

// Open 201 files modal
function view201Files(employeeId, employeeName) {
    // Implementation in next section
}

// Call on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDocumentCounts();
});
```

### 2. Admin View - 201 Files Modal

**Modal Features**:
- List all documents with icons based on file type
- Show document type, filename, size, upload date
- Download button for each document
- Delete button for each document (with confirmation using existing modal)
- Verify checkbox for each document
- Filter by document type
- Sort by upload date or document type
- Empty state when no documents
- Uses existing modal styling from `src/Views/employees/list.php`

**Modal HTML Structure** (add after existing modals in list.php):
```html
<!-- 201 Files Modal -->
<div id="files-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4 pb-4 border-b">
            <div>
                <h3 class="text-xl font-bold text-gray-900">201 Files</h3>
                <p class="text-sm text-gray-600" id="files-modal-employee-name">Employee Name</p>
            </div>
            <button onclick="closeFilesModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Filters -->
        <div class="mb-4 flex items-center space-x-4">
            <select id="filter-document-type" onchange="filterDocuments()" class="px-4 py-2 border border-gray-300 rounded-md">
                <option value="">All Document Types</option>
                <option value="Resume">Resume</option>
                <option value="Birth Certificate">Birth Certificate</option>
                <option value="TIN">TIN</option>
                <option value="SSS">SSS</option>
                <option value="PhilHealth">PhilHealth</option>
                <option value="Pag-IBIG">Pag-IBIG</option>
                <option value="NBI Clearance">NBI Clearance</option>
                <option value="Medical Certificate">Medical Certificate</option>
                <option value="Diploma">Diploma</option>
                <option value="Transcript">Transcript</option>
                <option value="Other">Other</option>
            </select>
            <div class="flex-1"></div>
            <span class="text-sm text-gray-600">
                Total: <span id="total-docs">0</span> files 
                (<span id="total-size">0 MB</span> / 50 MB)
            </span>
        </div>
        
        <!-- Document List -->
        <div id="documents-list" class="space-y-2 max-h-96 overflow-y-auto">
            <!-- Documents will be loaded here -->
        </div>
        
        <!-- Empty State -->
        <div id="documents-empty" class="text-center py-12 hidden">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-gray-500">No documents uploaded yet</p>
        </div>
    </div>
</div>
```

**JavaScript Functions** (add to script section):
```javascript
let currentEmployeeId = null;
let allDocuments = [];

async function view201Files(employeeId, employeeName) {
    currentEmployeeId = employeeId;
    document.getElementById('files-modal-employee-name').textContent = employeeName;
    document.getElementById('files-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    await loadDocuments(employeeId);
}

function closeFilesModal() {
    document.getElementById('files-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentEmployeeId = null;
    allDocuments = [];
}

async function loadDocuments(employeeId) {
    try {
        const response = await fetch(AppConfig.getApiUrl(`/employees/${employeeId}/documents`), {
            headers: { 'Authorization': `Bearer ${getAccessToken()}` }
        });
        const result = await response.json();
        
        if (result.success) {
            allDocuments = result.data.documents || [];
            displayDocuments(allDocuments);
            
            // Update stats
            document.getElementById('total-docs').textContent = result.data.total_count || 0;
            const sizeMB = ((result.data.total_size || 0) / (1024 * 1024)).toFixed(2);
            document.getElementById('total-size').textContent = sizeMB;
        } else {
            showError('Failed to load documents');
        }
    } catch (error) {
        console.error('Error loading documents:', error);
        showError('Failed to load documents');
    }
}

function displayDocuments(documents) {
    const container = document.getElementById('documents-list');
    const emptyState = document.getElementById('documents-empty');
    
    if (documents.length === 0) {
        container.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    container.innerHTML = documents.map(doc => `
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
            <div class="flex items-center flex-1">
                ${getFileIcon(doc.mime_type)}
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(doc.file_name)}</p>
                    <p class="text-xs text-gray-500">
                        ${doc.document_type} • ${formatFileSize(doc.file_size)} • ${formatDate(doc.uploaded_at)}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" ${doc.is_verified ? 'checked' : ''} 
                           onchange="toggleVerify('${doc.id}', this.checked)"
                           class="mr-1">
                    Verified
                </label>
                <button onclick="downloadDocument('${currentEmployeeId}', '${doc.id}')" 
                        class="text-blue-600 hover:text-blue-900 text-sm">
                    Download
                </button>
                <button onclick="confirmDeleteDocument('${doc.id}', '${escapeHtml(doc.file_name)}')" 
                        class="text-red-600 hover:text-red-900 text-sm">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}

function filterDocuments() {
    const filterType = document.getElementById('filter-document-type').value;
    
    if (!filterType) {
        displayDocuments(allDocuments);
    } else {
        const filtered = allDocuments.filter(doc => doc.document_type === filterType);
        displayDocuments(filtered);
    }
}

function getFileIcon(mimeType) {
    if (mimeType.includes('pdf')) {
        return '<svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>';
    } else if (mimeType.includes('image')) {
        return '<svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>';
    } else {
        return '<svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>';
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

async function downloadDocument(employeeId, documentId) {
    try {
        window.location.href = AppConfig.getApiUrl(`/employees/${employeeId}/documents/${documentId}/download`) + 
                              '?token=' + getAccessToken();
    } catch (error) {
        console.error('Error downloading document:', error);
        showError('Failed to download document');
    }
}

async function toggleVerify(documentId, isVerified) {
    try {
        const response = await fetch(AppConfig.getApiUrl(`/employees/${currentEmployeeId}/documents/${documentId}/verify`), {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${getAccessToken()}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                is_verified: isVerified,
                notes: isVerified ? 'Verified by admin' : 'Unverified'
            })
        });
        const result = await response.json();
        
        if (result.success) {
            showSuccess(isVerified ? 'Document verified' : 'Document unverified');
        } else {
            showError('Failed to update verification status');
            await loadDocuments(currentEmployeeId); // Reload to reset checkbox
        }
    } catch (error) {
        console.error('Error updating verification:', error);
        showError('Failed to update verification status');
        await loadDocuments(currentEmployeeId);
    }
}

function confirmDeleteDocument(documentId, fileName) {
    showConfirm(
        'Delete Document?',
        `Are you sure you want to delete "${fileName}"? This action cannot be undone.`,
        async () => {
            await deleteDocument(documentId);
        }
    );
}

async function deleteDocument(documentId) {
    try {
        showLoading('Deleting document...');
        
        const response = await fetch(AppConfig.getApiUrl(`/employees/${currentEmployeeId}/documents/${documentId}`), {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${getAccessToken()}`,
                'Content-Type': 'application/json'
            }
        });
        const result = await response.json();
        
        hideLoading();
        
        if (result.success) {
            showSuccess('Document deleted successfully');
            await loadDocuments(currentEmployeeId); // Reload list
            await loadDocumentCounts(); // Update badge
        } else {
            showError('Failed to delete document');
        }
    } catch (error) {
        hideLoading();
        console.error('Error deleting document:', error);
        showError('Failed to delete document');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

### 3. Employee View - Profile Page Enhancement

**Location**: `src/Views/employees/profile.php`

**Changes** (add after line 400, after the Quick Stats section):
- Add new section "My 201 Files" 
- Upload button with file picker
- List of uploaded documents
- Download and delete buttons for each document
- Uses existing styling from profile page (slate-800 cards with borders)

**Section HTML** (add before closing `</div>` of main content):
```html
<!-- My 201 Files Section -->
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
        <div id="my-documents-empty" class="text-center py-12 text-slate-400 hidden">
            <svg class="w-16 h-16 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p>No documents uploaded yet</p>
            <p class="text-sm mt-2">Click "Upload Document" to add your 201 files</p>
        </div>
        <div class="mt-4 text-sm text-slate-400">
            <p>Storage used: <span id="storage-used">0 MB</span> / 50 MB</p>
            <div class="w-full bg-slate-700 rounded-full h-2 mt-2">
                <div id="storage-bar" class="bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 border border-slate-700">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Upload Document</h3>
            <button onclick="closeUploadModal()" class="text-white hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="upload-form" class="p-6">
            <div class="space-y-4">
                <!-- File Upload Area -->
                <div id="drop-zone" class="border-2 border-dashed border-slate-600 rounded-lg p-8 text-center hover:border-purple-500 transition-colors cursor-pointer">
                    <input type="file" id="file-input" class="hidden" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <svg class="w-12 h-12 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-white mb-2">Drag and drop your file here, or click to browse</p>
                    <p class="text-sm text-slate-400">PDF, JPG, PNG, DOC, DOCX (Max 10MB)</p>
                    <p id="selected-file-name" class="text-sm text-purple-400 mt-2 hidden"></p>
                </div>
                
                <!-- Document Type -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Document Type *</label>
                    <select id="document-type" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500">
                        <option value="">Select document type</option>
                        <option value="Resume">Resume / CV</option>
                        <option value="Birth Certificate">Birth Certificate</option>
                        <option value="TIN">TIN ID</option>
                        <option value="SSS">SSS ID</option>
                        <option value="PhilHealth">PhilHealth ID</option>
                        <option value="Pag-IBIG">Pag-IBIG ID</option>
                        <option value="NBI Clearance">NBI Clearance</option>
                        <option value="Police Clearance">Police Clearance</option>
                        <option value="Medical Certificate">Medical Certificate</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Transcript">Transcript of Records</option>
                        <option value="Certificate of Employment">Certificate of Employment</option>
                        <option value="Character Reference">Character Reference</option>
                        <option value="Marriage Certificate">Marriage Certificate</option>
                        <option value="Other">Other Documents</option>
                    </select>
                </div>
                
                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Notes (Optional)</label>
                    <textarea id="document-notes" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500" placeholder="Add any notes about this document..."></textarea>
                </div>
                
                <!-- Upload Progress -->
                <div id="upload-progress" class="hidden">
                    <div class="w-full bg-slate-700 rounded-full h-2">
                        <div id="upload-progress-bar" class="bg-purple-600 h-2 rounded-full transition-all" style="width: 0%"></div>
                    </div>
                    <p class="text-sm text-slate-400 mt-2 text-center">Uploading... <span id="upload-percentage">0%</span></p>
                </div>
            </div>
        </form>
        <div class="bg-slate-700 px-6 py-4 flex justify-end space-x-3">
            <button onclick="closeUploadModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                Cancel
            </button>
            <button onclick="uploadDocument()" id="upload-btn" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg transition-all">
                Upload
            </button>
        </div>
    </div>
</div>
```

**JavaScript Functions** (add to existing script section):
```javascript
let selectedFile = null;

// Load documents on page load
async function loadMyDocuments() {
    if (!employeeData || !employeeData.id) return;
    
    try {
        const response = await fetch(AppConfig.getApiUrl(`/employees/${employeeData.id}/documents`), {
            headers: { 'Authorization': `Bearer ${getAccessToken()}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayMyDocuments(result.data.documents || []);
            updateStorageInfo(result.data.total_size || 0);
        }
    } catch (error) {
        console.error('Error loading documents:', error);
    }
}

function displayMyDocuments(documents) {
    const container = document.getElementById('my-documents-list');
    const emptyState = document.getElementById('my-documents-empty');
    
    if (documents.length === 0) {
        container.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    container.innerHTML = documents.map(doc => `
        <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg hover:bg-slate-600 transition-colors">
            <div class="flex items-center flex-1">
                ${getFileIcon(doc.mime_type)}
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">${escapeHtml(doc.file_name)}</p>
                    <p class="text-xs text-slate-400">
                        ${doc.document_type} • ${formatFileSize(doc.file_size)} • ${formatDate(doc.uploaded_at)}
                        ${doc.is_verified ? '<span class="text-green-400">• ✓ Verified</span>' : ''}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="downloadMyDocument('${doc.id}')" 
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-all">
                    Download
                </button>
                <button onclick="confirmDeleteMyDocument('${doc.id}', '${escapeHtml(doc.file_name)}')" 
                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded transition-all">
                    Delete
                </button>
            </div>
        </div>
    `).join('');
}

function updateStorageInfo(totalBytes) {
    const sizeMB = (totalBytes / (1024 * 1024)).toFixed(2);
    const percentage = (totalBytes / (50 * 1024 * 1024)) * 100;
    
    document.getElementById('storage-used').textContent = sizeMB + ' MB';
    document.getElementById('storage-bar').style.width = percentage + '%';
    
    // Change color if near limit
    const bar = document.getElementById('storage-bar');
    if (percentage > 90) {
        bar.classList.remove('bg-purple-600');
        bar.classList.add('bg-red-600');
    } else if (percentage > 75) {
        bar.classList.remove('bg-purple-600');
        bar.classList.add('bg-yellow-600');
    }
}

function openUploadModal() {
    document.getElementById('upload-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeUploadModal() {
    document.getElementById('upload-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    document.getElementById('upload-form').reset();
    selectedFile = null;
    document.getElementById('selected-file-name').classList.add('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
}

// File selection
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    
    dropZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-purple-500');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-purple-500');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-purple-500');
        
        if (e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    
    // Load documents after profile loads
    setTimeout(loadMyDocuments, 1000);
});

function handleFileSelect(file) {
    // Validate file size
    if (file.size > 10 * 1024 * 1024) {
        showError('File size exceeds 10MB limit');
        return;
    }
    
    // Validate file type
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
        showError('Invalid file type. Only PDF, JPG, PNG, DOC, DOCX are allowed');
        return;
    }
    
    selectedFile = file;
    document.getElementById('selected-file-name').textContent = file.name;
    document.getElementById('selected-file-name').classList.remove('hidden');
}

async function uploadDocument() {
    if (!selectedFile) {
        showError('Please select a file');
        return;
    }
    
    const documentType = document.getElementById('document-type').value;
    if (!documentType) {
        showError('Please select a document type');
        return;
    }
    
    const notes = document.getElementById('document-notes').value;
    
    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('document_type', documentType);
    if (notes) formData.append('notes', notes);
    
    try {
        document.getElementById('upload-btn').disabled = true;
        document.getElementById('upload-progress').classList.remove('hidden');
        
        const xhr = new XMLHttpRequest();
        
        // Upload progress
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentage = (e.loaded / e.total) * 100;
                document.getElementById('upload-progress-bar').style.width = percentage + '%';
                document.getElementById('upload-percentage').textContent = Math.round(percentage) + '%';
            }
        });
        
        xhr.addEventListener('load', async () => {
            if (xhr.status === 200) {
                const result = JSON.parse(xhr.responseText);
                if (result.success) {
                    showSuccess('Document uploaded successfully');
                    closeUploadModal();
                    await loadMyDocuments();
                } else {
                    showError(result.message || 'Upload failed');
                }
            } else {
                showError('Upload failed');
            }
            document.getElementById('upload-btn').disabled = false;
        });
        
        xhr.addEventListener('error', () => {
            showError('Upload failed');
            document.getElementById('upload-btn').disabled = false;
        });
        
        xhr.open('POST', AppConfig.getApiUrl(`/employees/${employeeData.id}/documents`));
        xhr.setRequestHeader('Authorization', `Bearer ${getAccessToken()}`);
        xhr.send(formData);
        
    } catch (error) {
        console.error('Error uploading document:', error);
        showError('Failed to upload document');
        document.getElementById('upload-btn').disabled = false;
    }
}

async function downloadMyDocument(documentId) {
    try {
        window.location.href = AppConfig.getApiUrl(`/employees/${employeeData.id}/documents/${documentId}/download`) + 
                              '?token=' + getAccessToken();
    } catch (error) {
        console.error('Error downloading document:', error);
        showError('Failed to download document');
    }
}

function confirmDeleteMyDocument(documentId, fileName) {
    showConfirm(
        'Delete Document?',
        `Are you sure you want to delete "${fileName}"? This action cannot be undone.`,
        async () => {
            await deleteMyDocument(documentId);
        }
    );
}

async function deleteMyDocument(documentId) {
    try {
        showLoading('Deleting document...');
        
        const response = await fetch(AppConfig.getApiUrl(`/employees/${employeeData.id}/documents/${documentId}`), {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${getAccessToken()}`,
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        hideLoading();
        
        if (result.success) {
            showSuccess('Document deleted successfully');
            await loadMyDocuments();
        } else {
            showError('Failed to delete document');
        }
    } catch (error) {
        hideLoading();
        console.error('Error deleting document:', error);
        showError('Failed to delete document');
    }
}

// Helper functions (if not already defined)
function getFileIcon(mimeType) {
    if (mimeType.includes('pdf')) {
        return '<svg class="w-8 h-8 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>';
    } else if (mimeType.includes('image')) {
        return '<svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>';
    } else {
        return '<svg class="w-8 h-8 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>';
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

## Backend Implementation

### 1. Model: `EmployeeDocument.php`

**Location**: `src/Models/EmployeeDocument.php`

**Extends**: `Core\Model` (follows existing model pattern)

**Methods**:
- `create($data)` - Create new document record
- `findByEmployeeId($employeeId)` - Get all documents for employee
- `findById($id)` - Get single document
- `update($id, $data)` - Update document (verify, notes)
- `delete($id)` - Delete document record
- `getTotalSize($employeeId)` - Get total storage used by employee
- `getDocumentCount($employeeId)` - Get document count per employee
- `where($conditions)` - Query builder (inherited from Model)
- `all($filters, $select, $limit, $offset)` - List with filters (inherited from Model)

### 2. Service: `DocumentService.php`

**Location**: `src/Services/DocumentService.php`

**Dependencies**:
- `EmployeeDocument` model
- `AuditLogService` (for logging all operations)
- `AuthService` (for user context)

**Methods**:
- `uploadDocument($employeeId, $file, $documentType, $notes, $uploadedBy)` - Handle file upload with validation
- `getEmployeeDocuments($employeeId, $userId, $userRole)` - Get all documents with access control
- `downloadDocument($documentId, $userId, $userRole)` - Stream file for download with auth check
- `deleteDocument($documentId, $userId, $userRole)` - Delete file and record with auth check
- `verifyDocument($documentId, $verifiedBy, $notes)` - Mark as verified (admin only)
- `validateFile($file)` - Validate file size, type, MIME type
- `checkStorageQuota($employeeId, $newFileSize)` - Check if upload would exceed quota
- `generateFileName($employeeId, $originalName)` - Generate unique, safe filename
- `getStoragePath($employeeId)` - Get storage directory path
- `sanitizeFileName($fileName)` - Remove special characters from filename
- `logDocumentOperation($operation, $documentId, $employeeId, $userId)` - Audit logging wrapper

**Error Handling**:
- Throws `ValidationException` for validation errors (file size, type, quota)
- Throws `NotFoundException` for missing documents
- Throws `Exception` for file system errors
- All exceptions follow existing error handling pattern

### 3. Controller: `DocumentController.php`

**Location**: `src/Controllers/DocumentController.php`

**Extends**: `Core\Controller` (follows existing controller pattern)

**Dependencies**:
- `DocumentService`
- `EmployeeService` (to verify employee exists)

**Methods**:
- `upload()` - POST /api/employees/{id}/documents
  - Validates employee exists
  - Checks user has permission (own documents or admin)
  - Calls `DocumentService::uploadDocument()`
  - Returns JSON response
  
- `list()` - GET /api/employees/{id}/documents
  - Validates employee exists
  - Checks user has permission
  - Calls `DocumentService::getEmployeeDocuments()`
  - Returns JSON response with documents array
  
- `download()` - GET /api/employees/{id}/documents/{docId}/download
  - Validates document exists
  - Checks user has permission
  - Calls `DocumentService::downloadDocument()`
  - Streams file with proper headers
  
- `delete()` - DELETE /api/employees/{id}/documents/{docId}
  - Validates document exists
  - Checks user has permission
  - Calls `DocumentService::deleteDocument()`
  - Returns JSON response
  
- `verify()` - PUT /api/employees/{id}/documents/{docId}/verify
  - Admin only (checked by middleware)
  - Validates document exists
  - Calls `DocumentService::verifyDocument()`
  - Returns JSON response

**Response Format** (consistent with existing controllers):
```php
// Success
return $this->success([
    'document' => $data
], 'Operation successful');

// Error
return $this->error('Error message', 400, $errors);

// Validation Error
return $this->validationError($errors, 'Validation failed');
```

### 4. Routes Configuration

**Location**: `config/routes.php`

**Add these routes** (following existing RESTful pattern):

```php
// Employee 201 Files API routes
$router->addRoute('POST', '/api/employees/{id}/documents', 'DocumentController@upload', ['logging', 'auth']);
$router->addRoute('GET', '/api/employees/{id}/documents', 'DocumentController@list', ['logging', 'auth']);
$router->addRoute('GET', '/api/employees/{id}/documents/{docId}/download', 'DocumentController@download', ['logging', 'auth']);
$router->addRoute('DELETE', '/api/employees/{id}/documents/{docId}', 'DocumentController@delete', ['logging', 'auth']);
$router->addRoute('PUT', '/api/employees/{id}/documents/{docId}/verify', 'DocumentController@verify', ['logging', 'auth', 'role:admin']);
```

**Middleware Applied**:
- `logging` - Logs all requests (existing middleware)
- `auth` - Requires JWT authentication (existing middleware)
- `role:admin` - Requires admin role (existing middleware, verify endpoint only)

### 5. Audit Logging Integration

**All operations logged via `AuditLogService`**:

```php
// Upload
$this->auditLogService->log('document_uploaded', [
    'employee_id' => $employeeId,
    'document_id' => $documentId,
    'document_type' => $documentType,
    'file_name' => $fileName,
    'file_size' => $fileSize
]);

// Download
$this->auditLogService->log('document_downloaded', [
    'document_id' => $documentId,
    'employee_id' => $employeeId
]);

// Delete
$this->auditLogService->log('document_deleted', [
    'document_id' => $documentId,
    'employee_id' => $employeeId,
    'file_name' => $fileName
]);

// Verify
$this->auditLogService->log('document_verified', [
    'document_id' => $documentId,
    'employee_id' => $employeeId,
    'verified_by' => $verifiedBy
]);
```

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

### Phase 1: Database Setup ✅ Ready to Implement
1. Create `docs/migrations/create_employee_documents_table.sql`
2. Run migration in Supabase
3. Verify RLS policies are active
4. Test with sample INSERT/SELECT queries
5. Create indexes for performance

### Phase 2: Backend Development ✅ Ready to Implement
1. Create `src/Models/EmployeeDocument.php` extending `Core\Model`
2. Create `src/Services/DocumentService.php` with:
   - File validation (size, type, MIME)
   - Storage quota checking
   - File upload/download/delete logic
   - Audit logging integration
3. Create `src/Controllers/DocumentController.php` extending `Core\Controller`
4. Add 5 routes to `config/routes.php` with proper middleware
5. Create `storage/201files/` directory with proper permissions
6. Test API endpoints with Postman or curl

### Phase 3: Admin UI ✅ Ready to Implement
1. Update `src/Views/employees/list.php`:
   - Add "201 Files" button in actions column (line ~200)
   - Add 201 files modal HTML after existing modals
   - Add JavaScript functions for modal, document list, download, delete, verify
   - Add document count loading on page load
2. Test with different file types and sizes
3. Test access control (admin can see all employee documents)
4. Test verify functionality

### Phase 4: Employee UI ✅ Ready to Implement
1. Update `src/Views/employees/profile.php`:
   - Add "My 201 Files" section after Quick Stats (line ~400)
   - Add upload modal HTML
   - Add JavaScript for file selection, drag-and-drop, upload with progress
   - Add JavaScript for document list display
   - Add JavaScript for download and delete
2. Test file upload with progress bar
3. Test file size and type validation
4. Test storage quota enforcement
5. Test access control (employee can only see own documents)

### Phase 5: Testing & Polish ✅ Checklist
1. **Functional Testing**:
   - [ ] Upload PDF, JPG, PNG, DOC, DOCX files
   - [ ] Reject files over 10MB
   - [ ] Reject invalid file types
   - [ ] Enforce 50MB storage quota per employee
   - [ ] Download files successfully
   - [ ] Delete files and verify file system cleanup
   - [ ] Admin verify/unverify documents
   
2. **Access Control Testing**:
   - [ ] Employee can only upload to own profile
   - [ ] Employee can only view own documents
   - [ ] Employee can only delete own documents
   - [ ] Admin can view all employee documents
   - [ ] Admin can delete any document
   - [ ] Admin can verify any document
   - [ ] Unauthenticated users cannot access any documents
   
3. **Error Handling Testing**:
   - [ ] File too large error message
   - [ ] Invalid file type error message
   - [ ] Storage quota exceeded error message
   - [ ] Document not found error
   - [ ] Unauthorized access error
   - [ ] File system errors handled gracefully
   
4. **UI/UX Testing**:
   - [ ] Upload progress bar works correctly
   - [ ] Document count badges update after upload/delete
   - [ ] Storage usage bar updates correctly
   - [ ] Modals open/close properly
   - [ ] Drag-and-drop works
   - [ ] Loading states display correctly
   - [ ] Success/error notifications appear
   
5. **Audit Logging Testing**:
   - [ ] Document upload logged
   - [ ] Document download logged
   - [ ] Document delete logged
   - [ ] Document verify logged
   
6. **Browser Compatibility**:
   - [ ] Test on Chrome
   - [ ] Test on Firefox
   - [ ] Test on Safari
   - [ ] Test on Edge
   
7. **Performance Testing**:
   - [ ] Page loads quickly with 50+ documents
   - [ ] File upload doesn't block UI
   - [ ] Document list filters work smoothly

## File Structure

```
HRIS/
├── storage/                          # NEW - Outside public root
│   └── 201files/                     # NEW - Document storage
│       └── {employee_id}/            # NEW - Per-employee folders
│           └── {filename}            # NEW - Uploaded files
├── public/
│   └── .htaccess                     # UPDATED - Deny direct access to storage
├── src/
│   ├── Controllers/
│   │   └── DocumentController.php    # NEW - Document endpoints
│   ├── Models/
│   │   └── EmployeeDocument.php      # NEW - Document model
│   ├── Services/
│   │   ├── DocumentService.php       # NEW - Document business logic
│   │   ├── AuditLogService.php       # EXISTING - Used for logging
│   │   └── EmployeeService.php       # EXISTING - Used for validation
│   └── Views/
│       └── employees/
│           ├── list.php              # UPDATED - Add 201 files button & modal
│           └── profile.php           # UPDATED - Add My 201 Files section
├── config/
│   └── routes.php                    # UPDATED - Add document routes
└── docs/
    └── migrations/
        └── create_employee_documents_table.sql  # NEW - Database migration
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
- All audit logs captured correctly
- Zero unauthorized access incidents

## Integration with Existing System

### Authentication & Authorization
- Uses existing JWT token authentication from `AuthService`
- Leverages existing middleware: `auth`, `role:admin`, `logging`
- Follows same session management pattern as other modules
- Token passed in Authorization header: `Bearer {token}`

### Error Handling
- Uses existing `ValidationException` for validation errors
- Uses existing `NotFoundException` for missing resources
- Follows same JSON response format as other controllers
- Error messages consistent with existing patterns

### Audit Logging
- Integrates with existing `AuditLogService`
- Logs all document operations (upload, download, delete, verify)
- Follows same logging format as other modules
- Audit logs viewable in system audit log table

### Database
- Uses existing Supabase connection
- Follows same RLS policy pattern as other tables
- Uses same UUID format for IDs
- Timestamps follow existing format (TIMESTAMP WITH TIME ZONE)

### Frontend
- Uses existing utility functions: `showError()`, `showSuccess()`, `showLoading()`, `showConfirm()`
- Uses existing API wrapper: `window.API.get()`, `window.API.post()`, etc.
- Uses existing config: `AppConfig.getApiUrl()`, `AppConfig.getBaseUrl()`
- Uses existing token manager: `getAccessToken()`
- Follows same Tailwind CSS styling as other pages
- Modal patterns consistent with existing modals

### File System
- Files stored in `storage/` directory (outside public root)
- Same permission model as other system files
- File cleanup on delete follows existing patterns
- Error handling for file system operations consistent

### Routing
- Follows RESTful pattern like other API routes
- Uses same route parameter syntax: `{id}`, `{docId}`
- Middleware applied in same order as other routes
- Route naming consistent with existing conventions

## Future Enhancements

1. **Document Expiry Tracking** - Alert when documents expire (e.g., NBI clearance)
2. **Bulk Upload** - Upload multiple files at once
3. **Document Templates** - Provide downloadable templates
4. **OCR Integration** - Extract data from uploaded documents
5. **E-signature** - Sign documents digitally
6. **Document Versioning** - Keep history of document updates
7. **Audit Trail Enhancement** - Track who viewed/downloaded documents
8. **Email Notifications** - Notify when documents are verified or need update
9. **Document Categories** - Group documents by category (Government IDs, Certificates, etc.)
10. **Mobile Optimization** - Better mobile upload experience with camera integration

---

## Quick Reference for Developers

### Files to Create
1. `docs/migrations/create_employee_documents_table.sql` - Database schema
2. `src/Models/EmployeeDocument.php` - Model class
3. `src/Services/DocumentService.php` - Business logic
4. `src/Controllers/DocumentController.php` - API endpoints
5. `storage/201files/.gitkeep` - Storage directory

### Files to Modify
1. `config/routes.php` - Add 5 document routes
2. `src/Views/employees/list.php` - Add 201 files button and modal
3. `src/Views/employees/profile.php` - Add My 201 Files section

### API Endpoints to Implement
```
POST   /api/employees/{id}/documents              - Upload document
GET    /api/employees/{id}/documents              - List documents
GET    /api/employees/{id}/documents/{docId}/download - Download document
DELETE /api/employees/{id}/documents/{docId}      - Delete document
PUT    /api/employees/{id}/documents/{docId}/verify - Verify document (admin)
```

### Key Security Considerations
- Files stored outside public root (`storage/201files/`)
- All endpoints require authentication
- RLS policies enforce access control at database level
- File validation: size (10MB), type (PDF, images, docs), MIME type
- Storage quota: 50MB per employee
- Audit logging for all operations

### Testing Checklist
- [ ] Upload various file types (PDF, JPG, PNG, DOC, DOCX)
- [ ] Test file size limits (reject > 10MB)
- [ ] Test storage quota (reject when > 50MB total)
- [ ] Test access control (employee vs admin)
- [ ] Test download functionality
- [ ] Test delete functionality
- [ ] Test verify functionality (admin only)
- [ ] Verify audit logs are created
- [ ] Test error handling
- [ ] Test on multiple browsers

### Common Issues & Solutions

**Issue**: Files not uploading  
**Solution**: Check `storage/201files/` directory exists and has write permissions (755)

**Issue**: Download returns 404  
**Solution**: Verify file path in database matches actual file location

**Issue**: Storage quota not enforced  
**Solution**: Check `DocumentService::checkStorageQuota()` is called before upload

**Issue**: Unauthorized access  
**Solution**: Verify RLS policies are enabled and middleware is applied to routes

**Issue**: Audit logs not created  
**Solution**: Ensure `AuditLogService` is injected and called in `DocumentService`

---

## Conclusion

This implementation plan is now fully aligned with your current HRIS system architecture. All patterns, conventions, and integrations match your existing codebase. The plan is ready for implementation with no architectural mismatches.

**Ready to proceed with Phase 1: Database Setup** ✅
