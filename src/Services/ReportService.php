<?php

namespace Services;

use Core\SupabaseConnection;

/**
 * ReportService - Business logic for report generation
 * 
 * This service encapsulates report generation logic for attendance, leave,
 * and headcount reports with consistent formatting and data aggregation.
 * 
 * Requirements: 4.1, 4.3, 4.6
 */
class ReportService
{
    private SupabaseConnection $db;
    
    public function __construct()
    {
        // Load Supabase config to define TABLE_* constants
        require_once dirname(__DIR__, 2) . '/config/supabase.php';
        
        // Initialize database connection
        $this->db = new SupabaseConnection();
    }
    
    /**
     * Generate attendance summary report
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param array $filters Optional filters (employee_id, department, status)
     * @return array Report data
     * @throws \Exception If report generation fails
     */
    public function generateAttendanceReport(string $startDate, string $endDate, array $filters = []): array
    {
        // Validate date range
        if (strtotime($startDate) > strtotime($endDate)) {
            throw new \Exception('Start date must be before end date');
        }
        
        // Load database helper (not needed - using models instead)
        // Database connection is handled by models
        
        // Build query conditions
        $conditions = [
            'date' => [
                'operator' => 'between',
                'value' => [$startDate, $endDate]
            ]
        ];
        
        // Add filters
        if (!empty($filters['employee_id'])) {
            $conditions['employee_id'] = $filters['employee_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions['status'] = $filters['status'];
        }
        
        // Get attendance records
        $records = $this->db->select(TABLE_ATTENDANCE, $conditions);
        
        // If no records found, return empty report instead of throwing exception
        if (empty($records)) {
            return [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'filters' => $filters,
                'summary' => [
                    'total_records' => 0,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'total_hours' => 0,
                    'average_hours' => 0
                ],
                'records' => [],
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Filter by department if specified
        if (!empty($filters['department'])) {
            $records = array_filter($records, function($record) use ($filters) {
                $employee = $this->db->find(TABLE_EMPLOYEES, $record['employee_id']);
                return $employee && $employee['department'] === $filters['department'];
            });
        }
        
        // Calculate summary statistics
        $summary = $this->calculateAttendanceSummary($records);
        
        // Get employee details for each record
        $detailedRecords = $this->enrichAttendanceRecords($records);
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'filters' => $filters,
            'summary' => $summary,
            'records' => $detailedRecords,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate leave summary report
     * 
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param array $filters Optional filters (employee_id, department, leave_type_id, status)
     * @return array Report data
     * @throws \Exception If report generation fails
     */
    public function generateLeaveReport(string $startDate, string $endDate, array $filters = []): array
    {
        // Validate date range
        if (strtotime($startDate) > strtotime($endDate)) {
            throw new \Exception('Start date must be before end date');
        }
        
        // Database connection handled by models
        
        // Build query conditions - get all leave requests and filter in PHP
        // This is more reliable than date range queries with PostgREST
        $conditions = [];
        
        // Add filters
        if (!empty($filters['employee_id'])) {
            $conditions['employee_id'] = $filters['employee_id'];
        }
        
        if (!empty($filters['leave_type_id'])) {
            $conditions['leave_type_id'] = $filters['leave_type_id'];
        }
        
        if (!empty($filters['status'])) {
            $conditions['status'] = $filters['status'];
        }
        
        // Debug logging
        error_log("Leave Report - Date Range: $startDate to $endDate");
        error_log("Leave Report - Conditions: " . json_encode($conditions));
        
        // Get all leave requests (or filtered by status/type)
        $allRequests = $this->db->select(TABLE_LEAVE_REQUESTS, $conditions);
        
        error_log("Leave Report - Found " . count($allRequests) . " total requests before date filtering");
        
        // Filter by date range in PHP (more reliable than PostgREST date operators)
        $requests = array_filter($allRequests, function($request) use ($startDate, $endDate) {
            $reqStartDate = $request['start_date'] ?? '';
            $reqEndDate = $request['end_date'] ?? '';
            
            // Debug: log the dates being compared
            error_log("Comparing: request[$reqStartDate to $reqEndDate] vs report[$startDate to $endDate]");
            
            // Extract just the date part if it's a timestamp
            if (strlen($reqStartDate) > 10) {
                $reqStartDate = substr($reqStartDate, 0, 10);
            }
            if (strlen($reqEndDate) > 10) {
                $reqEndDate = substr($reqEndDate, 0, 10);
            }
            
            // Check if leave request overlaps with report date range
            // Overlap occurs if: request_start <= report_end AND request_end >= report_start
            $overlaps = $reqStartDate <= $endDate && $reqEndDate >= $startDate;
            error_log("After trimming: request[$reqStartDate to $reqEndDate] overlaps=$overlaps");
            
            return $overlaps;
        });
        
        error_log("Leave Report - Found " . count($requests) . " requests after date filtering");
        
        // If no requests found, return empty report instead of throwing exception
        if (empty($requests)) {
            return [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'filters' => $filters,
                'summary' => [
                    'total_requests' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'denied' => 0,
                    'total_days' => 0,
                    'by_leave_type' => []
                ],
                'records' => [],
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Filter by department if specified
        if (!empty($filters['department'])) {
            $requests = array_filter($requests, function($request) use ($filters) {
                $employee = $this->db->find(TABLE_EMPLOYEES, $request['employee_id']);
                return $employee && $employee['department'] === $filters['department'];
            });
        }
        
        // Calculate summary statistics
        $summary = $this->calculateLeaveSummary($requests);
        
        // Get detailed records with employee and leave type info
        $detailedRecords = $this->enrichLeaveRecords($requests);
        
        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'filters' => $filters,
            'summary' => $summary,
            'records' => $detailedRecords,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Generate department headcount report
     * 
     * @param array $filters Optional filters (department, employment_status, is_active)
     * @return array Report data
     * @throws \Exception If report generation fails
     */
    public function generateHeadcountReport(array $filters = []): array
    {
        // Database connection handled by models
        
        // Build query conditions
        $conditions = [];
        
        if (!empty($filters['department'])) {
            $conditions['department'] = $filters['department'];
        }
        
        if (!empty($filters['employment_status'])) {
            $conditions['employment_status'] = $filters['employment_status'];
        }
        
        // Default to active employees only
        if (!isset($filters['is_active'])) {
            $conditions['is_active'] = true;
        } else {
            $conditions['is_active'] = $filters['is_active'];
        }
        
        // Get employees
        $employees = $this->db->select(TABLE_EMPLOYEES, $conditions);
        
        // If no employees found, return empty report instead of throwing exception
        if (empty($employees)) {
            return [
                'filters' => $filters,
                'summary' => [
                    'total_employees' => 0,
                    'by_department' => [],
                    'by_employment_status' => [],
                    'by_position' => []
                ],
                'employees' => [],
                'generated_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Calculate breakdowns
        $summary = [
            'total_employees' => count($employees),
            'by_department' => $this->groupByField($employees, 'department'),
            'by_employment_status' => $this->groupByField($employees, 'employment_status'),
            'by_position' => $this->groupByField($employees, 'position')
        ];
        
        return [
            'filters' => $filters,
            'summary' => $summary,
            'employees' => $employees,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Calculate attendance summary statistics
     * 
     * @param array $records Attendance records
     * @return array Summary statistics
     */
    private function calculateAttendanceSummary(array $records): array
    {
        $summary = [
            'total_records' => count($records),
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'total_hours' => 0,
            'average_hours' => 0
        ];
        
        foreach ($records as $record) {
            $status = $record['status'] ?? '';
            
            switch ($status) {
                case 'Present':
                    $summary['present']++;
                    break;
                case 'Late':
                    $summary['late']++;
                    break;
                case 'Absent':
                    $summary['absent']++;
                    break;
            }
            
            if (!empty($record['work_hours'])) {
                $summary['total_hours'] += floatval($record['work_hours']);
            }
        }
        
        // Calculate average hours
        if ($summary['total_records'] > 0) {
            $summary['average_hours'] = round($summary['total_hours'] / $summary['total_records'], 2);
        }
        
        return $summary;
    }
    
    /**
     * Calculate leave summary statistics
     * 
     * @param array $requests Leave requests
     * @return array Summary statistics
     */
    private function calculateLeaveSummary(array $requests): array
    {
        $summary = [
            'total_requests' => count($requests),
            'pending' => 0,
            'approved' => 0,
            'denied' => 0,
            'total_days' => 0,
            'by_leave_type' => []
        ];
        
        foreach ($requests as $request) {
            $status = $request['status'] ?? '';
            
            switch ($status) {
                case 'Pending':
                    $summary['pending']++;
                    break;
                case 'Approved':
                    $summary['approved']++;
                    break;
                case 'Denied':
                    $summary['denied']++;
                    break;
            }
            
            // Add days
            if (!empty($request['days'])) {
                $summary['total_days'] += floatval($request['days']);
            }
            
            // Group by leave type - use name instead of ID
            $leaveTypeId = $request['leave_type_id'] ?? null;
            $leaveTypeName = 'Unknown';
            
            if ($leaveTypeId) {
                $leaveType = $this->db->find(TABLE_LEAVE_TYPES, $leaveTypeId);
                if ($leaveType && !empty($leaveType['name'])) {
                    $leaveTypeName = $leaveType['name'];
                }
            }
            
            if (!isset($summary['by_leave_type'][$leaveTypeName])) {
                $summary['by_leave_type'][$leaveTypeName] = [
                    'count' => 0,
                    'days' => 0
                ];
            }
            $summary['by_leave_type'][$leaveTypeName]['count']++;
            $summary['by_leave_type'][$leaveTypeName]['days'] += floatval($request['days'] ?? 0);
        }
        
        return $summary;
    }
    
    /**
     * Enrich attendance records with employee details
     * 
     * @param array $records Attendance records
     * @return array Enriched records
     */
    private function enrichAttendanceRecords(array $records): array
    {
        // Database connection handled by models
        
        $enriched = [];
        
        foreach ($records as $record) {
            $employee = $this->db->find(TABLE_EMPLOYEES, $record['employee_id']);
            
            if ($employee) {
                $record['employee'] = [
                    'employee_id' => $employee['employee_id'],
                    'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'department' => $employee['department'],
                    'position' => $employee['position']
                ];
            }
            
            $enriched[] = $record;
        }
        
        return $enriched;
    }
    
    /**
     * Enrich leave records with employee and leave type details
     * 
     * @param array $requests Leave requests
     * @return array Enriched records
     */
    private function enrichLeaveRecords(array $requests): array
    {
        // Database connection handled by models
        
        $enriched = [];
        
        foreach ($requests as $request) {
            // Get employee details
            $employee = $this->db->find(TABLE_EMPLOYEES, $request['employee_id']);
            
            if ($employee) {
                $request['employee'] = [
                    'employee_id' => $employee['employee_id'],
                    'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'department' => $employee['department'],
                    'position' => $employee['position']
                ];
            }
            
            // Get leave type details
            $leaveType = $this->db->find(TABLE_LEAVE_TYPES, $request['leave_type_id']);
            
            if ($leaveType) {
                $request['leave_type'] = [
                    'name' => $leaveType['name'],
                    'code' => $leaveType['code'] ?? ''
                ];
            }
            
            $enriched[] = $request;
        }
        
        return $enriched;
    }
    
    /**
     * Group records by a specific field
     * 
     * @param array $records Records to group
     * @param string $field Field to group by
     * @return array Grouped counts
     */
    private function groupByField(array $records, string $field): array
    {
        $grouped = [];
        
        foreach ($records as $record) {
            $value = $record[$field] ?? 'Unassigned';
            
            if (!isset($grouped[$value])) {
                $grouped[$value] = 0;
            }
            
            $grouped[$value]++;
        }
        
        // Sort by count descending
        arsort($grouped);
        
        return $grouped;
    }
}
