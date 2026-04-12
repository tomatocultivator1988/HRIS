<?php

namespace Controllers;

use Core\Controller;
use Core\Container;
use Core\Response;
use Services\DocumentService;
use Models\EmployeeDocument;
use Models\Employee;
use Services\AuditLogService;

/**
 * DocumentController
 * 
 * Handles HTTP requests for employee 201 files management.
 * Provides endpoints for upload, list, download, delete, and verify operations.
 * 
 * @package App\Controllers
 */
class DocumentController extends Controller
{
    private DocumentService $documentService;
    private EmployeeDocument $employeeDocument;
    private Employee $employeeModel;
    private AuditLogService $auditLogService;

    /**
     * Constructor
     *
     * @param Container $container Dependency injection container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->documentService = $container->resolve(DocumentService::class);
        $this->employeeDocument = $container->resolve(EmployeeDocument::class);
        $this->employeeModel = $container->resolve(Employee::class);
        $this->auditLogService = $container->resolve(AuditLogService::class);
    }

    /**
     * Upload a document
     * 
     * POST /api/employees/{employeeId}/documents
     *
     * @return Response JSON response
     */
    public function upload(): Response
    {
        try {
            // Get employee ID from route
            $employeeId = $this->getRouteParam('employeeId');
            
            // Verify employee exists
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }

            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            // Check access: employee can upload own docs (user['id'] is employee ID), admin can upload any
            $isOwner = $user['id'] === $employeeId;
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            if (!$isOwner && !$isAdmin) {
                return $this->error('You do not have permission to upload documents for this employee', 403);
            }

            // Check if file was uploaded
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return $this->validationError(['file' => 'No file was uploaded or upload error occurred']);
            }

            // Get document type and notes
            $documentType = $this->getPostData('document_type');
            $notes = $this->getPostData('notes', '');

            if (empty($documentType)) {
                return $this->validationError(['document_type' => 'Document type is required']);
            }

            // Validate file
            $validation = $this->documentService->validateFile($_FILES['file']);
            if (!$validation['valid']) {
                return $this->validationError($validation['errors']);
            }

            // Check storage quota
            if (!$this->documentService->checkStorageQuota($employeeId, $_FILES['file']['size'])) {
                return $this->validationError([
                    'storage' => 'Storage quota exceeded. Maximum 50MB per employee'
                ]);
            }

            // Store file
            $fileData = $this->documentService->storeFile($_FILES['file'], $employeeId);

