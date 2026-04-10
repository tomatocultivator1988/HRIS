<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * JobPosting Model - Represents job posting entities and handles job posting data operations
 * 
 * This model handles job posting data access, validation, and business entity operations.
 * Works with the Supabase job_postings table and provides methods for CRUD operations.
 */
class JobPosting extends Model
{
    protected string $table = 'job_postings';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'job_title',
        'department',
        'position',
        'num_openings',
        'description',
        'status'
    ];
    
    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'num_openings' => 'integer'
    ];
    
    /**
     * Get job postings by status
     *
     * @param string $status Status (Open, Closed, On Hold)
     * @return array Array of job postings
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
     * Get job postings by department
     *
     * @param string $department Department name
     * @return array Array of job postings
     */
    public function getByDepartment(string $department): array
    {
        try {
            return $this->where(['department' => $department])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByDepartment', ['department' => $department]);
            return [];
        }
    }
    
    /**
     * Get open job postings
     *
     * @return array Array of open job postings
     */
    public function getOpen(): array
    {
        return $this->getByStatus('Open');
    }
    
    /**
     * Check if job posting has available openings
     *
     * @param array $jobPosting Job posting data
     * @return bool True if has openings, false otherwise
     */
    public function hasOpenings(array $jobPosting): bool
    {
        return isset($jobPosting['num_openings']) && $jobPosting['num_openings'] > 0;
    }
    
    /**
     * Validate job posting data before database operations
     *
     * @param array $data Job posting data to validate
     * @param mixed $id Job posting ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            $requiredFields = ['job_title', 'department', 'position', 'num_openings'];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field]) && $data[$field] !== 0) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Job title validation
        if (isset($data['job_title'])) {
            $jobTitle = trim($data['job_title']);
            if (strlen($jobTitle) < 3 || strlen($jobTitle) > 255) {
                $errors['job_title'] = 'Job title must be between 3 and 255 characters';
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
        
        // Number of openings validation
        if (isset($data['num_openings'])) {
            if (!is_numeric($data['num_openings']) || $data['num_openings'] < 0) {
                $errors['num_openings'] = 'Number of openings must be a non-negative integer';
            }
        }
        
        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['Open', 'Closed', 'On Hold'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeJobPostingData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Sanitize job posting data
     *
     * @param array $data Raw job posting data
     * @return array Sanitized data
     */
    private function sanitizeJobPostingData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            
            // Cast num_openings to integer
            if ($key === 'num_openings' && is_numeric($value)) {
                $value = (int) $value;
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
}
