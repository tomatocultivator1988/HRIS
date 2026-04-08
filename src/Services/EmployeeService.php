<?php

namespace Services;

use Core\ValidationException;
use Core\NotFoundException;
use Models\Employee;
use Exception;

/**
 * EmployeeService - Handles employee business logic
 * 
 * This service encapsulates all employee-related business logic including
 * CRUD operations, validation, search functionality, and Supabase auth integration.
 */
class EmployeeService
{
    private Employee $employeeModel;
    private AuthService $authService;
    private array $config;
    
    public function __construct(Employee $employeeModel, AuthService $authService)
    {
        $this->employeeModel = $employeeModel;
        $this->authService = $authService;
        $this->loadConfig();
    }
    
    /**
     * Load Supabase configuration
     */
    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/supabase.php';
        $this->config = require $configFile;
    }
    
    /**
     * Get employees with filtering and pagination
     *
     * @param array $filters Filter parameters
     * @return array Employees list with pagination
     */
    public function getEmployees(array $filters): array
    {
        try {
            // Build database filters
            $dbFilters = [];
            
            // Active status filter (default to active only)
            if ($filters['status'] === 'all') {
                // No filter for status
            } elseif ($filters['status'] === 'inactive') {
                $dbFilters['is_active'] = false;
            } else {
                $dbFilters['is_active'] = true; // Default
            }
            
            // Department filter
            if (!empty($filters['department'])) {
                $dbFilters['department'] = ['operator' => 'ilike', 'value' => "%{$filters['department']}%"];
            }
            
            // Position filter
            if (!empty($filters['position'])) {
                $dbFilters['position'] = ['operator' => 'ilike', 'value' => "%{$filters['position']}%"];
            }
            
            // Employment status filter
            if (!empty($filters['employment_status'])) {
                $dbFilters['employment_status'] = $filters['employment_status'];
            }
            
            // Build options
            $options = [
                'limit' => $filters['limit'],
                'offset' => $filters['offset'],
                'order' => "{$filters['order_by']}.{$filters['order_dir']}"
            ];
            
            // Get employees from database
            $employees = $this->employeeModel->all($dbFilters, [], $filters['limit'], $filters['offset']);
            
            // Apply search filter if provided
            if (!empty($filters['search'])) {
                $employees = $this->applySearchFilter($employees, $filters['search']);
            }
            
            // Get total count
            $totalCount = $this->employeeModel->count($dbFilters);
            
            // If search was applied, adjust total count
            if (!empty($filters['search'])) {
                $totalCount = count($employees);
            }
            
            // Format employee data
            $formattedEmployees = array_map([$this, 'formatEmployeeData'], $employees);
            
            // Prepare filter metadata
            $filterMeta = [
                'search' => $filters['search'],
                'department' => $filters['department'],
                'position' => $filters['position'],
                'status' => $filters['status'],
                'employment_status' => $filters['employment_status'],
                'order_by' => $filters['order_by'],
                'order_dir' => $filters['order_dir']
            ];
            
            return [
                'employees' => $formattedEmployees,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $filters['limit'],
                    'offset' => $filters['offset'],
                    'has_more' => ($filters['offset'] + $filters['limit']) < $totalCount,
                    'current_page' => floor($filters['offset'] / $filters['limit']) + 1,
                    'total_pages' => ceil($totalCount / $filters['limit'])
                ],
                'filters' => $filterMeta,
                'summary' => [
                    'total_employees' => $totalCount,
                    'active_employees' => $filters['status'] !== 'inactive' ? 
                        count(array_filter($formattedEmployees, function($emp) { return $emp['is_active']; })) : 0,
                    'inactive_employees' => $filters['status'] !== 'active' ? 
                        count(array_filter($formattedEmployees, function($emp) { return !$emp['is_active']; })) : 0
                ]
            ];
            
        } catch (Exception $e) {
            error_log('EmployeeService::getEmployees Error: ' . $e->getMessage());
            throw new Exception('Failed to fetch employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Get employee by Supabase user ID
     *
     * @param string $supabaseUserId Supabase user ID
     * @return array|null Employee data or null if not found
     */
    public function getEmployeeBySupabaseUserId(string $supabaseUserId): ?array
    {
        try {
            $employee = $this->employeeModel->findBySupabaseUserId($supabaseUserId);
            
            if (!$employee) {
                return null;
            }
            
            return $this->formatEmployeeData($employee);
            
        } catch (Exception $e) {
            error_log('EmployeeService::getEmployeeBySupabaseUserId Error: ' . $e->getMessage());
            return null;
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
            return $this->employeeModel->getUniqueDepartments($activeOnly);
        } catch (Exception $e) {
            error_log('EmployeeService::getUniqueDepartments Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get employee by ID
     *
     * @param string $employeeId Employee ID
     * @return array Employee data
     * @throws NotFoundException If employee not found
     */
    public function getEmployeeById(string $employeeId): array
    {
        try {
            $employee = $this->employeeModel->find($employeeId);
            
            if (!$employee) {
                throw new NotFoundException('Employee not found');
            }
            
            return $this->formatEmployeeData($employee);
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::getEmployeeById Error: ' . $e->getMessage());
            throw new Exception('Failed to fetch employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Create new employee
     *
     * @param array $data Employee data
     * @return array Created employee data
     */
    public function createEmployee(array $data): array
    {
        try {
            // Validate required fields
            $requiredFields = [
                'first_name', 'last_name', 'work_email',
                'department', 'position', 'employment_status'
            ];
            
            $this->validateRequiredFields($data, $requiredFields);
            
            // Validate employee data
            $this->validateEmployeeData($data);
            
            // Prepare employee data
            $employeeId = !empty($data['employee_id'])
                ? strtoupper(trim($data['employee_id']))
                : $this->generateEmployeeId();

            $employeeData = [
                'employee_id' => $employeeId,
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'work_email' => strtolower(trim($data['work_email'])),
                'mobile_number' => !empty($data['mobile_number'])
                    ? trim($data['mobile_number'])
                    : (!empty($data['phone']) ? trim($data['phone']) : null),
                'department' => trim($data['department']),
                'position' => trim($data['position']),
                'employment_status' => $data['employment_status'],
                'date_hired' => !empty($data['date_hired']) ? $data['date_hired'] : null,
                'manager_id' => !empty($data['manager_id']) ? $data['manager_id'] : null,
                'is_active' => true
            ];

            $authUserCreated = false;
            $usedDefaultPassword = false;
            $generatedPassword = null;
            
            if (!empty($data['supabase_user_id'])) {
                $employeeData['supabase_user_id'] = $data['supabase_user_id'];
            } else {
                try {
                    // Generate default password: firstname + phone (e.g., Juan09123456789)
                    $defaultPassword = $this->generateDefaultPasswordFromData($employeeData);
                    
                    // Check if custom password provided
                    if (!empty($data['password'])) {
                        $password = $data['password'];
                        $usedDefaultPassword = false; // Custom password provided
                    } else {
                        $password = $defaultPassword;
                        $usedDefaultPassword = true; // Using generated default password
                    }
                    
                    $authResult = $this->createSupabaseAuthUser($employeeData['work_email'], $password);
                    if ($authResult['success']) {
                        $employeeData['supabase_user_id'] = $authResult['user_id'];
                        $authUserCreated = true;
                        $generatedPassword = $password;
                    } else {
                        error_log('Warning: Failed to create Supabase auth user for ' . $employeeData['work_email'] . ': ' . ($authResult['error'] ?? 'Unknown error'));
                    }
                } catch (Exception $authException) {
                    error_log('Warning: Exception creating Supabase auth user: ' . $authException->getMessage());
                }
            }

            if (empty($employeeData['supabase_user_id'])) {
                $employeeData['supabase_user_id'] = $this->generateUuidV4();
            }
            
            // ALWAYS force password change for new employees (security best practice)
            // Employee must change password on first login regardless of who set it
            $employeeData['force_password_change'] = true;
            error_log('EmployeeService: Setting force_password_change=true for ' . $employeeData['work_email'] . ' (new employee - must change password on first login)');
            error_log('EmployeeService: employeeData before create: ' . json_encode([
                'force_password_change' => $employeeData['force_password_change'],
                'work_email' => $employeeData['work_email']
            ]));
            
            // Create employee record
            $newEmployee = $this->employeeModel->create($employeeData);
            if (isset($newEmployee[0]) && is_array($newEmployee[0])) {
                $newEmployee = $newEmployee[0];
            }

            if (empty($newEmployee['id'])) {
                $createdEmployee = $this->employeeModel->where(['employee_id' => $employeeData['employee_id']])->first();
                if (!empty($createdEmployee['id'])) {
                    $newEmployee = $createdEmployee;
                }
            }

            if (empty($newEmployee['id'])) {
                throw new Exception('Employee created but ID could not be resolved');
            }

            $this->initializeLeaveCredits($newEmployee['id']);
            
            // Format response data
            $responseData = $this->formatEmployeeData($newEmployee);
            
            // Include generated password in response if it was auto-generated
            if (isset($generatedPassword)) {
                $responseData['temporary_password'] = $generatedPassword;
                $responseData['password_message'] = 'This is a temporary password. Employee should change it after first login.';
            }

            $responseData['auth_user_created'] = $authUserCreated;
            $responseData['auth_linked'] = $authUserCreated || !empty($data['supabase_user_id']);
            $responseData['requires_auth_link'] = !$responseData['auth_linked'];
            
            return $responseData;
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::createEmployee Error: ' . $e->getMessage());
            throw new Exception('Failed to create employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Update existing employee
     *
     * @param string $employeeId Employee ID
     * @param array $data Updated data
     * @return array Updated employee data
     */
    public function updateEmployee(string $employeeId, array $data): array
    {
        try {
            // Get existing employee
            $existingEmployee = $this->employeeModel->find($employeeId);
            
            if (!$existingEmployee) {
                throw new NotFoundException('Employee not found');
            }
            
            // Prepare update data (only include provided fields)
            $updateData = [];
            $updatableFields = [
                'first_name', 'last_name', 'work_email', 'mobile_number',
                'department', 'position', 'employment_status', 'date_hired', 'manager_id'
            ];
            
            foreach ($updatableFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            // Employee ID cannot be changed
            if (isset($data['employee_id']) && $data['employee_id'] !== $existingEmployee['employee_id']) {
                throw new ValidationException('Employee ID cannot be modified', ['employee_id' => 'Employee ID cannot be modified']);
            }
            
            // Validate updated data if any fields are being changed
            if (!empty($updateData)) {
                $this->validateEmployeeData($updateData, $employeeId);
                
                // Clean up data
                if (isset($updateData['work_email'])) {
                    $updateData['work_email'] = strtolower(trim($updateData['work_email']));
                }
                
                if (isset($updateData['first_name'])) {
                    $updateData['first_name'] = trim($updateData['first_name']);
                }
                
                if (isset($updateData['last_name'])) {
                    $updateData['last_name'] = trim($updateData['last_name']);
                }
                
                if (isset($updateData['mobile_number'])) {
                    $updateData['mobile_number'] = !empty($updateData['mobile_number']) 
                        ? trim($updateData['mobile_number']) 
                        : null;
                }
                
                if (isset($updateData['department'])) {
                    $updateData['department'] = trim($updateData['department']);
                }
                
                if (isset($updateData['position'])) {
                    $updateData['position'] = trim($updateData['position']);
                }
                
                // Update employee record
                $success = $this->employeeModel->update($employeeId, $updateData);
                
                if (!$success) {
                    throw new Exception('Failed to update employee record');
                }
            }
            
            // Get updated employee data
            $updatedEmployee = $this->employeeModel->find($employeeId);
            
            return $this->formatEmployeeData($updatedEmployee);
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::updateEmployee Error: ' . $e->getMessage());
            throw new Exception('Failed to update employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Deactivate employee (soft delete)
     *
     * @param string $employeeId Employee ID
     * @return array Deactivated employee data
     */
    public function deactivateEmployee(string $employeeId): array
    {
        try {
            // Get existing employee
            $existingEmployee = $this->employeeModel->find($employeeId);
            
            if (!$existingEmployee) {
                throw new NotFoundException('Employee not found');
            }
            
            // Check if employee is already inactive
            if (!$existingEmployee['is_active']) {
                throw new ValidationException('Employee is already inactive', ['status' => 'Employee is already inactive']);
            }
            
            // Check for dependencies before soft delete
            $dependencies = $this->checkEmployeeDependencies($employeeId);
            
            // Perform soft delete by setting is_active to false
            $updateData = [
                'is_active' => false,
                'deactivated_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->employeeModel->update($employeeId, $updateData);
            
            if (!$success) {
                throw new Exception('Failed to deactivate employee');
            }
            
            // Also disable the Supabase authentication user
            if (!empty($existingEmployee['supabase_user_id'])) {
                $authDeleteResult = $this->deleteSupabaseAuthUser($existingEmployee['supabase_user_id']);
                if (!$authDeleteResult['success']) {
                    error_log('Warning: Failed to delete Supabase auth user for employee ' . $employeeId . ': ' . ($authDeleteResult['error'] ?? 'Unknown error'));
                }
            }
            
            // Get updated employee data
            $updatedEmployee = $this->employeeModel->find($employeeId);
            $responseData = $this->formatEmployeeData($updatedEmployee);
            
            // Add metadata about the operation
            $responseData['soft_delete'] = true;
            $responseData['permanent_delete'] = false;
            $responseData['dependencies_checked'] = true;
            
            if (!empty($dependencies)) {
                $responseData['dependencies'] = $dependencies;
                $responseData['note'] = 'Employee deactivated despite dependencies. Review related records.';
            }
            
            return $responseData;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::deactivateEmployee Error: ' . $e->getMessage());
            throw new Exception('Failed to deactivate employee: ' . $e->getMessage());
        }
    }
    
    /**
     * Search employees with advanced filtering
     *
     * @param array $searchParams Search parameters
     * @return array Search results with pagination
     */
    public function searchEmployees(array $searchParams): array
    {
        try {
            // Build base filters
            $filters = [];
            
            // Active status filter (default to active only)
            if ($searchParams['status'] === 'all') {
                // No filter for status
            } elseif ($searchParams['status'] === 'inactive') {
                $filters['is_active'] = false;
            } else {
                $filters['is_active'] = true; // Default
            }
            
            // Department filter
            if (!empty($searchParams['department'])) {
                $filters['department'] = ['operator' => 'ilike', 'value' => "%{$searchParams['department']}%"];
            }
            
            // Position filter
            if (!empty($searchParams['position'])) {
                $filters['position'] = ['operator' => 'ilike', 'value' => "%{$searchParams['position']}%"];
            }
            
            // Employment status filter
            if (!empty($searchParams['employment_status'])) {
                $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
                if (in_array($searchParams['employment_status'], $validStatuses)) {
                    $filters['employment_status'] = $searchParams['employment_status'];
                }
            }
            
            // Date hired range filters (simplified - would need more complex handling for ranges)
            if (!empty($searchParams['date_hired_from'])) {
                $filters['date_hired'] = ['operator' => 'gte', 'value' => $searchParams['date_hired_from']];
            }
            
            if (!empty($searchParams['date_hired_to'])) {
                if (!isset($filters['date_hired'])) {
                    $filters['date_hired'] = ['operator' => 'lte', 'value' => $searchParams['date_hired_to']];
                }
            }
            
            // Build options
            $options = [
                'limit' => $searchParams['limit'],
                'offset' => $searchParams['offset'],
                'order' => "{$searchParams['sort_by']}.{$searchParams['sort_order']}"
            ];
            
            // Get employees from database
            $employees = $this->employeeModel->all($filters, [], $searchParams['limit'], $searchParams['offset']);
            
            // Apply additional filters that couldn't be handled at database level
            if (!empty($searchParams['date_hired_from']) && !empty($searchParams['date_hired_to'])) {
                $employees = $this->applyDateRangeFilter($employees, $searchParams['date_hired_from'], $searchParams['date_hired_to']);
            }
            
            // Apply text search filter with enhanced matching
            if (!empty($searchParams['query'])) {
                $employees = $this->applySearchFilter($employees, $searchParams['query']);
            }
            
            // Get total count for the search
            $totalCount = count($employees);
            
            // Apply pagination to filtered results
            $paginatedEmployees = array_slice($employees, $searchParams['offset'], $searchParams['limit']);
            
            // Format employee data
            $formattedEmployees = array_map([$this, 'formatEmployeeData'], $paginatedEmployees);
            
            // Prepare search metadata
            $searchMeta = [
                'query' => $searchParams['query'],
                'department' => $searchParams['department'],
                'position' => $searchParams['position'],
                'status' => $searchParams['status'],
                'employment_status' => $searchParams['employment_status'],
                'date_hired_from' => $searchParams['date_hired_from'] ?? '',
                'date_hired_to' => $searchParams['date_hired_to'] ?? '',
                'sort_by' => $searchParams['sort_by'],
                'sort_order' => $searchParams['sort_order']
            ];
            
            return [
                'employees' => $formattedEmployees,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $searchParams['limit'],
                    'offset' => $searchParams['offset'],
                    'has_more' => ($searchParams['offset'] + $searchParams['limit']) < $totalCount,
                    'current_page' => floor($searchParams['offset'] / $searchParams['limit']) + 1,
                    'total_pages' => ceil($totalCount / $searchParams['limit'])
                ],
                'search' => $searchMeta
            ];
            
        } catch (Exception $e) {
            error_log('EmployeeService::searchEmployees Error: ' . $e->getMessage());
            throw new Exception('Failed to search employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Get employee profile (for self-service or admin)
     *
     * @param array $user Authenticated user data
     * @param string|null $employeeId Specific employee ID (for admin)
     * @return array Employee profile data
     */
    public function getEmployeeProfile(array $user, ?string $employeeId = null): array
    {
        try {
            if ($user['role'] === 'admin' && !empty($employeeId)) {
                // Admin getting specific employee profile
                $employee = $this->employeeModel->find($employeeId);
                
                if (!$employee) {
                    throw new NotFoundException('Employee not found');
                }
            } else {
                // Employee getting their own profile
                // Auth payload uses database employee ID in $user['id']
                $employee = $this->employeeModel->find($user['id']);
                
                if (!$employee) {
                    throw new NotFoundException('Employee profile not found');
                }
            }
            
            // Get manager information if available
            $managerName = null;
            if (!empty($employee['manager_id'])) {
                $manager = $this->employeeModel->find($employee['manager_id']);
                if ($manager) {
                    $managerName = $manager['first_name'] . ' ' . $manager['last_name'];
                }
            }
            
            // Format profile data
            $profileData = $this->formatEmployeeData($employee);
            $profileData['manager_name'] = $managerName;
            
            return $profileData;
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::getEmployeeProfile Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Update employee profile (for self-service or admin)
     *
     * @param array $user Authenticated user data
     * @param array $data Profile update data
     * @return array Updated profile data
     */
    public function updateEmployeeProfile(array $user, array $data): array
    {
        try {
            if ($user['role'] === 'admin' && !empty($data['id'])) {
                // Admin updating specific employee
                $employeeId = $data['id'];
                $existingEmployee = $this->employeeModel->find($employeeId);
                
                if (!$existingEmployee) {
                    throw new NotFoundException('Employee not found');
                }
            } else {
                // Employee updating their own profile
                // Auth payload uses database employee ID in $user['id']
                $existingEmployee = $this->employeeModel->find($user['id']);
                
                if (!$existingEmployee) {
                    throw new NotFoundException('Employee profile not found');
                }
                
                $employeeId = $existingEmployee['id'];
            }
            
            // Determine which fields can be updated based on user role
            $updateData = [];
            
            if ($user['role'] === 'admin') {
                // Admins can update most fields (except employee_id)
                $allowedFields = [
                    'first_name', 'last_name', 'work_email', 'mobile_number',
                    'department', 'position', 'employment_status', 'date_hired', 
                    'manager_id', 'is_active'
                ];
            } else {
                // Employees can only update limited fields
                $allowedFields = ['mobile_number'];
            }
            
            // Build update data with only allowed fields
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            // Validate that employee ID is not being changed
            if (isset($data['employee_id']) && $data['employee_id'] !== $existingEmployee['employee_id']) {
                throw new ValidationException('Employee ID cannot be modified', ['employee_id' => 'Employee ID cannot be modified']);
            }
            
            // Validate updated data if any fields are being changed
            if (!empty($updateData)) {
                $this->validateEmployeeData($updateData, $employeeId);
                
                // Additional validations for admin updates
                if ($user['role'] === 'admin') {
                    $this->validateAdminProfileUpdate($updateData);
                }
                
                // Clean up data
                $this->cleanUpdateData($updateData);
                
                // Update employee record
                $success = $this->employeeModel->update($employeeId, $updateData);
                
                if (!$success) {
                    throw new Exception('Failed to update profile');
                }
            }
            
            // Get updated employee data
            $updatedEmployee = $this->employeeModel->find($employeeId);
            
            return $this->formatEmployeeData($updatedEmployee);
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('EmployeeService::updateEmployeeProfile Error: ' . $e->getMessage());
            throw new Exception('Failed to update profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Apply search filter to employees array
     *
     * @param array $employees Employees array
     * @param string $search Search query
     * @return array Filtered employees
     */
    private function applySearchFilter(array $employees, string $search): array
    {
        $search = strtolower($search);
        
        return array_filter($employees, function($employee) use ($search) {
            $searchFields = [
                $employee['employee_id'],
                $employee['first_name'],
                $employee['last_name'],
                $employee['work_email'],
                $employee['department'],
                $employee['position'],
                $employee['mobile_number'],
                $employee['employment_status']
            ];
            
            $searchText = strtolower(implode(' ', array_filter($searchFields)));
            
            // Support multiple search terms
            $searchTerms = explode(' ', $search);
            foreach ($searchTerms as $term) {
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
     * @param string $dateFrom Start date
     * @param string $dateTo End date
     * @return array Filtered employees
     */
    private function applyDateRangeFilter(array $employees, string $dateFrom, string $dateTo): array
    {
        return array_filter($employees, function($employee) use ($dateFrom, $dateTo) {
            if (empty($employee['date_hired'])) {
                return false;
            }
            
            $hiredDate = strtotime($employee['date_hired']);
            $fromDate = strtotime($dateFrom);
            $toDate = strtotime($dateTo);
            
            return $hiredDate >= $fromDate && $hiredDate <= $toDate;
        });
    }
    
    /**
     * Format employee data for API response
     *
     * @param array $employee Raw employee data
     * @return array Formatted employee data
     */
    private function formatEmployeeData(array $employee): array
    {
        return [
            'id' => $employee['id'],
            'employee_id' => $employee['employee_id'],
            'first_name' => $employee['first_name'],
            'last_name' => $employee['last_name'],
            'full_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'work_email' => $employee['work_email'],
            'mobile_number' => $employee['mobile_number'],
            'department' => $employee['department'],
            'position' => $employee['position'],
            'employment_status' => $employee['employment_status'],
            'date_hired' => $employee['date_hired'],
            'manager_id' => $employee['manager_id'] ?? null,
            'is_active' => $employee['is_active'],
            'status_label' => $employee['is_active'] ? 'Active' : 'Inactive',
            'created_at' => $employee['created_at'],
            'updated_at' => $employee['updated_at']
        ];
    }
    
    /**
     * Validate required fields
     *
     * @param array $data Input data
     * @param array $requiredFields Required field names
     * @throws ValidationException If validation fails
     */
    private function validateRequiredFields(array $data, array $requiredFields): void
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
    
    /**
     * Validate employee data
     *
     * @param array $data Employee data
     * @param string|null $employeeId Employee ID for updates
     * @throws ValidationException If validation fails
     */
    private function validateEmployeeData(array $data, ?string $employeeId = null): void
    {
        $errors = [];
        
        // Email validation
        if (isset($data['work_email']) && !filter_var($data['work_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['work_email'] = 'Invalid email format';
        }
        
        // Name length validation
        if (isset($data['first_name']) && (strlen($data['first_name']) < 2 || strlen($data['first_name']) > 100)) {
            $errors['first_name'] = 'First name must be between 2 and 100 characters';
        }
        
        if (isset($data['last_name']) && (strlen($data['last_name']) < 2 || strlen($data['last_name']) > 100)) {
            $errors['last_name'] = 'Last name must be between 2 and 100 characters';
        }
        
        // Employee ID validation
        if (isset($data['employee_id']) && (strlen($data['employee_id']) < 3 || strlen($data['employee_id']) > 20)) {
            $errors['employee_id'] = 'Employee ID must be between 3 and 20 characters';
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
        
        // Check for duplicate employee ID and email (excluding current employee for updates)
        if (isset($data['employee_id'])) {
            $existing = $this->employeeModel->where(['employee_id' => $data['employee_id']])->first();
            if ($existing && ($employeeId === null || $existing['id'] !== $employeeId)) {
                $errors['employee_id'] = 'Employee ID already exists';
            }
        }
        
        if (isset($data['work_email'])) {
            $existing = $this->employeeModel->where(['work_email' => $data['work_email']])->first();
            if ($existing && ($employeeId === null || $existing['id'] !== $employeeId)) {
                $errors['work_email'] = 'Work email already exists';
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
    
    /**
     * Validate admin profile update data
     *
     * @param array $data Update data
     * @throws ValidationException If validation fails
     */
    private function validateAdminProfileUpdate(array $data): void
    {
        $errors = [];
        
        // Validate employment status if provided
        if (isset($data['employment_status'])) {
            $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
            if (!in_array($data['employment_status'], $validStatuses)) {
                $errors['employment_status'] = 'Invalid employment status';
            }
        }
        
        // Validate date hired if provided
        if (isset($data['date_hired']) && !empty($data['date_hired'])) {
            $dateHired = new \DateTime($data['date_hired']);
            $today = new \DateTime();
            
            if ($dateHired > $today) {
                $errors['date_hired'] = 'Date hired cannot be in the future';
            }
        }
        
        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
    
    /**
     * Clean update data
     *
     * @param array &$updateData Update data (passed by reference)
     */
    private function cleanUpdateData(array &$updateData): void
    {
        if (isset($updateData['work_email'])) {
            $updateData['work_email'] = strtolower(trim($updateData['work_email']));
        }
        
        if (isset($updateData['first_name'])) {
            $updateData['first_name'] = trim($updateData['first_name']);
        }
        
        if (isset($updateData['last_name'])) {
            $updateData['last_name'] = trim($updateData['last_name']);
        }
        
        if (isset($updateData['mobile_number'])) {
            $updateData['mobile_number'] = !empty($updateData['mobile_number']) 
                ? trim($updateData['mobile_number']) 
                : null;
        }
        
        if (isset($updateData['department'])) {
            $updateData['department'] = trim($updateData['department']);
        }
        
        if (isset($updateData['position'])) {
            $updateData['position'] = trim($updateData['position']);
        }
    }
    
    /**
     * Check employee dependencies before deletion
     *
     * @param string $employeeId Employee ID
     * @return array List of dependencies
     */
    private function checkEmployeeDependencies(string $employeeId): array
    {
        $dependencies = [];
        
        try {
            // Check for active attendance records (last 30 days)
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $recentAttendance = $this->makeSupabaseRequest(
                $this->config['tables']['attendance'] . '?employee_id=eq.' . $employeeId . '&date=gte.' . $thirtyDaysAgo,
                'GET',
                null,
                true
            );
            
            if ($recentAttendance['success'] && !empty($recentAttendance['data'])) {
                $dependencies[] = 'Recent attendance records found';
            }
            
            // Check for pending leave requests
            $pendingLeave = $this->makeSupabaseRequest(
                $this->config['tables']['leave_requests'] . '?employee_id=eq.' . $employeeId . '&status=eq.Pending',
                'GET',
                null,
                true
            );
            
            if ($pendingLeave['success'] && !empty($pendingLeave['data'])) {
                $dependencies[] = 'Pending leave requests found';
            }
        } catch (Exception $e) {
            error_log('Error checking employee dependencies: ' . $e->getMessage());
        }
        
        return $dependencies;
    }
    
    /**
     * Generate default password from employee data
     * Format: firstname + phone (e.g., Juan09123456789)
     *
     * @param array $employeeData Employee data
     * @return string Generated password
     */
    private function generateDefaultPasswordFromData(array $employeeData): string
    {
        $firstName = $employeeData['first_name'];
        $phone = $employeeData['mobile_number'] ?? '';
        
        // Remove any non-numeric characters from phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If no phone, use random numbers
        if (empty($phone)) {
            $phone = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        }
        
        return $firstName . $phone;
    }
    
    /**
     * Generate default password for new employees
     *
     * @return string Generated password
     */
    private function generateDefaultPassword(): string
    {
        // Generate a secure random password
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < 12; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    private function generateEmployeeId(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'EMP' . date('ymdHis') . random_int(10, 99);
            $existing = $this->employeeModel->where(['employee_id' => $candidate])->first();

            if (!$existing) {
                return $candidate;
            }
        }

        return 'EMP' . date('ymdHis') . random_int(100, 999);
    }

    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Create Supabase authentication user
     *
     * @param string $email User email
     * @param string $password User password
     * @return array Creation result
     */
    private function createSupabaseAuthUser(string $email, string $password): array
    {
        try {
            $url = $this->config['auth_url'] . 'admin/users';
            
            $data = [
                'email' => $email,
                'password' => $password,
                'email_confirm' => true
            ];
            
            $headers = [
                'Content-Type: application/json',
                'apikey: ' . $this->config['service_key'],
                'Authorization: Bearer ' . $this->config['service_key']
            ];
            
            $response = $this->makeCurlRequest($url, 'POST', $data, $headers);
            
            if ($response['success'] && isset($response['data']['id'])) {
                return [
                    'success' => true,
                    'user_id' => $response['data']['id']
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Failed to create auth user'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete Supabase authentication user
     *
     * @param string $userId Supabase user ID
     * @return array Deletion result
     */
    private function deleteSupabaseAuthUser(string $userId): array
    {
        try {
            $url = $this->config['auth_url'] . 'admin/users/' . $userId;
            
            $headers = [
                'apikey: ' . $this->config['service_key'],
                'Authorization: Bearer ' . $this->config['service_key']
            ];
            
            $response = $this->makeCurlRequest($url, 'DELETE', null, $headers);
            
            return [
                'success' => $response['success']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Initialize leave credits for new employee
     *
     * @param string $employeeId Employee ID
     */
    private function initializeLeaveCredits(string $employeeId): void
    {
        try {
            // This would typically call a LeaveService or LeaveManager
            // For now, we'll just log that it should be done
            error_log("TODO: Initialize leave credits for employee {$employeeId}");
        } catch (Exception $e) {
            error_log('Warning: Failed to initialize leave credits for employee ' . $employeeId . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Make request to Supabase REST API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param bool $useServiceKey Use service key instead of anon key
     * @return array Response
     */
    private function makeSupabaseRequest(string $endpoint, string $method = 'GET', ?array $data = null, bool $useServiceKey = false): array
    {
        $url = $this->config['api_url'] . $endpoint;
        $apiKey = $useServiceKey ? $this->config['service_key'] : $this->config['anon_key'];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey
        ];
        
        return $this->makeCurlRequest($url, $method, $data, $headers);
    }
    
    /**
     * Make HTTP request using cURL
     *
     * @param string $url Request URL
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param array $headers Request headers
     * @return array Response
     */
    private function makeCurlRequest(string $url, string $method, ?array $data, array $headers): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'] ?? 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->config['ssl_verify'] ?? true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'status_code' => 0
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse,
            'status_code' => $httpCode,
            'raw_response' => $response
        ];
    }
}
