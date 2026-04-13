<?php

namespace Services;

use Core\ValidationException;
use Core\NotFoundException;
use Core\SimpleCache;
use Core\PerformanceMonitor;
use Models\LeaveRequest;
use Models\Employee;
use Exception;

/**
 * LeaveService - Handles leave management business logic
 * 
 * This service encapsulates all leave-related business logic including
 * leave request submission, approval/denial, leave credit validation,
 * and business days calculation.
 */
class LeaveService
{
    private LeaveRequest $leaveRequestModel;
    private Employee $employeeModel;
    private \DateTimeZone $appTimezone;
    
    public function __construct(LeaveRequest $leaveRequestModel, Employee $employeeModel)
    {
        $this->leaveRequestModel = $leaveRequestModel;
        $this->employeeModel = $employeeModel;
        $this->appTimezone = $this->resolveTimezone();
    }
    
    /**
     * Submit a new leave request
     *
     * @param array $requestData Leave request data
     * @return array Created leave request
     */
    public function submitLeaveRequest(array $requestData): array
    {
        try {
            error_log('=== SUBMIT LEAVE REQUEST DEBUG ===');
            error_log('Request data received: ' . json_encode($requestData));
            
            // Validate required fields
            $requiredFields = ['employee_id', 'leave_type_id', 'start_date', 'end_date'];
            $this->validateRequiredFields($requestData, $requiredFields);
            
            error_log('Required fields validated');
            
            // Validate employee exists and is active
            $employee = $this->employeeModel->find($requestData['employee_id']);
            if (!$employee || !$employee['is_active']) {
                throw new NotFoundException('Employee not found or inactive');
            }
            
            error_log('Employee found: ' . $employee['first_name'] . ' ' . $employee['last_name']);
            
            // Get the actual leave type UUID from the database
            // The frontend sends numeric IDs like "1", "2", "3" but we need UUIDs
            $leaveTypes = $this->getLeaveTypes();
            $leaveTypeUuid = null;
            
            // Map numeric ID to UUID
            // Assuming: 1=Vacation, 2=Sick, 3=Emergency based on insertion order
            $leaveTypeMapping = [];
            foreach ($leaveTypes as $index => $type) {
                $leaveTypeMapping[(string)($index + 1)] = $type['id'];
            }
            
            error_log('Leave type mapping: ' . json_encode($leaveTypeMapping));
            error_log('Requested leave_type_id: ' . $requestData['leave_type_id']);
            
            if (isset($leaveTypeMapping[$requestData['leave_type_id']])) {
                $leaveTypeUuid = $leaveTypeMapping[$requestData['leave_type_id']];
                error_log('Mapped to UUID: ' . $leaveTypeUuid);
            } else {
                // If it's already a UUID format, use it directly
                if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestData['leave_type_id'])) {
                    $leaveTypeUuid = $requestData['leave_type_id'];
                    error_log('Using provided UUID: ' . $leaveTypeUuid);
                } else {
                    throw new ValidationException('Invalid leave type', [
                        'leave_type_id' => 'Invalid leave type selected'
                    ]);
                }
            }
            
            // Calculate total days using business days
            $totalDays = $this->calculateBusinessDays(
                $requestData['start_date'],
                $requestData['end_date']
            );
            
            error_log('Total business days calculated: ' . $totalDays);
            
            if ($totalDays <= 0) {
                throw new ValidationException('Invalid date range - no working days found', [
                    'date_range' => 'Invalid date range - no working days found'
                ]);
            }
            
            // Validate leave credits - check if employee has enough credits
            $this->validateLeaveCredits(
                $requestData['employee_id'],
                $leaveTypeUuid,
                $totalDays,
                $requestData['start_date']
            );
            
