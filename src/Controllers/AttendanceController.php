<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\NotFoundException;
use Services\AttendanceService;

/**
 * AttendanceController - Handles HTTP requests for attendance management
 * 
 * This controller coordinates attendance-related operations, handling HTTP requests
 * and delegating business logic to the AttendanceService.
 */
class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;
    
    /**
     * Constructor - Initialize with AttendanceService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->attendanceService = $container->resolve(AttendanceService::class);
    }
    
    /**
     * Record time-in for an employee
     * POST /api/attendance/timein
     */
    public function timeIn(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $data = $this->getJsonData();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can record time-in for any employee
                if (empty($data['employee_id'])) {
                    return $this->error('Employee ID is required for admin users', 400);
                }
                $employeeId = $data['employee_id'];
            } else {
                // Employee can only record their own time-in
                // The user['id'] is the UUID from the employees table (same as employee UUID)
                $employeeId = $user['id'];
            }
            
            // Optional date and time parameters
            $date = $data['date'] ?? null;
            $timeIn = $data['time_in'] ?? null;
            
            // Validate date format if provided
            if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->error('Invalid date format. Use Y-m-d format.', 400);
            }
            
            // Record time-in
            $result = $this->attendanceService->recordTimeIn($employeeId, $date, $timeIn);
            
            // Log activity
            $this->logActivity('TIME_IN_RECORDED', [
                'employee_id' => $employeeId,
                'date' => $result['date']
            ]);
            
            return $this->success(['attendance' => $result], 'Time-in recorded successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Record time-out for an employee
     * POST /api/attendance/timeout
     */
    public function timeOut(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $data = $this->getJsonData();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can record time-out for any employee
                if (empty($data['employee_id'])) {
                    return $this->error('Employee ID is required for admin users', 400);
                }
                $employeeId = $data['employee_id'];
            } else {
                // Employee can only record their own time-out
                $employeeId = $user['id'];
            }
            
            // Optional date and time parameters
            $date = $data['date'] ?? null;
            $timeOut = $data['time_out'] ?? null;
            
            // Validate date format if provided
            if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->error('Invalid date format. Use Y-m-d format.', 400);
            }
            
            // Record time-out
            $result = $this->attendanceService->recordTimeOut($employeeId, $date, $timeOut);
            
            // Log activity
            $this->logActivity('TIME_OUT_RECORDED', [
                'employee_id' => $employeeId,
                'date' => $result['date']
            ]);
            
            return $this->success(['attendance' => $result], 'Time-out recorded successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get daily attendance for all employees
     * GET /api/attendance/daily
     */
    public function daily(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $date = $this->getQueryParam('date', date('Y-m-d'));
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->error('Invalid date format. Use Y-m-d format.', 400);
            }
            
            // Admins can see all attendance, employees can only see their own
            if ($user['role'] === 'admin') {
                $result = $this->attendanceService->getDailyAttendance($date);
            } else {
                // For employees, get only their own attendance for the date
                $result = $this->attendanceService->getEmployeeAttendanceByDate($user['id'], $date);
            }
            
            // Log activity
            $this->logActivity('VIEW_DAILY_ATTENDANCE', ['date' => $date]);
            
            return $this->success($result, 'Daily attendance retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get attendance history for an employee
     * GET /api/attendance/history
     */
    public function history(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can view any employee's history
                $employeeId = $this->getQueryParam('employee_id');
                if (empty($employeeId)) {
                    return $this->error('Employee ID is required for admin users', 400);
                }
            } else {
                // Employee can only view their own history
                $employeeId = $user['id'];
            }
            
            $startDate = $this->getQueryParam('start_date');
            $endDate = $this->getQueryParam('end_date');
            $limit = min(max(intval($this->getQueryParam('limit', 100)), 1), 500);
            
            // Validate date formats if provided
            if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                return $this->error('Invalid start_date format. Use Y-m-d format.', 400);
            }
            
            if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                return $this->error('Invalid end_date format. Use Y-m-d format.', 400);
            }
            
            $result = $this->attendanceService->getAttendanceHistory($employeeId, $startDate, $endDate, $limit);
            
            // Log activity
            $this->logActivity('VIEW_ATTENDANCE_HISTORY', [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            return $this->success($result, 'Attendance history retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Detect and mark absent employees for a specific date
     * POST /api/attendance/detect_absences
     */
    public function detectAbsences(Request $request): Response
    {
        try {
            // Clear any output buffers to prevent HTML errors
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            $date = $data['date'] ?? date('Y-m-d');
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return $this->error('Invalid date format. Use Y-m-d format.', 400);
            }
            
            // Prevent processing future dates
            $today = date('Y-m-d');
            if ($date > $today) {
                return $this->error('Cannot detect absences for future dates. Please select today or a past date.', 400);
            }
            
            $result = $this->attendanceService->detectAbsentEmployees($date);
            
            // Log activity
            $this->logActivity('DETECT_ABSENCES', [
                'date' => $date,
                'absent_count' => $result['absent_count']
            ]);
            
            return $this->success($result, 'Absence detection completed successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            error_log('DetectAbsences Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->handleException($e);
        }
    }
    
    /**
     * Override attendance status manually (admin function)
     * PUT /api/attendance/override
     */
    public function override(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            
            if (empty($data['attendance_id'])) {
                return $this->error('Attendance ID is required', 400);
            }
            
            if (empty($data['status'])) {
                return $this->error('Status is required', 400);
            }
            
            $attendanceId = $data['attendance_id'];
            $newStatus = $data['status'];
            $remarks = $data['remarks'] ?? '';
            
            $result = $this->attendanceService->overrideAttendanceStatus($attendanceId, $newStatus, $remarks);
            
            // Log activity
            $this->logActivity('OVERRIDE_ATTENDANCE_STATUS', [
                'attendance_id' => $attendanceId,
                'new_status' => $newStatus
            ]);
            
            return $this->success(['attendance' => $result], 'Attendance status updated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Display attendance page
     * GET /attendance
     */
    public function indexView(Request $request): Response
    {
        try {
            // Render the attendance index view directly without base layout
            ob_start();
            include __DIR__ . '/../Views/attendance/index.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            error_log('View Exception: ' . $e->getMessage());
            return new Response('Internal Server Error', 500, ['Content-Type' => 'text/html']);
        }
    }
}
