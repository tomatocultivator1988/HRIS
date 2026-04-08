<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;
use Core\Container;

/**
 * Employee Model - Represents employee entities and handles employee data operations
 * 
 * This model handles employee data access, validation, and business entity operations.
 * Works with the Supabase employees table and provides methods for CRUD operations.
 */
class Employee extends Model
{
    protected string $table = 'employees';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'work_email',
        'mobile_number',
        'department',
        'position',
        'employment_status',
        'date_hired',
        'manager_id',
        'supabase_user_id',
        'is_active',
        'force_password_change',
        'password_changed_at'
    ];
    
    protected array $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deactivated_at',
        'deactivated_by'
    ];
    
    protected array $casts = [
        'is_active' => 'boolean',
        'date_hired' => 'datetime'
    ];
    
    /**
     * Find employee by Supabase user ID
     *
     * @param string $supabaseUserId Supabase user ID
     * @return array|null Employee data or null if not found
     */
    public function findBySupabaseUserId(string $supabaseUserId): ?array
    {
        try {
            $result = $this->where([
                'supabase_user_id' => $supabaseUserId,
                'is_active' => true
            ])->first();
            
            return $result;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findBySupabaseUserId', ['supabase_user_id' => $supabaseUserId]);
            return null;
        }
    }
    
    /**
     * Find employee by employee ID
     *
     * @param string $employeeId Employee ID
     * @return array|null Employee data or null if not found
     */
    public function findByEmployeeId(string $employeeId): ?array
    {
        try {
            $result = $this->where([
                'employee_id' => $employeeId,
                'is_active' => true
            ])->first();
            
            return $result;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByEmployeeId', ['employee_id' => $employeeId]);
            return null;
        }
    }
    
    /**
     * Find employee by work email
     *
     * @param string $workEmail Work email address
     * @return array|null Employee data or null if not found
     */
    public function findByWorkEmail(string $workEmail): ?array
    {
        try {
            $result = $this->where([
                'work_email' => strtolower($workEmail),
                'is_active' => true
            ])->first();
            
            return $result;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByWorkEmail', ['work_email' => $workEmail]);
            return null;
        }
    }
    
    /**
     * Get employees by department
     *
     * @param string $department Department name
     * @param bool $activeOnly Include only active employees
     * @return array Array of employees
     */
    public function getByDepartment(string $department, bool $activeOnly = true): array
    {
        try {
            $conditions = ['department' => $department];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByDepartment', ['department' => $department]);
            return [];
        }
    }
    
    /**
     * Get employees by employment status
     *
     * @param string $employmentStatus Employment status
     * @param bool $activeOnly Include only active employees
     * @return array Array of employees
     */
    public function getByEmploymentStatus(string $employmentStatus, bool $activeOnly = true): array
    {
        try {
            $conditions = ['employment_status' => $employmentStatus];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByEmploymentStatus', ['employment_status' => $employmentStatus]);
            return [];
        }
    }
    
    /**
     * Get employees by manager
     *
     * @param string $managerId Manager ID
     * @param bool $activeOnly Include only active employees
     * @return array Array of employees
     */
    public function getByManager(string $managerId, bool $activeOnly = true): array
    {
        try {
            $conditions = ['manager_id' => $managerId];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            return $this->where($conditions)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByManager', ['manager_id' => $managerId]);
            return [];
        }
    }
    
    /**
     * Get active employees count
     *
     * @return int Number of active employees
     */
    public function getActiveCount(): int
    {
        try {
            return $this->where(['is_active' => true])->count();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getActiveCount', []);
            return 0;
        }
    }
    
    /**
     * Get employees hired in date range
     *
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @param bool $activeOnly Include only active employees
     * @return array Array of employees
     */
    public function getHiredInDateRange(string $startDate, string $endDate, bool $activeOnly = true): array
    {
        try {
            $conditions = [];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            // Note: This is a simplified implementation
            // In a real Supabase implementation, you'd need to handle date ranges properly
            $employees = $this->where($conditions)->get();
            
            // Filter by date range in PHP (not ideal for large datasets)
            return array_filter($employees, function($employee) use ($startDate, $endDate) {
                if (empty($employee['date_hired'])) {
                    return false;
                }
                
                $hiredDate = $employee['date_hired'];
                return $hiredDate >= $startDate && $hiredDate <= $endDate;
            });
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getHiredInDateRange', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Check if employee ID exists
     *
     * @param string $employeeId Employee ID to check
     * @param string|null $excludeId Employee ID to exclude from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function employeeIdExists(string $employeeId, ?string $excludeId = null): bool
    {
        try {
            $conditions = ['employee_id' => $employeeId];
            
            $employees = $this->where($conditions)->get();
            
            if (empty($employees)) {
                return false;
            }
            
            // If excluding an ID (for updates), check if any other employee has this ID
            if ($excludeId !== null) {
                foreach ($employees as $employee) {
                    if ($employee['id'] !== $excludeId) {
                        return true;
                    }
                }
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'employeeIdExists', [
                'employee_id' => $employeeId,
                'exclude_id' => $excludeId
            ]);
            return false;
        }
    }
    
    /**
     * Check if work email exists
     *
     * @param string $workEmail Work email to check
     * @param string|null $excludeId Employee ID to exclude from check (for updates)
     * @return bool True if exists, false otherwise
     */
    public function workEmailExists(string $workEmail, ?string $excludeId = null): bool
    {
        try {
            $conditions = ['work_email' => strtolower($workEmail)];
            
            $employees = $this->where($conditions)->get();
            
            if (empty($employees)) {
                return false;
            }
            
            // If excluding an ID (for updates), check if any other employee has this email
            if ($excludeId !== null) {
                foreach ($employees as $employee) {
                    if ($employee['id'] !== $excludeId) {
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
     * Get employee's full name
     *
     * @param array $employee Employee data
     * @return string Full name
     */
    public function getFullName(array $employee): string
    {
        $firstName = $employee['first_name'] ?? '';
        $lastName = $employee['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Unknown Employee';
    }
    
    /**
     * Check if employee is active
     *
     * @param array $employee Employee data
     * @return bool True if active, false otherwise
     */
    public function isActive(array $employee): bool
    {
        return (bool) ($employee['is_active'] ?? false);
    }
    
    /**
     * Get employee's manager
     *
     * @param array $employee Employee data
     * @return array|null Manager data or null if no manager
     */
    public function getManager(array $employee): ?array
    {
        if (empty($employee['manager_id'])) {
            return null;
        }
        
        try {
            return $this->find($employee['manager_id']);
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getManager', ['manager_id' => $employee['manager_id']]);
            return null;
        }
    }
    
    /**
     * Get employee's direct reports
     *
     * @param string $employeeId Employee ID
     * @param bool $activeOnly Include only active employees
     * @return array Array of direct reports
     */
    public function getDirectReports(string $employeeId, bool $activeOnly = true): array
    {
        return $this->getByManager($employeeId, $activeOnly);
    }
    
    /**
     * Validate employee data before database operations
     *
     * @param array $data Employee data to validate
     * @param mixed $id Employee ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            $requiredFields = ['employee_id', 'first_name', 'last_name', 'work_email', 'department', 'position'];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }
        
        // Employee ID validation
        if (isset($data['employee_id'])) {
            $employeeId = trim($data['employee_id']);
            
            if (strlen($employeeId) < 3 || strlen($employeeId) > 20) {
                $errors['employee_id'] = 'Employee ID must be between 3 and 20 characters';
            } elseif (!preg_match('/^[A-Z0-9-]+$/', $employeeId)) {
                $errors['employee_id'] = 'Employee ID can only contain uppercase letters, numbers, and hyphens';
            } elseif ($this->employeeIdExists($employeeId, $id)) {
                $errors['employee_id'] = 'Employee ID already exists';
            }
        }
        
        // Name validation
        if (isset($data['first_name'])) {
            $firstName = trim($data['first_name']);
            if (strlen($firstName) < 2 || strlen($firstName) > 100) {
                $errors['first_name'] = 'First name must be between 2 and 100 characters';
            }
        }
        
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
            if (strlen($department) > 100) {
                $errors['department'] = 'Department name is too long';
            }
        }
        
        // Position validation
        if (isset($data['position'])) {
            $position = trim($data['position']);
            if (strlen($position) > 100) {
                $errors['position'] = 'Position title is too long';
            }
        }
        
        // Employment status validation
        if (isset($data['employment_status'])) {
            $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
            if (!in_array($data['employment_status'], $validStatuses)) {
                $errors['employment_status'] = 'Invalid employment status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Date hired validation
        if (isset($data['date_hired']) && !empty($data['date_hired'])) {
            $dateHired = strtotime($data['date_hired']);
            if (!$dateHired) {
                $errors['date_hired'] = 'Invalid date format';
            } elseif ($dateHired > time()) {
                $errors['date_hired'] = 'Date hired cannot be in the future';
            }
        }
        
        // Manager validation (if provided)
        if (isset($data['manager_id']) && !empty($data['manager_id'])) {
            $manager = $this->find($data['manager_id']);
            if (!$manager) {
                $errors['manager_id'] = 'Manager not found';
            } elseif (!$this->isActive($manager)) {
                $errors['manager_id'] = 'Manager is not active';
            }
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeEmployeeData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Search employees with advanced filtering
     *
     * @param array $searchParams Search parameters
     * @return array Search results
     */
    public function searchEmployees(array $searchParams): array
    {
        try {
            // Build base conditions
            $conditions = [];
            
            // Active status filter
            if (isset($searchParams['status'])) {
                if ($searchParams['status'] === 'inactive') {
                    $conditions['is_active'] = false;
                } elseif ($searchParams['status'] !== 'all') {
                    $conditions['is_active'] = true;
                }
            } else {
                $conditions['is_active'] = true; // Default to active only
            }
            
            // Department filter
            if (!empty($searchParams['department'])) {
                $conditions['department'] = ['operator' => 'ilike', 'value' => "%{$searchParams['department']}%"];
            }
            
            // Position filter
            if (!empty($searchParams['position'])) {
                $conditions['position'] = ['operator' => 'ilike', 'value' => "%{$searchParams['position']}%"];
            }
            
            // Employment status filter
            if (!empty($searchParams['employment_status'])) {
                $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
                if (in_array($searchParams['employment_status'], $validStatuses)) {
                    $conditions['employment_status'] = $searchParams['employment_status'];
                }
            }
            
            // Get employees matching base conditions
            $employees = $this->where($conditions)->get();
            
            // Apply text search filter
            if (!empty($searchParams['query'])) {
                $employees = $this->applyTextSearch($employees, $searchParams['query']);
            }
            
            // Apply date range filter
            if (!empty($searchParams['date_hired_from']) || !empty($searchParams['date_hired_to'])) {
                $employees = $this->applyDateRangeFilter(
                    $employees, 
                    $searchParams['date_hired_from'] ?? null,
                    $searchParams['date_hired_to'] ?? null
                );
            }
            
            // Apply sorting
            if (!empty($searchParams['sort_by'])) {
                $employees = $this->applySorting($employees, $searchParams['sort_by'], $searchParams['sort_order'] ?? 'ASC');
            }
            
            return $employees;
            
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'searchEmployees', $searchParams);
            return [];
        }
    }
    
    /**
     * Apply text search to employees array
     *
     * @param array $employees Employees array
     * @param string $query Search query
     * @return array Filtered employees
     */
    private function applyTextSearch(array $employees, string $query): array
    {
        $query = strtolower(trim($query));
        
        if (empty($query)) {
            return $employees;
        }
        
        return array_filter($employees, function($employee) use ($query) {
            $searchFields = [
                $employee['employee_id'] ?? '',
                $employee['first_name'] ?? '',
                $employee['last_name'] ?? '',
                $employee['work_email'] ?? '',
                $employee['department'] ?? '',
                $employee['position'] ?? '',
                $employee['mobile_number'] ?? '',
                $employee['employment_status'] ?? ''
            ];
            
            $searchText = strtolower(implode(' ', array_filter($searchFields)));
            
            // Support multiple search terms
            $queryTerms = explode(' ', $query);
            foreach ($queryTerms as $term) {
                $term = trim($term);
                if (!empty($term) && strpos($searchText, $term) === false) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    /**
     * Apply date range filter to employees array
     *
     * @param array $employees Employees array
     * @param string|null $dateFrom Start date
     * @param string|null $dateTo End date
     * @return array Filtered employees
     */
    private function applyDateRangeFilter(array $employees, ?string $dateFrom, ?string $dateTo): array
    {
        if (empty($dateFrom) && empty($dateTo)) {
            return $employees;
        }
        
        return array_filter($employees, function($employee) use ($dateFrom, $dateTo) {
            if (empty($employee['date_hired'])) {
                return false;
            }
            
            $hiredDate = strtotime($employee['date_hired']);
            
            if ($dateFrom && $hiredDate < strtotime($dateFrom)) {
                return false;
            }
            
            if ($dateTo && $hiredDate > strtotime($dateTo)) {
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Apply sorting to employees array
     *
     * @param array $employees Employees array
     * @param string $sortBy Sort field
     * @param string $sortOrder Sort order (ASC/DESC)
     * @return array Sorted employees
     */
    private function applySorting(array $employees, string $sortBy, string $sortOrder = 'ASC'): array
    {
        $allowedSortFields = [
            'employee_id', 'first_name', 'last_name', 'department', 
            'position', 'employment_status', 'date_hired', 'created_at'
        ];
        
        if (!in_array($sortBy, $allowedSortFields)) {
            return $employees;
        }
        
        usort($employees, function($a, $b) use ($sortBy, $sortOrder) {
            $valueA = $a[$sortBy] ?? '';
            $valueB = $b[$sortBy] ?? '';
            
            // Handle date fields
            if (in_array($sortBy, ['date_hired', 'created_at', 'updated_at'])) {
                $valueA = strtotime($valueA ?: '1970-01-01');
                $valueB = strtotime($valueB ?: '1970-01-01');
            }
            
            $comparison = $valueA <=> $valueB;
            
            return strtoupper($sortOrder) === 'DESC' ? -$comparison : $comparison;
        });
        
        return $employees;
    }
    
    /**
     * Get employees with pagination
     *
     * @param array $conditions Filter conditions
     * @param int $limit Number of records per page
     * @param int $offset Number of records to skip
     * @param string|null $orderBy Order by field
     * @return array Paginated employees
     */
    public function getPaginated(array $conditions = [], int $limit = 50, int $offset = 0, ?string $orderBy = null): array
    {
        try {
            $query = $this->where($conditions);
            
            if ($orderBy) {
                // Parse order by (e.g., "created_at.DESC")
                $orderParts = explode('.', $orderBy);
                $field = $orderParts[0];
                $direction = strtoupper($orderParts[1] ?? 'ASC');
                
                // Note: This is a simplified implementation
                // In a real Supabase implementation, you'd use the order() method
                $employees = $query->get();
                $employees = $this->applySorting($employees, $field, $direction);
            } else {
                $employees = $query->get();
            }
            
            // Apply pagination
            return array_slice($employees, $offset, $limit);
            
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getPaginated', [
                'conditions' => $conditions,
                'limit' => $limit,
                'offset' => $offset
            ]);
            return [];
        }
    }
    
    /**
     * Get unique departments
     *
     * @param bool $activeOnly Include only active employees
     * @return array Array of unique departments
     */
    public function getUniqueDepartments(bool $activeOnly = true): array
    {
        try {
            $conditions = [];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            $employees = $this->where($conditions)->get();
            
            $departments = array_unique(array_filter(array_column($employees, 'department')));
            sort($departments);
            
            return $departments;
            
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getUniqueDepartments', ['active_only' => $activeOnly]);
            return [];
        }
    }
    
    /**
     * Get unique positions
     *
     * @param bool $activeOnly Include only active employees
     * @return array Array of unique positions
     */
    public function getUniquePositions(bool $activeOnly = true): array
    {
        try {
            $conditions = [];
            
            if ($activeOnly) {
                $conditions['is_active'] = true;
            }
            
            $employees = $this->where($conditions)->get();
            
            $positions = array_unique(array_filter(array_column($employees, 'position')));
            sort($positions);
            
            return $positions;
            
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getUniquePositions', ['active_only' => $activeOnly]);
            return [];
        }
    }
    
    /**
     * Get employee statistics
     *
     * @return array Employee statistics
     */
    public function getStatistics(): array
    {
        try {
            $allEmployees = $this->all();
            
            $stats = [
                'total' => count($allEmployees),
                'active' => 0,
                'inactive' => 0,
                'by_status' => [
                    'Regular' => 0,
                    'Probationary' => 0,
                    'Contractual' => 0,
                    'Part-time' => 0
                ],
                'by_department' => [],
                'recent_hires' => 0 // Last 30 days
            ];
            
            $thirtyDaysAgo = strtotime('-30 days');
            
            foreach ($allEmployees as $employee) {
                // Active/Inactive count
                if ($employee['is_active']) {
                    $stats['active']++;
                    
                    // Employment status count (only for active employees)
                    $status = $employee['employment_status'] ?? 'Unknown';
                    if (isset($stats['by_status'][$status])) {
                        $stats['by_status'][$status]++;
                    }
                    
                    // Department count
                    $department = $employee['department'] ?? 'Unassigned';
                    $stats['by_department'][$department] = ($stats['by_department'][$department] ?? 0) + 1;
                } else {
                    $stats['inactive']++;
                }
                
                // Recent hires
                if (!empty($employee['date_hired'])) {
                    $dateHired = $employee['date_hired'];
                    if ($dateHired instanceof \DateTime) {
                        $dateHired = $dateHired->getTimestamp();
                    } else {
                        $dateHired = strtotime($dateHired);
                    }
                    
                    if ($dateHired >= $thirtyDaysAgo) {
                        $stats['recent_hires']++;
                    }
                }
            }
            
            return $stats;
            
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getStatistics', []);
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'by_status' => [],
                'by_department' => [],
                'recent_hires' => 0
            ];
        }
    }
    
    /**
     * Sanitize employee data
     *
     * @param array $data Raw employee data
     * @return array Sanitized data
     */
    private function sanitizeEmployeeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                
                // Special handling for specific fields
                switch ($key) {
                    case 'employee_id':
                        $value = strtoupper($value);
                        break;
                    case 'work_email':
                        $value = strtolower($value);
                        break;
                    case 'first_name':
                    case 'last_name':
                    case 'department':
                    case 'position':
                        $value = ucwords(strtolower($value));
                        break;
                }
                
                // General sanitization
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
}