            // Check for overlapping leave requests
            if ($this->leaveRequestModel->hasOverlappingLeave(
                $requestData['employee_id'],
                $requestData['start_date'],
                $requestData['end_date']
            )) {
                throw new ValidationException('You have overlapping leave requests for the selected dates', [
                    'date_range' => 'You have overlapping leave requests for the selected dates'
                ]);
            }
            
            error_log('No overlapping leaves found');
            
            // Prepare leave request data
            $leaveRequest = [
                'employee_id' => $requestData['employee_id'],
                'leave_type_id' => $leaveTypeUuid, // Use the UUID instead of numeric ID
                'start_date' => $requestData['start_date'],
                'end_date' => $requestData['end_date'],
                'total_days' => $totalDays,
                'reason' => $requestData['reason'] ?? '',
                'status' => 'Pending'
            ];
            
            error_log('Leave request data to create: ' . json_encode($leaveRequest));
            
            // Create leave request
            $newRequest = $this->leaveRequestModel->create($leaveRequest);
            
            error_log('Leave request created: ' . json_encode($newRequest));
            
            if (empty($newRequest)) {
                error_log('ERROR: create() returned empty result!');
                throw new Exception('Failed to create leave request - empty result from database');
            }
            
            $formatted = $this->formatLeaveRequestData($newRequest);
            error_log('Formatted response: ' . json_encode($formatted));
            
