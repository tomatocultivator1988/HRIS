<?php

namespace Services;

use Core\ValidationException;
use Core\NotFoundException;
use Models\Attendance;
use Models\Employee;
use Exception;

/**
 * AttendanceService - Handles attendance business logic
 * 
 * This service encapsulates all attendance-related business logic including
 * time-in/time-out recording, attendance status calculation, work hours computation,
 * and absence detection.
 */
class AttendanceService
{
    private Attendance $attendanceModel;
    private Employee $employeeModel;
    
    public function __construct(Attendance $attendanceModel, Employee $employeeModel)
    {
        $this->attendanceModel = $attendanceModel;
        $this->employeeModel = $employeeModel;
    }
    
    /**
     * Record time-in for an employee
     *
     * @param string $employeeId Employee ID
     * @param string|null $date Date (Y-m-d format), defaults to today
     * @param string|null $timeIn Time-in timestamp, defaults to now
     * @return array Time-in record
     */
    public function recordTimeIn(string $employeeId, ?string $date = null, ?string $timeIn = null): array
    {
        try {
            error_log('=== RECORD TIME IN DEBUG ===');
            error_log('Employee ID: ' . $employeeId);
            error_log('Date parameter: ' . ($date ?? 'NULL'));
            error_log('Time In parameter: ' . ($timeIn ?? 'NULL'));
            
            // Use current date and time if not provided
            if ($date === null) {
                $date = date('Y-m-d');
                error_log('Using current date: ' . $date);
            }
            
            if ($timeIn === null) {
                $timeIn = date('Y-m-d H:i:s');
                error_log('Using current time: ' . $timeIn);
            }
            
            error_log('PHP timezone: ' . date_default_timezone_get());
            error_log('Final time_in to save: ' . $timeIn);
            
            // Validate employee exists and is active
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee || !$employee['is_active']) {
                throw new NotFoundException('Employee not found or inactive');
            }
            
            // Check if attendance record already exists for this date
            $existingRecord = $this->attendanceModel->findByEmployeeAndDate($employeeId, $date);
            if ($existingRecord) {
                throw new ValidationException('Time-in already recorded for this date', [
                    'date' => 'Time-in already recorded for this date'
                ]);
            }
            
            // Validate that the date is a working day
            if (!$this->isWorkingDay($date)) {
                throw new ValidationException('Cannot record attendance on non-working day', [
                    'date' => 'Cannot record attendance on non-working day'
                ]);
            }
            
            // Calculate attendance status based on time-in
            $status = $this->attendanceModel->determineStatus($timeIn);
            
            // Create attendance record
            $attendanceData = [
                'employee_id' => $employeeId,
                'date' => $date,
                'time_in' => $timeIn,
                'status' => $status,
                'work_hours' => null,
                'remarks' => null
            ];
            
            error_log('Attendance data to insert: ' . json_encode($attendanceData));
            
            $newRecord = $this->attendanceModel->create($attendanceData);
            
            error_log('Record created: ' . json_encode($newRecord));
            
            if (empty($newRecord)) {
                throw new Exception('Failed to create attendance record - empty result from database');
            }
            
            return $this->formatAttendanceData($newRecord);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('AttendanceService::recordTimeIn Error: ' . $e->getMessage());
            throw new Exception('Failed to record time-in: ' . $e->getMessage());
        }
    }
    
    /**
     * Record time-out for an employee
     *
     * @param string $employeeId Employee ID
     * @param string|null $date Date (Y-m-d format), defaults to today
     * @param string|null $timeOut Time-out timestamp, defaults to now
     * @return array Updated attendance record
     */
    public function recordTimeOut(string $employeeId, ?string $date = null, ?string $timeOut = null): array
    {
        try {
            error_log('=== RECORD TIME OUT DEBUG ===');
            error_log('Employee ID: ' . $employeeId);
            error_log('Date parameter: ' . ($date ?? 'NULL'));
            error_log('Time Out parameter: ' . ($timeOut ?? 'NULL'));
            
            // Use current date and time if not provided
            if ($date === null) {
                $date = date('Y-m-d');
                error_log('Using current date: ' . $date);
            }
            
            if ($timeOut === null) {
                $timeOut = date('Y-m-d H:i:s');
                error_log('Using current time: ' . $timeOut);
            }
            
            error_log('PHP timezone: ' . date_default_timezone_get());
            error_log('Final time_out to save: ' . $timeOut);
            
            // Find existing attendance record for this date
            $existingRecord = $this->attendanceModel->findByEmployeeAndDate($employeeId, $date);
            
            if (!$existingRecord) {
                throw new NotFoundException('No time-in record found for this date');
            }
            
            // Check if time-out is already recorded
            if (!empty($existingRecord['time_out'])) {
                throw new ValidationException('Time-out already recorded for this date', [
                    'time_out' => 'Time-out already recorded for this date'
                ]);
            }
            
            // Validate that time-out is after time-in
            if (strtotime($timeOut) <= strtotime($existingRecord['time_in'])) {
                throw new ValidationException('Time-out must be after time-in', [
                    'time_out' => 'Time-out must be after time-in'
                ]);
            }
            
            // Calculate work hours
            $workHours = $this->attendanceModel->calculateWorkHours($existingRecord['time_in'], $timeOut);
            
            error_log('Calculated work hours: ' . $workHours);
            
            // Update attendance record
            $updateData = [
                'time_out' => $timeOut,
                'work_hours' => $workHours
            ];
            
            error_log('Update data: ' . json_encode($updateData));
            
            $this->attendanceModel->update($existingRecord['id'], $updateData);
            
            // Get updated record
            $updatedRecord = $this->attendanceModel->find($existingRecord['id']);
            
            error_log('Updated record: ' . json_encode($updatedRecord));
            
            return $this->formatAttendanceData($updatedRecord);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('AttendanceService::recordTimeOut Error: ' . $e->getMessage());
            throw new Exception('Failed to record time-out: ' . $e->getMessage());
        }
    }
    
    /**
     * Get daily attendance for all employees
     *
     * @param string $date Date in Y-m-d format
     * @return array Daily attendance data with employee information
     */
    public function getDailyAttendance(string $date): array
    {
        try {
            $attendanceRecords = $this->attendanceModel->getDailyAttendance($date);
            
            // Enrich with employee information - ONLY for active employees
            $enrichedRecords = [];
            
            foreach ($attendanceRecords as $record) {
                $employee = $this->employeeModel->find($record['employee_id']);
                
                // Only include attendance for active employees
                if ($employee && $employee['is_active']) {
                    $enrichedRecords[] = [
                        'attendance_id' => $record['id'],
                        'employee_id' => $record['employee_id'],
                        'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                        'employee_number' => $employee['employee_id'],
                        'department' => $employee['department'],
                        'position' => $employee['position'],
                        'date' => $record['date'],
                        'time_in' => $record['time_in'],
                        'time_out' => $record['time_out'],
                        'work_hours' => $record['work_hours'],
                        'status' => $record['status'],
                        'remarks' => $record['remarks']
                    ];
                }
            }
            
            // Calculate summary
            $summary = $this->calculateDailySummary($attendanceRecords);
            
            return [
                'date' => $date,
                'records' => $enrichedRecords,
                'summary' => $summary
            ];
            
        } catch (Exception $e) {
            error_log('AttendanceService::getDailyAttendance Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve daily attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Get attendance for a specific employee on a specific date
     *
     * @param string $employeeId Employee ID
     * @param string $date Date in Y-m-d format
     * @return array Attendance record for the employee
     */
    public function getEmployeeAttendanceByDate(string $employeeId, string $date): array
    {
        try {
            // Get attendance record for this employee on this date
            $attendanceRecord = $this->attendanceModel->findByEmployeeAndDate($employeeId, $date);
            
            $employee = $this->employeeModel->find($employeeId);
            
            if (!$employee) {
                throw new NotFoundException('Employee not found');
            }
            
            // Format the record if it exists
            $record = null;
            if ($attendanceRecord) {
                $record = [
                    'attendance_id' => $attendanceRecord['id'],
                    'employee_id' => $attendanceRecord['employee_id'],
                    'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'employee_number' => $employee['employee_id'],
                    'department' => $employee['department'],
                    'position' => $employee['position'],
                    'date' => $attendanceRecord['date'],
                    'time_in' => $attendanceRecord['time_in'],
                    'time_out' => $attendanceRecord['time_out'],
                    'work_hours' => $attendanceRecord['work_hours'],
                    'status' => $attendanceRecord['status'],
                    'remarks' => $attendanceRecord['remarks']
                ];
            }
            
            return [
                'date' => $date,
                'employee_id' => $employeeId,
                'record' => $record
            ];
            
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('AttendanceService::getEmployeeAttendanceByDate Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve employee attendance: ' . $e->getMessage());
        }
    }
    
    /**
     * Get attendance history for an employee
     *
     * @param string $employeeId Employee ID
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @param int $limit Maximum records to return
     * @return array Attendance history
     */
    public function getAttendanceHistory(string $employeeId, ?string $startDate = null, ?string $endDate = null, int $limit = 100): array
    {
        try {
            // Default to last 30 days if no dates provided
            if ($startDate === null) {
                $startDate = date('Y-m-d', strtotime('-30 days'));
            }
            
            if ($endDate === null) {
                $endDate = date('Y-m-d');
            }
            
            $records = $this->attendanceModel->getByDateRange($employeeId, $startDate, $endDate);
            
            // Sort by date descending
            usort($records, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            
            // Apply limit
            $records = array_slice($records, 0, $limit);
            
            // Format records
            $formattedRecords = array_map([$this, 'formatAttendanceData'], $records);
            
            return [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_records' => count($formattedRecords),
                'records' => $formattedRecords
            ];
            
        } catch (Exception $e) {
            error_log('AttendanceService::getAttendanceHistory Error: ' . $e->getMessage());
            throw new Exception('Failed to retrieve attendance history: ' . $e->getMessage());
        }
    }
    
    /**
     * Detect and mark absent employees for a specific date
     *
     * @param string $date Date in Y-m-d format
     * @return array Detection result
     */
    public function detectAbsentEmployees(string $date): array
    {
        try {
            // Prevent processing future dates
            $today = date('Y-m-d');
            if ($date > $today) {
                return [
                    'date' => $date,
                    'is_future_date' => true,
                    'is_working_day' => false,
                    'absent_count' => 0,
                    'absences_marked' => 0,
                    'absent_employees' => [],
                    'on_leave_count' => 0,
                    'on_leave_employees' => [],
                    'message' => 'Cannot detect absences for future dates'
                ];
            }
            
            // Only process working days
            if (!$this->isWorkingDay($date)) {
                return [
                    'date' => $date,
                    'is_working_day' => false,
                    'absent_count' => 0,
                    'absences_marked' => 0,
                    'absent_employees' => [],
                    'on_leave_count' => 0,
                    'on_leave_employees' => []
                ];
            }
            
            // Get all active employees
            $employees = $this->employeeModel->where(['is_active' => true])->get();
            
            // Get approved leave requests that cover this date
            $approvedLeaves = $this->getApprovedLeavesForDate($date);
            
            // Build a map of employees on leave for this date
            $employeesOnLeave = [];
            foreach ($approvedLeaves as $leave) {
                if ($leave['start_date'] <= $date && $leave['end_date'] >= $date) {
                    $employeesOnLeave[$leave['employee_id']] = $leave;
                }
            }
            
            $newlyMarkedAbsent = [];
            $allAbsentEmployees = [];
            $onLeaveEmployees = [];
            
            foreach ($employees as $employee) {
                // Check if employee has attendance record for this date
                $attendanceRecord = $this->attendanceModel->findByEmployeeAndDate($employee['id'], $date);
                
                // If no attendance record exists
                if (!$attendanceRecord) {
                    // Check if employee is on approved leave
                    if (isset($employeesOnLeave[$employee['id']])) {
                        $leave = $employeesOnLeave[$employee['id']];
                        
                        // Create "On Leave" attendance record
                        $leaveData = [
                            'employee_id' => $employee['id'],
                            'date' => $date,
                            'time_in' => null,
                            'time_out' => null,
                            'status' => 'On Leave',
                            'work_hours' => 0.00,
                            'remarks' => 'On approved leave (Leave ID: ' . $leave['id'] . ')'
                        ];
                        
                        $newRecord = $this->attendanceModel->create($leaveData);
                        
                        $onLeaveEmployees[] = [
                            'employee_id' => $employee['id'],
                            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                            'department' => $employee['department'],
                            'attendance_id' => $newRecord['id'],
                            'leave_start' => $leave['start_date'],
                            'leave_end' => $leave['end_date'],
                            'leave_duration' => $leave['total_days'] . ' day(s)'
                        ];
                    } else {
                        // Mark as absent
                        $absentData = [
                            'employee_id' => $employee['id'],
                            'date' => $date,
                            'time_in' => null,
                            'time_out' => null,
                            'status' => 'Absent',
                            'work_hours' => 0.00,
                            'remarks' => 'Auto-detected absence'
                        ];
                        
                        $newRecord = $this->attendanceModel->create($absentData);
                        
                        $employeeInfo = [
                            'employee_id' => $employee['id'],
                            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                            'department' => $employee['department'],
                            'attendance_id' => $newRecord['id']
                        ];
                        
                        $newlyMarkedAbsent[] = $employeeInfo;
                        $allAbsentEmployees[] = $employeeInfo;
                    }
                } else {
                    // If attendance record exists and status is Absent, add to all absent list
                    if ($attendanceRecord['status'] === 'Absent') {
                        $allAbsentEmployees[] = [
                            'employee_id' => $employee['id'],
                            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                            'department' => $employee['department'],
                            'attendance_id' => $attendanceRecord['id'],
                            'already_marked' => true
                        ];
                    }
                }
            }
            
            return [
                'date' => $date,
                'is_working_day' => true,
                'absent_count' => count($allAbsentEmployees),
                'absences_marked' => count($newlyMarkedAbsent),
                'absent_employees' => $allAbsentEmployees,
                'newly_marked' => $newlyMarkedAbsent,
                'on_leave_count' => count($onLeaveEmployees),
                'on_leave_employees' => $onLeaveEmployees
            ];
            
        } catch (Exception $e) {
            error_log('AttendanceService::detectAbsentEmployees Error: ' . $e->getMessage());
            throw new Exception('Failed to detect absent employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Get approved leave requests for a specific date
     *
     * @param string $date Date in Y-m-d format
     * @return array Approved leave requests
     */
    private function getApprovedLeavesForDate(string $date): array
    {
        try {
            // Use reflection to get the database connection from attendance model
            $reflection = new \ReflectionClass($this->attendanceModel);
            $property = $reflection->getProperty('db');
            $property->setAccessible(true);
            $db = $property->getValue($this->attendanceModel);
            
            // Query leave_requests table using Supabase select method
            // Note: select() returns the data array directly, not ['success' => true, 'data' => [...]]
            $result = $db->select('leave_requests', ['status' => 'Approved']);
            
            // select() returns empty array on failure, or array of records on success
            if (!is_array($result)) {
                error_log('Failed to fetch approved leaves: Invalid result type');
                return [];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('AttendanceService::getApprovedLeavesForDate Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Override attendance status manually (admin function)
     *
     * @param string $attendanceId Attendance record ID
     * @param string $newStatus New status
     * @param string $remarks Admin remarks
     * @return array Updated attendance record
     */
    public function overrideAttendanceStatus(string $attendanceId, string $newStatus, string $remarks): array
    {
        try {
            // Validate status
            $validStatuses = ['Present', 'Late', 'Absent', 'Half-day'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new ValidationException('Invalid attendance status', [
                    'status' => 'Invalid attendance status. Must be one of: ' . implode(', ', $validStatuses)
                ]);
            }
            
            // Get existing record
            $existingRecord = $this->attendanceModel->find($attendanceId);
            if (!$existingRecord) {
                throw new NotFoundException('Attendance record not found');
            }
            
            // Update record
            $updateData = [
                'status' => $newStatus,
                'remarks' => $remarks
            ];
            
            $this->attendanceModel->update($attendanceId, $updateData);
            
            // Get updated record
            $updatedRecord = $this->attendanceModel->find($attendanceId);
            
            return $this->formatAttendanceData($updatedRecord);
            
        } catch (ValidationException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            error_log('AttendanceService::overrideAttendanceStatus Error: ' . $e->getMessage());
            throw new Exception('Failed to override attendance status: ' . $e->getMessage());
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
        // Default: Monday-Friday are working days
        $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }
    
    /**
     * Calculate daily attendance summary
     *
     * @param array $attendanceRecords Raw attendance records
     * @return array Summary statistics
     */
    private function calculateDailySummary(array $attendanceRecords): array
    {
        $summary = [
            'total_employees' => count($attendanceRecords),
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'half_day' => 0,
            'total_work_hours' => 0.0,
            'average_work_hours' => 0.0
        ];
        
        foreach ($attendanceRecords as $record) {
            switch ($record['status']) {
                case 'Present':
                    $summary['present']++;
                    break;
                case 'Late':
                    $summary['late']++;
                    break;
                case 'Absent':
                    $summary['absent']++;
                    break;
                case 'Half-day':
                    $summary['half_day']++;
                    break;
            }
            
            if (!empty($record['work_hours'])) {
                $summary['total_work_hours'] += floatval($record['work_hours']);
            }
        }
        
        // Calculate average work hours (excluding absent employees)
        $workingEmployees = $summary['total_employees'] - $summary['absent'];
        if ($workingEmployees > 0) {
            $summary['average_work_hours'] = round($summary['total_work_hours'] / $workingEmployees, 2);
        }
        
        return $summary;
    }
    
    /**
     * Format attendance data for API response
     *
     * @param array $attendance Raw attendance data
     * @return array Formatted attendance data
     */
    private function formatAttendanceData(array $attendance): array
    {
        return [
            'id' => $attendance['id'],
            'employee_id' => $attendance['employee_id'],
            'date' => $attendance['date'],
            'time_in' => $attendance['time_in'],
            'time_out' => $attendance['time_out'],
            'work_hours' => $attendance['work_hours'] ? floatval($attendance['work_hours']) : null,
            'status' => $attendance['status'],
            'remarks' => $attendance['remarks'],
            'created_at' => $attendance['created_at'] ?? null,
            'updated_at' => $attendance['updated_at'] ?? null
        ];
    }
}
