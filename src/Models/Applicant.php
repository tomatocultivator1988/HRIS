<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * Applicant Model - Represents applicant entities and handles applicant data operations
 * 
 * This model handles applicant data access, validation, and business entity operations.
 * Works with the Supabase applicants table and provides methods for CRUD operations.
 */
class Applicant extends Model
{
    protected string $table = 'applicants';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'job_posting_id',
        'first_name',
        'last_name',
        'work_email',
        'mobile_number',
        'department',
        'position',
        'employment_status',
        'status',
        'employee_id',
        'is_active'
    ];
    
    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'is_active' => 'boolean'
    ];
    
    /**
     * Get applicants by job posting
     *
     * @param string $jobPostingId Job posting ID
     * @return array Array of applicants
     */
    public function getByJobPosting(string $jobPostingId): array
    {
        try {
            return $this->where(['job_posting_id' => $jobPostingId])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByJobPosting', ['job_posting_id' => $jobPostingId]);
            return [];
        }
    }
    
    /**
     * Get applicants by status
     *
     * @param string $status Status (Applied, In Progress, Passed, Failed, Hired)
     * @return array Array of applicants
     */
    public function getByStatus(string $status): array
    {
        try {
            return $this->where(['status' => $status])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByStatus', ['status' => $status]);
            return [];
        }
    }
    
    /**
     * Find applicant by work email
     *
     * @param string $workEmail Work email address
     * @return array|null Applicant data or null if not found
     */
    public function findByWorkEmail(string $workEmail): ?array
    {
        try {
            $result = $this->where([
                'work_email' => strtolower($workEmail)
            ])->first();
            
            return $result;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByWorkEmail', ['work_email' => $workEmail]);
            return null;
        }
    }
    
    /**
     * Check if work email exists
     *
     * @param string $workEmail Work email to check
     * @param string|null $excludeId Applicant ID to exclude from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function workEmailExists(string $workEmail, ?string $excludeId = null): bool
    {
        try {
            $conditions = ['work_email' => strtolower($workEmail)];
            
            $applicants = $this->where($conditions)->get();
            
            if (empty($applicants)) {
                return false;
            }
            
            // If excluding an ID (for updates), check if any other applicant has this email
            if ($excludeId !== null) {
                foreach ($applicants as $applicant) {
                    if ($applicant['id'] !== $excludeId) {
                        return true;
                    }
                }
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'workEmailExists', [
                'work_email' => $workEmail,
                'exclude_id' => $excludeId
            ]);
            return false;
        }
    }
    
    /**
     * Get applicant's full name
     *
     * @param array $applicant Applicant data
     * @return string Full name
     */
    public function getFullName(array $applicant): string
    {
        $firstName = $applicant['first_name'] ?? '';
        $lastName = $applicant['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Unknown Applicant';
    }
    
    /**
     * Check if applicant is active
     *
     * @param array $applicant Applicant data
     * @return bool True if active, false otherwise
     */
    public function isActive(array $applicant): bool
    {
        return (bool) ($applicant['is_active'] ?? false);
    }
    
    /**
     * Check if applicant is hired
     *
     * @param array $applicant Applicant data
     * @return bool True if hired, false otherwise
     */
    public function isHired(array $applicant): bool
    {
        return isset($applicant['status']) && $applicant['status'] === 'Hired';
    }
    
    /**
     * Validate applicant data before database operations
     *
     * @param array $data Applicant data to validate
     * @param mixed $id Applicant ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            $requiredFields = ['job_posting_id', 'first_name', 'last_name', 'work_email', 'department', 'position', 'employment_status'];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // First name validation
        if (isset($data['first_name'])) {
            $firstName = trim($data['first_name']);
            if (strlen($firstName) < 2 || strlen($firstName) > 100) {
                $errors['first_name'] = 'First name must be between 2 and 100 characters';
            }
        }
        
        // Last name validation
        if (isset($data['last_name'])) {
            $lastName = trim($data['last_name']);
            if (strlen($lastName) < 2 || strlen($lastName) > 100) {
                $errors['last_name'] = 'Last name must be between 2 and 100 characters';
            }
        }
        
        // Email validation
        if (isset($data['work_email'])) {
            $workEmail = trim($data['work_email']);
            
            if (!filter_var($workEmail, FILTER_VALIDATE_EMAIL)) {
                $errors['work_email'] = 'Invalid email format';
            } elseif (strlen($workEmail) > 255) {
                $errors['work_email'] = 'Email address is too long';
            } elseif ($this->workEmailExists($workEmail, $id)) {
                $errors['work_email'] = 'Work email already exists';
            }
        }
        
        // Mobile number validation
        if (isset($data['mobile_number']) && !empty($data['mobile_number'])) {
            $mobileNumber = trim($data['mobile_number']);
            if (strlen($mobileNumber) > 20) {
                $errors['mobile_number'] = 'Mobile number is too long';
            }
        }
        
        // Department validation
        if (isset($data['department'])) {
            $department = trim($data['department']);
            if (strlen($department) < 1 || strlen($department) > 100) {
                $errors['department'] = 'Department must be between 1 and 100 characters';
            }
        }
        
        // Position validation
        if (isset($data['position'])) {
            $position = trim($data['position']);
            if (strlen($position) < 1 || strlen($position) > 100) {
                $errors['position'] = 'Position must be between 1 and 100 characters';
            }
        }
        
        // Employment status validation
        if (isset($data['employment_status'])) {
            $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
            if (!in_array($data['employment_status'], $validStatuses)) {
                $errors['employment_status'] = 'Invalid employment status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['Applied', 'In Progress', 'Passed', 'Failed', 'Hired'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeApplicantData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Sanitize applicant data
     *
     * @param array $data Raw applicant data
     * @return array Sanitized data
     */
    private function sanitizeApplicantData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                
                // Special handling for specific fields
                if ($key === 'work_email') {
                    $value = strtolower($value);
                }
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
}