            return $formatted;
            
        } catch (ValidationException $e) {
            error_log('Validation error: ' . $e->getMessage());
            throw $e;
        } catch (NotFoundException $e) {
            error_log('Not found error: ' . $e->getMessage());
            throw $e;
        } catch (Exception $e) {
            error_log('LeaveService::submitLeaveRequest Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to submit leave request: ' . $e->getMessage());
        }
    }
    
    /**
     * Approve a leave request
     *
     * @param string $requestId Leave request ID
     * @param string $reviewerId Admin/Manager ID who is approving
     * @return array Updated leave request
     */
    public function approveLeaveRequest(string $requestId, string $reviewerId): array
    {
        try {
            error_log('=== APPROVE LEAVE REQUEST DEBUG ===');
            error_log('Request ID: ' . $requestId);
            error_log('Reviewer ID: ' . $reviewerId);
            
            // Get the leave request details
            $leaveRequest = $this->leaveRequestModel->find($requestId);
            
            error_log('Leave request found: ' . json_encode($leaveRequest));
            
            if (!$leaveRequest) {
                throw new NotFoundException('Leave request not found');
            }
            
            // Check if request is still pending
            if ($leaveRequest['status'] !== 'Pending') {
                error_log('Leave request already processed. Current status: ' . $leaveRequest['status']);
                throw new ValidationException('Leave request has already been processed', [
                    'status' => 'Leave request has already been processed'
                ]);
            }
            
            // Prevent self-approval
            if ($leaveRequest['employee_id'] === $reviewerId) {
                throw new ValidationException('Employees cannot approve their own leave requests', [
                    'reviewer' => 'Employees cannot approve their own leave requests'
                ]);
            }
            
            // Update leave request status
            $updateData = [
                'status' => 'Approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s')
            ];
            
            error_log('Update data: ' . json_encode($updateData));
            
            $updateResult = $this->leaveRequestModel->update($requestId, $updateData);
            
            error_log('Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Get updated request
            $updatedRequest = $this->leaveRequestModel->find($requestId);
            
            error_log('Updated request: ' . json_encode($updatedRequest));
            
            $this->createLeaveAttendanceRecords($updatedRequest);
            
            return $this->formatLeaveRequestData($updatedRequest);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('LeaveService::approveLeaveRequest Error: ' . $e->getMessage());
            throw new Exception('Failed to approve leave request: ' . $e->getMessage());
        }
    }
    
    /**
     * Create attendance records for approved leave dates
     * OPTIMIZED: Batch operations instead of one-by-one queries
     *
     * @param array $leaveRequest Approved leave request
     * @return void
     */
    private function createLeaveAttendanceRecords(array $leaveRequest): void
    {
        try {
            \Core\PerformanceMonitor::start('leave_attendance_creation');
            
            // Get database connection using reflection
            $reflection = new \ReflectionClass($this->leaveRequestModel);
            $property = $reflection->getProperty('db');
            $property->setAccessible(true);
            $db = $property->getValue($this->leaveRequestModel);
            
            $startDate = new \DateTime($leaveRequest['start_date'], $this->appTimezone);
            $endDate = new \DateTime($leaveRequest['end_date'], $this->appTimezone);
            $current = clone $startDate;
            
            // ========================================
            // PERFORMANCE OPTIMIZATION: Batch Operations
            // ========================================
            // OLD: Query database for EACH date in loop (N queries)
            // NEW: Load all existing records once, then batch insert/update
            
            // 1. Collect all working days in the leave period
            $workingDays = [];
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                if ($this->isWorkingDay($dateStr)) {
                    $workingDays[] = $dateStr;
                }
                $current->add(new \DateInterval('P1D'));
            }
            
            if (empty($workingDays)) {
                error_log("No working days found for leave period");
                return;
            }
            
            // 2. Batch load ALL existing attendance records for this employee and date range (1 query)
            $existingRecords = $db->select('attendance', [
                'employee_id' => $leaveRequest['employee_id']
            ]);
            
            // Index by date for fast lookup
            $existingByDate = [];
            foreach ($existingRecords as $record) {
                $recordDate = $record['date'];
                if (in_array($recordDate, $workingDays)) {
                    $existingByDate[$recordDate] = $record;
                }
            }
            
            // 3. Prepare batch operations
            $recordsToInsert = [];
            $recordsToUpdate = [];
            
            foreach ($workingDays as $dateStr) {
                if (!isset($existingByDate[$dateStr])) {
                    // No record exists - prepare for insert
                    $recordsToInsert[] = [
                        'employee_id' => $leaveRequest['employee_id'],
                        'date' => $dateStr,
                        'time_in' => null,
                        'time_out' => null,
                        'status' => 'On Leave',
                        'work_hours' => 0.00,
                        'remarks' => 'On approved leave (Leave ID: ' . $leaveRequest['id'] . ')'
                    ];
                } else {
                    // Record exists - check if we need to update
                    $existingRecord = $existingByDate[$dateStr];
                    $hasTimeLogs = !empty($existingRecord['time_in']) || !empty($existingRecord['time_out']);
                    $existingStatus = $existingRecord['status'] ?? '';
                    
                    if (!$hasTimeLogs && $existingStatus !== 'On Leave') {
                        $recordsToUpdate[] = [
                            'id' => $existingRecord['id'],
                            'status' => 'On Leave',
                            'work_hours' => 0.00,
                            'remarks' => 'On approved leave (Leave ID: ' . $leaveRequest['id'] . ')',
                            'time_in' => null,
                            'time_out' => null
                        ];
                    }
                }
            }
            
            // 4. Execute batch operations
            $createdRecords = 0;
            
            // Batch insert new records (insert one by one since Supabase doesn't support bulk insert via REST)
            // But at least we're not checking existence for each one
            foreach ($recordsToInsert as $record) {
                $insertResult = $db->insert('attendance', $record);
                if (!empty($insertResult)) {
                    $createdRecords++;
                }
            }
            
            // Batch update existing records
            foreach ($recordsToUpdate as $record) {
                $recordId = $record['id'];
                unset($record['id']);
                $db->update('attendance', $record, ['id' => $recordId]);
                $createdRecords++;
            }
            
            $duration = \Core\PerformanceMonitor::end('leave_attendance_creation');
            error_log("Created/updated {$createdRecords} attendance records for leave (Leave ID: {$leaveRequest['id']}) in {$duration}ms");
            
        } catch (Exception $e) {
            // Log error but don't fail the approval
            error_log('LeaveService::createLeaveAttendanceRecords Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Deny a leave request
     *
     * @param string $requestId Leave request ID
     * @param string $reviewerId Admin/Manager ID who is denying
     * @param string $denialReason Reason for denial
     * @return array Updated leave request
     */
    public function denyLeaveRequest(string $requestId, string $reviewerId, string $denialReason = ''): array
    {
        try {
            // Get the leave request details
            $leaveRequest = $this->leaveRequestModel->find($requestId);
            
            if (!$leaveRequest) {
                throw new NotFoundException('Leave request not found');
            }
            
            // Check if request is still pending
            if ($leaveRequest['status'] !== 'Pending') {
                throw new ValidationException('Leave request has already been processed', [
                    'status' => 'Leave request has already been processed'
                ]);
            }
            
            // Prevent self-denial
            if ($leaveRequest['employee_id'] === $reviewerId) {
                throw new ValidationException('Employees cannot review their own leave requests', [
                    'reviewer' => 'Employees cannot review their own leave requests'
                ]);
            }
            
            // Update leave request status
            $updateData = [
                'status' => 'Denied',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'denial_reason' => $denialReason
            ];
            
            $this->leaveRequestModel->update($requestId, $updateData);
            
            // Get updated request
            $updatedRequest = $this->leaveRequestModel->find($requestId);
            
            return $this->formatLeaveRequestData($updatedRequest);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('LeaveService::denyLeaveRequest Error: ' . $e->getMessage());
            throw new Exception('Failed to deny leave request: ' . $e->getMessage());
        }
    }
    
    /**
     * Get pending leave requests
     *
     * @return array Pending leave requests with employee information
     */
    public function getPendingLeaveRequests(): array
    {
        try {
            $requests = $this->leaveRequestModel->getPending();
            
            // Enrich with employee information
            $enrichedRequests = [];
            
            foreach ($requests as $request) {
                $employee = $this->employeeModel->find($request['employee_id']);
                
                if ($employee) {
                    $enrichedRequest = $this->formatLeaveRequestData($request);
                    $enrichedRequest['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                    $enrichedRequest['employee_number'] = $employee['employee_id'];
                    $enrichedRequest['department'] = $employee['department'];
                    $enrichedRequest['position'] = $employee['position'];
                    
                    $enrichedRequests[] = $enrichedRequest;
                }
            }
            
            return $enrichedRequests;
            
        } catch (Exception $e) {
            error_log('LeaveService::getPendingLeaveRequests Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve pending leave requests: ' . $e->getMessage());
        }
    }
    
    /**
     * Get approved leave requests with employee information
     * @return array Approved leave requests
     */
    public function getApprovedLeaveRequests(): array
    {
        try {
            $requests = $this->leaveRequestModel->where(['status' => 'Approved'])->get();
            
            // Sort by updated_at descending
            usort($requests, function($a, $b) {
                $dateA = $a['reviewed_at'] ?? $a['updated_at'] ?? '';
                $dateB = $b['reviewed_at'] ?? $b['updated_at'] ?? '';
                return strcmp($dateB, $dateA);
            });
            
            // Enrich with employee information
            $enrichedRequests = [];
            
            foreach ($requests as $request) {
                $employee = $this->employeeModel->find($request['employee_id']);
                
                if ($employee) {
                    $enrichedRequest = $this->formatLeaveRequestData($request);
                    $enrichedRequest['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                    $enrichedRequest['employee_number'] = $employee['employee_id'];
                    $enrichedRequest['department'] = $employee['department'];
                    $enrichedRequest['position'] = $employee['position'];
                    
                    $enrichedRequests[] = $enrichedRequest;
                }
            }
            
            return $enrichedRequests;
            
        } catch (Exception $e) {
            error_log('LeaveService::getApprovedLeaveRequests Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve approved leave requests: ' . $e->getMessage());
        }
    }
    
    /**
     * Get denied leave requests with employee information
     * @return array Denied leave requests
     */
    public function getDeniedLeaveRequests(): array
    {
        try {
            $requests = $this->leaveRequestModel->where(['status' => 'Denied'])->get();
            
            // Sort by updated_at descending
            usort($requests, function($a, $b) {
                $dateA = $a['reviewed_at'] ?? $a['updated_at'] ?? '';
                $dateB = $b['reviewed_at'] ?? $b['updated_at'] ?? '';
                return strcmp($dateB, $dateA);
            });
            
            // Enrich with employee information
            $enrichedRequests = [];
            
            foreach ($requests as $request) {
                $employee = $this->employeeModel->find($request['employee_id']);
                
                if ($employee) {
                    $enrichedRequest = $this->formatLeaveRequestData($request);
                    $enrichedRequest['employee_name'] = $employee['first_name'] . ' ' . $employee['last_name'];
                    $enrichedRequest['employee_number'] = $employee['employee_id'];
                    $enrichedRequest['department'] = $employee['department'];
                    $enrichedRequest['position'] = $employee['position'];
                    
                    $enrichedRequests[] = $enrichedRequest;
                }
            }
            
            return $enrichedRequests;
            
        } catch (Exception $e) {
            error_log('LeaveService::getDeniedLeaveRequests Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve denied leave requests: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all leave requests with optional filters
     * @param string|null $search Search term for employee name
     * @param string|null $status Filter by status
     * @param string|null $leaveType Filter by leave type
     * @return array Filtered leave requests grouped by status
     */
    public function getAllLeaveRequests(?string $search = null, ?string $status = null, ?string $leaveType = null): array
    {
        try {
            $pending = [];
            $approved = [];
            $denied = [];
            
            // Get requests based on status filter
            if (!$status || $status === 'Pending') {
                $pending = $this->getPendingLeaveRequests();
            }
            if (!$status || $status === 'Approved') {
                $approved = $this->getApprovedLeaveRequests();
            }
            if (!$status || $status === 'Denied') {
                $denied = $this->getDeniedLeaveRequests();
            }
            
            // Apply search filter
            if ($search) {
                $pending = array_filter($pending, function($req) use ($search) {
                    return stripos($req['employee_name'], $search) !== false;
                });
                $approved = array_filter($approved, function($req) use ($search) {
                    return stripos($req['employee_name'], $search) !== false;
                });
                $denied = array_filter($denied, function($req) use ($search) {
                    return stripos($req['employee_name'], $search) !== false;
                });
            }
            
            // Apply leave type filter
            if ($leaveType) {
                $pending = array_filter($pending, function($req) use ($leaveType) {
                    return $req['leave_type_id'] === $leaveType;
                });
                $approved = array_filter($approved, function($req) use ($leaveType) {
                    return $req['leave_type_id'] === $leaveType;
                });
                $denied = array_filter($denied, function($req) use ($leaveType) {
                    return $req['leave_type_id'] === $leaveType;
                });
            }
            
            return [
                'pending' => array_values($pending),
                'approved' => array_values($approved),
                'denied' => array_values($denied)
            ];
            
        } catch (Exception $e) {
            error_log('LeaveService::getAllLeaveRequests Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve leave requests: ' . $e->getMessage());
        }
    }
    
    /**
     * Get leave request history for an employee
     *
     * @param string $employeeId Employee ID
     * @param int $limit Maximum records to return
     * @param int $offset Offset for pagination
     * @return array Leave request history
     */
    public function getLeaveHistory(string $employeeId, int $limit = 50, int $offset = 0): array
    {
        try {
            $requests = $this->leaveRequestModel->getByEmployee($employeeId);
            
            // Sort by created_at descending
            usort($requests, function($a, $b) {
                return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
            });
            
            // Apply pagination
            $paginatedRequests = array_slice($requests, $offset, $limit);
            
            // Format requests
            $formattedRequests = array_map([$this, 'formatLeaveRequestData'], $paginatedRequests);
            
            return [
                'employee_id' => $employeeId,
                'total_records' => count($requests),
                'limit' => $limit,
                'offset' => $offset,
                'requests' => $formattedRequests
            ];
            
        } catch (Exception $e) {
            error_log('LeaveService::getLeaveHistory Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve leave history: ' . $e->getMessage());
        }
    }
    
    /**
     * Get leave balance for an employee
     * 
     * @param string $employeeId Employee ID
     * @return array Leave balance by type
     */
    public function getLeaveBalance(string $employeeId): array
    {
        try {
            // Get database connection using reflection
            $reflection = new \ReflectionClass($this->leaveRequestModel);
            $property = $reflection->getProperty('db');
            $property->setAccessible(true);
            $db = $property->getValue($this->leaveRequestModel);
            
            // Get current year
            $currentYear = (int) date('Y');
            
            // Get leave credits for this employee and year
            $leaveCredits = $db->select('leave_credits', [
                'employee_id' => $employeeId,
                'year' => $currentYear
            ], [
                'select' => '*'
            ]);
            
            if (empty($leaveCredits)) {
                error_log("No leave credits found for employee {$employeeId} for year {$currentYear}");
                return [];
            }
            
            // Get leave types to map names
            $leaveTypes = $db->select('leave_types', [], ['select' => '*']);
            $leaveTypeMap = [];
            foreach ($leaveTypes as $type) {
                $leaveTypeMap[$type['id']] = $type['name'];
            }
            
            // Format the response with leave type names
            $result = [];
            foreach ($leaveCredits as $credit) {
                $result[] = [
                    'leave_type' => $leaveTypeMap[$credit['leave_type_id']] ?? 'Unknown',
                    'total_credits' => (int) $credit['total_credits'],
                    'used_credits' => (int) $credit['used_credits'],
                    'remaining_credits' => (int) $credit['remaining_credits'],
                    'year' => (int) $credit['year']
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('LeaveService::getLeaveBalance Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to retrieve leave balance: ' . $e->getMessage());
        }
    }
    
    /**
     * Get detailed leave credits for an employee
     * 
     * @param string $employeeId Employee ID
     * @return array Leave credits with type information
     */
    public function getLeaveCredits(string $employeeId): array
    {
        try {
            // Get database connection using reflection
            $reflection = new \ReflectionClass($this->leaveRequestModel);
            $property = $reflection->getProperty('db');
            $property->setAccessible(true);
            $db = $property->getValue($this->leaveRequestModel);
            
            // Get current year
            $currentYear = (int) date('Y');
            
            // Get leave credits for this employee and year
            $leaveCredits = $db->select('leave_credits', [
                'employee_id' => $employeeId,
                'year' => $currentYear
            ], [
                'select' => '*'
            ]);
            
            if (empty($leaveCredits)) {
                error_log("No leave credits found for employee {$employeeId} for year {$currentYear}");
                return [];
            }
            
            // Get leave types to enrich the data
            $leaveTypes = $db->select('leave_types', [], ['select' => '*']);
            $leaveTypeMap = [];
            foreach ($leaveTypes as $type) {
                $leaveTypeMap[$type['id']] = $type;
            }
            
            // Enrich leave credits with type information
            $result = [];
            foreach ($leaveCredits as $credit) {
                $leaveType = $leaveTypeMap[$credit['leave_type_id']] ?? null;
                $result[] = [
                    'id' => $credit['id'],
                    'employee_id' => $credit['employee_id'],
                    'leave_type_id' => $credit['leave_type_id'],
                    'leave_type_name' => $leaveType['name'] ?? 'Unknown',
                    'leave_type_description' => $leaveType['description'] ?? '',
                    'total_credits' => (int) $credit['total_credits'],
                    'used_credits' => (int) $credit['used_credits'],
                    'remaining_credits' => (int) $credit['remaining_credits'],
                    'year' => (int) $credit['year'],
                    'created_at' => $credit['created_at'],
                    'updated_at' => $credit['updated_at']
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('LeaveService::getLeaveCredits Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw new Exception('Failed to retrieve leave credits: ' . $e->getMessage());
        }
    }
    
    /**
     * Get available leave types
     * 
     * @return array Leave types
     */
    public function getLeaveTypes(): array
    {
        // Cache leave types for 5 minutes (they rarely change)
        return SimpleCache::remember('leave_types', function() {
            PerformanceMonitor::start('get_leave_types');
            
            try {
                // Get database connection using reflection
                $reflection = new \ReflectionClass($this->leaveRequestModel);
                $property = $reflection->getProperty('db');
                $property->setAccessible(true);
                $db = $property->getValue($this->leaveRequestModel);
                
                // Query leave types from database
                $result = $db->select('leave_types', []);
                
                error_log('getLeaveTypes result: ' . json_encode($result));
                
                // SupabaseConnection::select returns array directly, not wrapped in success/data
                if (is_array($result) && !empty($result)) {
                    return $result;
                }
                
                // If no leave types in database, return default types with string IDs for backward compatibility
                return [
                    [
                        'id' => '1',
                        'name' => 'Vacation Leave',
                        'description' => 'Paid time off for vacation',
                        'max_days_per_year' => 15,
                        'requires_approval' => true,
                        'is_active' => true
                    ],
                    [
                        'id' => '2',
                        'name' => 'Sick Leave',
                        'description' => 'Paid time off for illness',
                        'max_days_per_year' => 15,
                    'requires_approval' => true,
                    'is_active' => true
                ],
                [
                    'id' => '3',
                    'name' => 'Emergency Leave',
                    'description' => 'Paid time off for emergencies',
                    'max_days_per_year' => 5,
                    'requires_approval' => true,
                    'is_active' => true
                ]
            ];
            } catch (Exception $e) {
                error_log('LeaveService::getLeaveTypes Error: ' . $e->getMessage());
                throw new Exception('Failed to retrieve leave types: ' . $e->getMessage());
            } finally {
                PerformanceMonitor::end('get_leave_types');
            }
        }, 300); // Cache for 5 minutes
    }
    
    /**
     * Validate if employee has sufficient leave credits
     *
     * @param string $employeeId Employee ID
     * @param string $leaveTypeId Leave type UUID
     * @param float $daysRequested Number of days requested
     * @param string $startDate Start date of leave
     * @throws ValidationException If insufficient credits
     */
    private function validateLeaveCredits(string $employeeId, string $leaveTypeId, float $daysRequested, string $startDate): void
    {
        try {
            // Get database connection using reflection
            $reflection = new \ReflectionClass($this->leaveRequestModel);
            $property = $reflection->getProperty('db');
            $property->setAccessible(true);
            $db = $property->getValue($this->leaveRequestModel);
            
            // Get the year from start date
            $year = (int) date('Y', strtotime($startDate));
            
            error_log('validateLeaveCredits - Employee: ' . $employeeId . ', LeaveType: ' . $leaveTypeId . ', Year: ' . $year);
            
            // Get leave credits for this employee, leave type, and year
            $leaveCredits = $db->select('leave_credits', [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year
            ]);
            
            error_log('validateLeaveCredits - Result: ' . json_encode($leaveCredits));
            
            // If no leave credits record found, employee cannot file leave
            if (empty($leaveCredits)) {
                throw new ValidationException('Leave credits not initialized for this employee', [
                    'leave_credits' => 'Leave credits not found. Please contact HR to initialize your leave credits.'
                ]);
            }
            
            $credits = $leaveCredits[0];
            
            // Get leave type name
            $leaveType = $db->find('leave_types', $leaveTypeId);
            $leaveTypeName = $leaveType['name'] ?? 'Leave';
            
            $remainingCredits = (float) $credits['remaining_credits'];
            
            error_log("validateLeaveCredits - Remaining: {$remainingCredits}, Requested: {$daysRequested}");
            
            // Check if employee has enough remaining credits
            if ($remainingCredits < $daysRequested) {
                throw new ValidationException('Insufficient leave credits', [
                    'leave_credits' => sprintf(
                        'Insufficient %s credits. You have %.1f days remaining but requested %.1f days.',
                        $leaveTypeName,
                        $remainingCredits,
                        $daysRequested
                    )
                ]);
            }
            
            error_log('validateLeaveCredits - Validation passed');
            
        } catch (ValidationException $e) {
            // Re-throw validation exceptions
            throw $e;
        } catch (Exception $e) {
            error_log('LeaveService::validateLeaveCredits Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            // Don't block leave request if validation check fails due to technical error
            // Just log the error and continue
            error_log('WARNING: Leave credits validation failed due to technical error, allowing request to proceed');
        }
    }
    
    /**
     * Calculate business days between two dates (excluding weekends)
     *
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @return float Number of business days
     */
    private function calculateBusinessDays(string $startDate, string $endDate): float
    {
        try {
            $start = new \DateTime($startDate, $this->appTimezone);
            $end = new \DateTime($endDate, $this->appTimezone);
            
            // Ensure start date is not after end date
            if ($start > $end) {
                return 0;
            }
            
            $businessDays = 0;
            $current = clone $start;
            
            // Iterate through each day in the range
            while ($current <= $end) {
                $dateStr = $current->format('Y-m-d');
                
                // Check if it's a working day (Monday-Friday)
                if ($this->isWorkingDay($dateStr)) {
                    $businessDays++;
                }
                
                $current->add(new \DateInterval('P1D'));
            }
            
            return $businessDays;
            
        } catch (Exception $e) {
            error_log('LeaveService::calculateBusinessDays Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if a date is a working day
     *
     * @param string $date Date in Y-m-d format
     * @return bool True if working day
     */
    private function isWorkingDay(string $date): bool
    {
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d|', $date, $this->appTimezone);
        if (!$dateObj) {
            return false;
        }
        $dayOfWeek = (int) $dateObj->format('w');
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }

    private function resolveTimezone(): \DateTimeZone
    {
        $timezoneName = 'Asia/Manila';
        if (function_exists('config')) {
            $configured = config('app.timezone', 'Asia/Manila');
            if (is_string($configured) && $configured !== '') {
                $timezoneName = $configured;
            }
        }
        try {
            return new \DateTimeZone($timezoneName);
        } catch (\Exception $e) {
            return new \DateTimeZone('Asia/Manila');
        }
    }
    
    /**
     * Validate required fields
     *
     * @param array $data Data to validate
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
     * Format leave request data for API response
     *
     * @param array $leaveRequest Raw leave request data
     * @return array Formatted leave request data
     */
    private function formatLeaveRequestData(array $leaveRequest): array
    {
        return [
            'id' => $leaveRequest['id'] ?? null,
            'employee_id' => $leaveRequest['employee_id'] ?? null,
            'leave_type_id' => $leaveRequest['leave_type_id'] ?? null,
            'start_date' => $leaveRequest['start_date'] ?? null,
            'end_date' => $leaveRequest['end_date'] ?? null,
            'total_days' => isset($leaveRequest['total_days']) ? floatval($leaveRequest['total_days']) : 0,
            'reason' => $leaveRequest['reason'] ?? '',
            'status' => $leaveRequest['status'] ?? 'Pending',
            'reviewed_by' => $leaveRequest['reviewed_by'] ?? null,
            'reviewed_at' => $leaveRequest['reviewed_at'] ?? null,
            'denial_reason' => $leaveRequest['denial_reason'] ?? null,
            'created_at' => $leaveRequest['created_at'] ?? null,
            'updated_at' => $leaveRequest['updated_at'] ?? null
        ];
    }
}
