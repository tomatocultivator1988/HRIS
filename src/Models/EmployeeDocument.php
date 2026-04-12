<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * EmployeeDocument Model
 * 
 * Manages employee 201 files metadata and database operations.
 * Files are stored in storage/201files/{employee_id}/ directory.
 * 
 * @package App\Models
 */
class EmployeeDocument extends Model
{
    protected string $table = 'employee_documents';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'employee_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'uploaded_at',
        'notes',
        'is_verified',
        'verified_by',
        'verified_at'
    ];

    protected array $casts = [
        'file_size' => 'integer',
        'is_verified' => 'boolean',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Find all documents for a specific employee
     *
     * @param string $employeeId Employee UUID
     * @return array Array of documents
     */
    public function findByEmployeeId(string $employeeId): array
    {
        try {
            return $this->where(['employee_id' => $employeeId])
                ->orderBy('uploaded_at', 'DESC')
                ->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByEmployeeId', ['employee_id' => $employeeId]);
            return [];
        }
    }

    /**
     * Get total storage used by an employee
     *
     * @param string $employeeId Employee UUID
     * @return int Total bytes used
     */
    public function getTotalSize(string $employeeId): int
    {
        try {
            $documents = $this->where(['employee_id' => $employeeId])->get();
            return array_sum(array_column($documents, 'file_size'));
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getTotalSize', ['employee_id' => $employeeId]);
            return 0;
        }
    }

    /**
     * Get document count for an employee
     *
     * @param string $employeeId Employee UUID
     * @return int Number of documents
     */
    public function getDocumentCount(string $employeeId): int
    {
        try {
            $documents = $this->where(['employee_id' => $employeeId])->get();
            return count($documents);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getDocumentCount', ['employee_id' => $employeeId]);
            return 0;
        }
    }

    /**
     * Find documents by type
     *
     * @param string $employeeId Employee UUID
     * @param string $documentType Document type
     * @return array Array of documents
     */
    public function findByType(string $employeeId, string $documentType): array
    {
        try {
            return $this->where([
                'employee_id' => $employeeId,
                'document_type' => $documentType
            ])->orderBy('uploaded_at', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByType', [
                'employee_id' => $employeeId,
                'document_type' => $documentType
            ]);
            return [];
        }
    }

    /**
     * Get verified documents for an employee
     *
     * @param string $employeeId Employee UUID
     * @return array Array of verified documents
     */
    public function getVerifiedDocuments(string $employeeId): array
    {
        try {
            return $this->where([
                'employee_id' => $employeeId,
                'is_verified' => true
            ])->orderBy('verified_at', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getVerifiedDocuments', ['employee_id' => $employeeId]);
            return [];
        }
    }

    /**
     * Get unverified documents for an employee
     *
     * @param string $employeeId Employee UUID
     * @return array Array of unverified documents
     */
    public function getUnverifiedDocuments(string $employeeId): array
    {
        try {
            return $this->where([
                'employee_id' => $employeeId,
                'is_verified' => false
            ])->orderBy('uploaded_at', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getUnverifiedDocuments', ['employee_id' => $employeeId]);
            return [];
        }
    }

    /**
     * Validate document data
     *
     * @param array $data Document data
     * @param string|null $id Document ID for updates
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        // Validate employee_id
        if (empty($data['employee_id'])) {
            $errors['employee_id'] = 'Employee ID is required';
        }

        // Validate document_type
        if (empty($data['document_type'])) {
            $errors['document_type'] = 'Document type is required';
        } else {
            $validTypes = [
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
            if (!in_array($data['document_type'], $validTypes)) {
                $errors['document_type'] = 'Invalid document type';
            }
        }

        // Validate file_name
        if (empty($data['file_name'])) {
            $errors['file_name'] = 'File name is required';
        } elseif (strlen($data['file_name']) > 255) {
            $errors['file_name'] = 'File name must not exceed 255 characters';
        }

        // Validate file_path
        if (empty($data['file_path'])) {
            $errors['file_path'] = 'File path is required';
        } elseif (strlen($data['file_path']) > 500) {
            $errors['file_path'] = 'File path must not exceed 500 characters';
        }

        // Validate file_size
        if (!isset($data['file_size'])) {
            $errors['file_size'] = 'File size is required';
        } elseif (!is_numeric($data['file_size']) || $data['file_size'] <= 0) {
            $errors['file_size'] = 'File size must be a positive number';
        }

        // Validate mime_type
        if (empty($data['mime_type'])) {
            $errors['mime_type'] = 'MIME type is required';
        }

        // Validate uploaded_by
        if (empty($data['uploaded_by'])) {
            $errors['uploaded_by'] = 'Uploaded by user ID is required';
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