            // Create database record
            try {
                // Determine uploaded_by value
                // For employees: use their supabase_user_id if available
                // For admins: use admin's supabase_user_id if available
                // Fallback: use a placeholder UUID or null
                $uploadedBy = null;
                
                if ($isAdmin) {
                    // Admin uploading - try to get admin's supabase_user_id
                    // In this system, admins might not have supabase_user_id in the user object
                    // So we'll use a placeholder or the employee's supabase_user_id
                    $uploadedBy = $employee['supabase_user_id'] ?? null;
                } else {
                    // Employee uploading their own document
                    $uploadedBy = $employee['supabase_user_id'] ?? null;
                }
                
                $documentData = [
                    'employee_id' => $employeeId,
                    'document_type' => $documentType,
                    'file_name' => $_FILES['file']['name'],
                    'file_path' => $fileData['path'],
                    'file_size' => $_FILES['file']['size'],
                    'mime_type' => $_FILES['file']['type'],
                    'notes' => $notes
                ];
                
                // Only add uploaded_by if we have a value
                if ($uploadedBy !== null) {
                    $documentData['uploaded_by'] = $uploadedBy;
                }
                
                // Debug: Log what we're trying to insert
                error_log("DocumentController: About to create document with data: " . json_encode($documentData));
                
                // Bypass Model's create method and use Supabase directly
                $supabase = $this->container->resolve('SupabaseConnection');
                $result = $supabase->insert('employee_documents', $documentData);
                
                // Supabase insert returns an array, we need the first element
                if (is_array($result) && isset($result['id'])) {
                    // Result is already the document object
                    $document = $result;
                } else {
                    throw new \Exception('Failed to create document record');
                }

                // Log the upload
                $this->auditLogService->log('document_upload', [
                    'employee_id' => $employeeId,
                    'document_id' => $document['id'],
                    'document_type' => $documentType,
                    'file_name' => $_FILES['file']['name'],
                    'file_size' => $_FILES['file']['size']
                ], $user['id'], $user['role'] ?? null);

                return $this->success([
                    'document' => $document
                ], 'Document uploaded successfully');

            } catch (\Exception $e) {
                // Rollback: delete file if database insert fails
                $this->documentService->deleteFile($fileData['path']);
                throw $e;
            }

        } catch (\Exception $e) {
            error_log("Document upload error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->error('Failed to upload document: ' . $e->getMessage(), 500);
        }
    }

    /**
     * List all documents for an employee
     * 
     * GET /api/employees/{employeeId}/documents
     *
     * @return Response JSON response
     */
    public function list(): Response
    {
        try {
            // Get employee ID from route
            $employeeId = $this->getRouteParam('employeeId');
            
            // Verify employee exists
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }

            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            // Check access: employee can view own docs (user['id'] is employee ID), admin can view any
            $isOwner = $user['id'] === $employeeId;
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            if (!$isOwner && !$isAdmin) {
                return $this->error('You do not have permission to view documents for this employee', 403);
            }

            // Retrieve documents
            $documents = $this->employeeDocument->findByEmployeeId($employeeId);

            // Calculate storage statistics
            $storageStats = $this->documentService->getStorageStats($employeeId);

            return $this->success([
                'documents' => $documents,
                'storage' => $storageStats
            ]);

        } catch (\Exception $e) {
            error_log("Document list error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->error('Failed to retrieve documents: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download a document
     * 
     * GET /api/employees/{employeeId}/documents/{documentId}/download
     *
     * @return Response File stream or JSON error
     */
    public function download(): Response
    {
        try {
            // Get employee ID and document ID from route
            $employeeId = $this->getRouteParam('employeeId');
            $documentId = $this->getRouteParam('documentId');
            
            // Verify employee exists
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }

            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            // Check access: employee can download own docs (user['id'] is employee ID), admin can download any
            $isOwner = $user['id'] === $employeeId;
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            if (!$isOwner && !$isAdmin) {
                return $this->error('You do not have permission to download documents for this employee', 403);
            }

            // Retrieve document
            $document = $this->employeeDocument->find($documentId);
            if (!$document) {
                return $this->error('Document not found', 404);
            }

            // Verify document belongs to the employee
            if ($document['employee_id'] !== $employeeId) {
                return $this->error('Document does not belong to this employee', 403);
            }

            // Check if file exists
            if (!$this->documentService->fileExists($document['file_path'])) {
                return $this->error('File not found on server', 404);
            }

            // Log the download
            $this->auditLogService->log('document_download', [
                'employee_id' => $employeeId,
                'document_id' => $documentId,
                'document_type' => $document['document_type'],
                'file_name' => $document['file_name']
            ], $user['id'], $user['role'] ?? null);

            // Get file from Supabase Storage
            $supabaseConfig = require dirname(__DIR__, 2) . '/config/supabase.php';
            $fileUrl = 'https://xtfekjcusnnadfgcrzht.supabase.co/storage/v1/object/employee-documents/' . $document['file_path'];
            
            // Download file from Supabase Storage with authentication
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $fileUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $supabaseConfig['service_key'],
                    'apikey: ' . $supabaseConfig['service_key']
                ]
            ]);
            
            $fileContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || $fileContent === false) {
                return $this->error('Failed to download file from storage', 500);
            }
            
            // Create response with file content
            $response = new Response();
            $response->setStatusCode(200);
            $response->setHeader('Content-Type', $document['mime_type']);
            $response->setHeader('Content-Disposition', 'attachment; filename="' . $document['file_name'] . '"');
            $response->setHeader('Content-Length', (string) strlen($fileContent));
            $response->setHeader('Cache-Control', 'no-cache, must-revalidate');
            $response->setHeader('Pragma', 'no-cache');
            $response->setContent($fileContent);
            
            return $response;

        } catch (\Exception $e) {
            error_log("Document download error: " . $e->getMessage());
            return $this->error('Failed to download document. Please try again.', 500);
        }
    }

    /**
     * Delete a document
     * 
     * DELETE /api/employees/{employeeId}/documents/{documentId}
     *
     * @return Response JSON response
     */
    public function delete(): Response
    {
        try {
            // Get employee ID and document ID from route
            $employeeId = $this->getRouteParam('employeeId');
            $documentId = $this->getRouteParam('documentId');
            
            // Verify employee exists
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }

            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            // Check access: employee can delete own docs (user['id'] is employee ID), admin can delete any
            $isOwner = $user['id'] === $employeeId;
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            if (!$isOwner && !$isAdmin) {
                return $this->error('You do not have permission to delete documents for this employee', 403);
            }

            // Retrieve document to get file path
            $document = $this->employeeDocument->find($documentId);
            if (!$document) {
                return $this->error('Document not found', 404);
            }

            // Verify document belongs to the employee
            if ($document['employee_id'] !== $employeeId) {
                return $this->error('Document does not belong to this employee', 403);
            }

            // Delete file from storage
            $this->documentService->deleteFile($document['file_path']);

            // Delete database record
            $this->employeeDocument->delete($documentId);

            // Log the deletion
            $this->auditLogService->log('document_delete', [
                'employee_id' => $employeeId,
                'document_id' => $documentId,
                'document_type' => $document['document_type'],
                'file_name' => $document['file_name']
            ], $user['id'], $user['role'] ?? null);

            return $this->success([], 'Document deleted successfully');

        } catch (\Exception $e) {
            error_log("Document delete error: " . $e->getMessage());
            return $this->error('Failed to delete document. Please try again.', 500);
        }
    }

    /**
     * Verify or unverify a document (admin only)
     * 
     * PUT /api/employees/{employeeId}/documents/{documentId}/verify
     *
     * @return Response JSON response
     */
    public function verify(): Response
    {
        try {
            // Get employee ID and document ID from route
            $employeeId = $this->getRouteParam('employeeId');
            $documentId = $this->getRouteParam('documentId');
            
            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }

            // Only admins can verify documents
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            if (!$isAdmin) {
                return $this->error('Only administrators can verify documents', 403);
            }

            // Verify employee exists
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }

            // Retrieve document
            $document = $this->employeeDocument->find($documentId);
            if (!$document) {
                return $this->error('Document not found', 404);
            }

            // Verify document belongs to the employee
            if ($document['employee_id'] !== $employeeId) {
                return $this->error('Document does not belong to this employee', 403);
            }

            // Get verification status from request
            $jsonData = $this->getJsonData();
            $isVerified = $jsonData['is_verified'] ?? true;
            $notes = $jsonData['notes'] ?? null;

            // Update verification fields
            $updateData = [
                'is_verified' => $isVerified
            ];

            if ($isVerified) {
                // Use supabase_user_id if available, otherwise use employee ID
                $verifiedBy = $employee['supabase_user_id'] ?? $user['id'];
                $updateData['verified_by'] = $verifiedBy;
                $updateData['verified_at'] = date('Y-m-d H:i:s');
            } else {
                $updateData['verified_by'] = null;
                $updateData['verified_at'] = null;
            }

            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }

            // Update document
            $this->employeeDocument->update($documentId, $updateData);

            // Get updated document
            $updatedDocument = $this->employeeDocument->find($documentId);

            // Log the verification
            $this->auditLogService->log('document_verify', [
                'employee_id' => $employeeId,
                'document_id' => $documentId,
                'document_type' => $document['document_type'],
                'file_name' => $document['file_name'],
                'is_verified' => $isVerified
            ], $user['id'], $user['role'] ?? null);

            return $this->success([
                'document' => $updatedDocument
            ], $isVerified ? 'Document verified successfully' : 'Document unverified successfully');

        } catch (\Exception $e) {
            error_log("Document verify error: " . $e->getMessage());
            return $this->error('Failed to verify document. Please try again.', 500);
        }
    }
}
