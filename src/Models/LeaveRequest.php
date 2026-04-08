<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * LeaveRequest Model - Represents leave request entities and handles leave request data operations
 * 
 * This model handles leave request data access, validation, and business entity operations.
 * Works with the Supabase leave_requests table and provides methods for CRUD operations.
 */
class LeaveRequest extends Model
{
    protected string $table = 'leave_requests';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'denial_reason'
    ];
    
    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'total_days' => 'float',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    /**
     * Get leave requests by employee
     *
     * @param string $employeeId Employee ID
     * @param string|null $status Filter by status
     * @return array Array of leave requests
     */
    public function getByEmployee(string $employeeId, ?string $status = null): array
    {
        try {
            $conditions = ['employee_id' => $employeeId];
            
            if ($status !== null) {
                $conditions['status'] = $status;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByEmployee', [
                'employee_id' => $employeeId,
                'status' => $status
            ]);
            return [];
        }
    }
    
    /**
     * Get pending leave requests
     *
     * @return array Array of pending leave requests
     */
    public function getPending(): array
    {
        try {
            return $this->where(['status' => 'Pending'])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getPending', []);
            return [];
        }
    }
    
    /**
     * Get leave requests by status
     *
     * @param string $status Status
     * @return array Array of leave requests
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
     * Check for overlapping leave requests
     *
     * @param string $employeeId Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param string|null $excludeId Leave request ID to exclude (for updates)
     * @return bool True if overlapping requests exist
     */
    public function hasOverlappingLeave(string $employeeId, string $startDate, string $endDate, ?string $excludeId = null): bool
    {
        try {
            $requests = $this->where(['employee_id' => $employeeId])->get();
            
            foreach ($requests as $request) {
                // Skip the current request if updating
                if ($excludeId && $request['id'] === $excludeId) {
                    continue;
                }
                
                // Only check pending or approved requests
                if (!in_array($request['status'], ['Pending', 'Approved'])) {
                    continue;
                }
                
                // Check for overlap
                if ($request['start_date'] <= $endDate && $request['end_date'] >= $startDate) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'hasOverlappingLeave', [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return false;
        }
    }
    
    /**
     * Get leave requests within date range
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Array of leave requests
     */
    public function getByDateRange(string $startDate, string $endDate): array
    {
        try {
            $requests = $this->all();
            
            return array_filter($requests, function($request) use ($startDate, $endDate) {
                return $request['start_date'] <= $endDate && $request['end_date'] >= $startDate;
            });
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByDateRange', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Validate leave request data before database operations
     *
     * @param array $data Leave request data to validate
     * @param mixed $id Leave request ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            $requiredFields = ['employee_id', 'leave_type_id', 'start_date', 'end_date'];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Date validation
        if (isset($data['start_date'])) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $data['start_date']);
            if (!$startDate || $startDate->format('Y-m-d') !== $data['start_date']) {
                $errors['start_date'] = 'Invalid start date format (Y-m-d required)';
            }
        }
        
        if (isset($data['end_date'])) {
            $endDate = \DateTime::createFromFormat('Y-m-d', $data['end_date']);
            if (!$endDate || $endDate->format('Y-m-d') !== $data['end_date']) {
                $errors['end_date'] = 'Invalid end date format (Y-m-d required)';
            }
        }
        
        // Validate end date is not before start date
        if (isset($data['start_date']) && isset($data['end_date']) && 
            empty($errors['start_date']) && empty($errors['end_date'])) {
            if ($data['end_date'] < $data['start_date']) {
                $errors['end_date'] = 'End date must be on or after start date';
            }
        }
        
        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['Pending', 'Approved', 'Denied', 'Cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Total days validation
        if (isset($data['total_days'])) {
            if (!is_numeric($data['total_days']) || $data['total_days'] <= 0) {
                $errors['total_days'] = 'Total days must be a positive number';
            }
        }
        
        // Reason validation
        if (isset($data['reason']) && strlen($data['reason']) > 500) {
            $errors['reason'] = 'Reason must not exceed 500 characters';
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeLeaveRequestData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Sanitize leave request data
     *
     * @param array $data Raw leave request data
     * @return array Sanitized data
     */
    private function sanitizeLeaveRequestData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
    
    /**
     * Get leave request statistics
     *
     * @param string|null $employeeId Filter by employee ID
     * @param int|null $year Filter by year
     * @return array Statistics
     */
    public function getStatistics(?string $employeeId = null, ?int $year = null): array
    {
        try {
            $conditions = [];
            
            if ($employeeId !== null) {
                $conditions['employee_id'] = $employeeId;
            }
            
            $requests = $this->where($conditions)->get();
            
            // Filter by year if provided
            if ($year !== null) {
                $requests = array_filter($requests, function($request) use ($year) {
                    return date('Y', strtotime($request['start_date'])) == $year;
                });
            }
            
            $stats = [
                'total_requests' => count($requests),
                'pending' => 0,
                'approved' => 0,
                'denied' => 0,
                'cancelled' => 0,
                'total_days_requested' => 0.0,
                'total_days_approved' => 0.0
            ];
            
            foreach ($requests as $request) {
                switch ($request['status']) {
                    case 'Pending':
                        $stats['pending']++;
                        break;
                    case 'Approved':
                        $stats['approved']++;
                        $stats['total_days_approved'] += floatval($request['total_days']);
                        break;
                    case 'Denied':
                        $stats['denied']++;
                        break;
                    case 'Cancelled':
                        $stats['cancelled']++;
                        break;
                }
                
                $stats['total_days_requested'] += floatval($request['total_days']);
            }
            
            return $stats;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getStatistics', [
                'employee_id' => $employeeId,
                'year' => $year
            ]);
            return [
                'total_requests' => 0,
                'pending' => 0,
                'approved' => 0,
                'denied' => 0,
                'cancelled' => 0,
                'total_days_requested' => 0.0,
                'total_days_approved' => 0.0
            ];
        }
    }
}
