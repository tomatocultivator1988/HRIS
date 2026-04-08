<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

/**
 * Attendance Model - Represents attendance records and handles attendance data operations
 * 
 * This model handles attendance data access, validation, and business entity operations.
 * Works with the Supabase attendance table and provides methods for CRUD operations.
 */
class Attendance extends Model
{
    protected string $table = 'attendance';
    protected string $primaryKey = 'id';
    protected bool $timestamps = false; // Attendance table doesn't have created_at/updated_at columns
    
    protected array $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'status',
        'work_hours',
        'remarks',
        'created_at' // Allow manual setting if needed
    ];
    
    protected array $guarded = [
        'id'
    ];
    
    protected array $casts = [
        'work_hours' => 'float',
        'date' => 'date'
    ];
    
    /**
     * Find attendance record by employee and date
     *
     * @param string $employeeId Employee ID
     * @param string $date Date in Y-m-d format
     * @return array|null Attendance record or null if not found
     */
    public function findByEmployeeAndDate(string $employeeId, string $date): ?array
    {
        try {
            $result = $this->where([
                'employee_id' => $employeeId,
                'date' => $date
            ])->first();
            
            return $result;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findByEmployeeAndDate', [
                'employee_id' => $employeeId,
                'date' => $date
            ]);
            return null;
        }
    }

    public function findLatestOpenRecord(string $employeeId): ?array
    {
        try {
            $records = $this->where([
                'employee_id' => $employeeId
            ])->orderBy('date', 'DESC')->limit(50)->get();

            foreach ($records as $record) {
                if (!empty($record['time_in']) && empty($record['time_out'])) {
                    return $record;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findLatestOpenRecord', [
                'employee_id' => $employeeId
            ]);
            return null;
        }
    }
    
    /**
     * Get attendance records for a date range
     *
     * @param string $employeeId Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Array of attendance records
     */
    public function getByDateRange(string $employeeId, string $startDate, string $endDate): array
    {
        try {
            $records = $this->where(['employee_id' => $employeeId])->get();
            
            // Filter by date range in PHP
            return array_filter($records, function($record) use ($startDate, $endDate) {
                return $record['date'] >= $startDate && $record['date'] <= $endDate;
            });
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByDateRange', [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [];
        }
    }
    
    /**
     * Get daily attendance for all employees
     *
     * @param string $date Date in Y-m-d format
     * @return array Array of attendance records
     */
    public function getDailyAttendance(string $date): array
    {
        try {
            return $this->where(['date' => $date])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getDailyAttendance', ['date' => $date]);
            return [];
        }
    }
    
    /**
     * Get attendance records by status
     *
     * @param string $status Attendance status
     * @param string $date Date in Y-m-d format
     * @return array Array of attendance records
     */
    public function getByStatus(string $status, string $date): array
    {
        try {
            return $this->where([
                'status' => $status,
                'date' => $date
            ])->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByStatus', [
                'status' => $status,
                'date' => $date
            ]);
            return [];
        }
    }
    
    /**
     * Calculate work hours between time-in and time-out
     *
     * @param string $timeIn Time-in timestamp
     * @param string $timeOut Time-out timestamp
     * @return float Work hours in decimal format
     */
    public function calculateWorkHours(string $timeIn, string $timeOut): float
    {
        $timeInTimestamp = strtotime($timeIn);
        $timeOutTimestamp = strtotime($timeOut);
        
        if ($timeOutTimestamp <= $timeInTimestamp) {
            return 0.0;
        }
        
        // Calculate difference in seconds
        $diffSeconds = $timeOutTimestamp - $timeInTimestamp;
        
        // Convert to hours (decimal format)
        $workHours = $diffSeconds / 3600;
        
        // Round to 2 decimal places and ensure maximum of 24 hours
        return min(round($workHours, 2), 24.00);
    }
    
    /**
     * Determine attendance status based on time-in
     *
     * @param string $timeIn Time-in timestamp
     * @param string $standardStartTime Standard start time (H:i:s format)
     * @return string Attendance status
     */
    public function determineStatus(string $timeIn, string $standardStartTime = '09:00:00'): string
    {
        $timeInTime = date('H:i:s', strtotime($timeIn));
        
        if ($timeInTime <= $standardStartTime) {
            return 'Present';
        } else {
            return 'Late';
        }
    }
    
    /**
     * Validate attendance data before database operations
     *
     * @param array $data Attendance data to validate
     * @param mixed $id Attendance ID for update operations (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];
        
        // Required field validation for create operations
        if ($id === null) {
            if (empty($data['employee_id'])) {
                $errors['employee_id'] = 'Employee ID is required';
            }
            
            if (empty($data['date'])) {
                $errors['date'] = 'Date is required';
            }
        }
        
        // Date validation
        if (isset($data['date'])) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $data['date']);
            if (!$dateTime || $dateTime->format('Y-m-d') !== $data['date']) {
                $errors['date'] = 'Invalid date format (Y-m-d required)';
            }
        }
        
        // Time-in validation
        if (isset($data['time_in']) && !empty($data['time_in'])) {
            if (!strtotime($data['time_in'])) {
                $errors['time_in'] = 'Invalid time-in format';
            }
        }
        
        // Time-out validation
        if (isset($data['time_out']) && !empty($data['time_out'])) {
            if (!strtotime($data['time_out'])) {
                $errors['time_out'] = 'Invalid time-out format';
            }
            
            // Validate time-out is after time-in
            if (isset($data['time_in']) && !empty($data['time_in']) && empty($errors['time_in'])) {
                if (strtotime($data['time_out']) <= strtotime($data['time_in'])) {
                    $errors['time_out'] = 'Time-out must be after time-in';
                }
            }
        }
        
        // Status validation
        if (isset($data['status'])) {
            $validStatuses = ['Present', 'Late', 'Absent', 'Half-day', 'On Leave'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid attendance status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }
        
        // Work hours validation
        if (isset($data['work_hours']) && !empty($data['work_hours'])) {
            if (!is_numeric($data['work_hours']) || $data['work_hours'] < 0 || $data['work_hours'] > 24) {
                $errors['work_hours'] = 'Work hours must be between 0 and 24';
            }
        }
        
        // Sanitize data
        $sanitizedData = $this->sanitizeAttendanceData($data);
        
        return new ValidationResult(empty($errors), $errors, $sanitizedData);
    }
    
    /**
     * Sanitize attendance data
     *
     * @param array $data Raw attendance data
     * @return array Sanitized data
     */
    private function sanitizeAttendanceData(array $data): array
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
     * Get attendance statistics for a date range
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Statistics
     */
    public function getStatistics(string $startDate, string $endDate): array
    {
        try {
            $records = $this->all();
            
            // Filter by date range
            $filteredRecords = array_filter($records, function($record) use ($startDate, $endDate) {
                return $record['date'] >= $startDate && $record['date'] <= $endDate;
            });
            
            $stats = [
                'total_records' => count($filteredRecords),
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'half_day' => 0,
                'total_work_hours' => 0.0,
                'average_work_hours' => 0.0
            ];
            
            foreach ($filteredRecords as $record) {
                switch ($record['status']) {
                    case 'Present':
                        $stats['present']++;
                        break;
                    case 'Late':
                        $stats['late']++;
                        break;
                    case 'Absent':
                        $stats['absent']++;
                        break;
                    case 'Half-day':
                        $stats['half_day']++;
                        break;
                }
                
                if (!empty($record['work_hours'])) {
                    $stats['total_work_hours'] += floatval($record['work_hours']);
                }
            }
            
            // Calculate average work hours (excluding absent employees)
            $workingEmployees = $stats['total_records'] - $stats['absent'];
            if ($workingEmployees > 0) {
                $stats['average_work_hours'] = round($stats['total_work_hours'] / $workingEmployees, 2);
            }
            
            return $stats;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getStatistics', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            return [
                'total_records' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'half_day' => 0,
                'total_work_hours' => 0.0,
                'average_work_hours' => 0.0
            ];
        }
    }
}
