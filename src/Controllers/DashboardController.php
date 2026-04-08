<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\View;

/**
 * DashboardController - Handles dashboard requests for admin and employee views
 * 
 * This controller manages dashboard rendering and metrics API endpoints,
 * providing aggregated data for both admin and employee dashboards.
 * 
 * Requirements: 3.1, 3.4, 6.3
 */
class DashboardController extends Controller
{
    private View $view;
    
    /**
     * Constructor - Initialize with dependencies
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->view = new View();
    }
    
    /**
     * Display admin dashboard
     * GET /dashboard/admin
     */
    public function admin(Request $request): Response
    {
        try {
            // For web routes, authentication is handled by JavaScript
            // The page will render, and auth.js will verify the token and redirect if needed
            
            // Render the admin dashboard view directly without base layout
            // Since admin.php is now a complete standalone HTML page
            ob_start();
            include __DIR__ . '/../Views/dashboard/admin.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Display employee dashboard
     * GET /dashboard/employee
     */
    public function employee(Request $request): Response
    {
        try {
            // For web routes, authentication is handled by JavaScript
            // The page will render, and auth.js will verify the token and redirect if needed
            
            // Render the employee dashboard view directly without base layout
            // Since employee.php is now a complete standalone HTML page
            ob_start();
            include __DIR__ . '/../Views/dashboard/employee.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get dashboard metrics (API endpoint)
     * GET /api/dashboard/metrics
     */
    public function metrics(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get dashboard metrics
            $metrics = $this->getDashboardMetrics();
            
            // Get chart data
            $charts = $this->getDashboardCharts();
            
            // Log activity
            $this->logActivity('VIEW_DASHBOARD_METRICS');
            
            return $this->success([
                'metrics' => $metrics,
                'charts' => $charts
            ], 'Dashboard metrics retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get dashboard metrics data
     * 
     * @return array Dashboard metrics
     */
    private function getDashboardMetrics(): array
    {
        $today = date('Y-m-d');
        
        // Get models from container
        $employeeModel = $this->container->resolve(\Models\Employee::class);
        $attendanceModel = $this->container->resolve(\Models\Attendance::class);
        $leaveRequestModel = $this->container->resolve(\Models\LeaveRequest::class);
        
        // Total active employees
        $totalEmployees = $employeeModel->getActiveCount();
        
        // Get all active employee IDs
        $activeEmployees = $employeeModel->where(['is_active' => true])->get();
        $activeEmployeeIds = array_column($activeEmployees, 'id');
        
        // Today's attendance summary - ONLY for active employees
        $attendanceToday = $attendanceModel->where(['date' => $today])->get();
        
        $presentToday = 0;
        $lateToday = 0;
        $absentToday = 0;
        
        foreach ($attendanceToday as $record) {
            // Skip attendance records for inactive employees
            if (!in_array($record['employee_id'], $activeEmployeeIds)) {
                continue;
            }
            
            $status = $record['status'] ?? '';
            
            switch ($status) {
                case 'Present':
                    $presentToday++;
                    break;
                case 'Late':
                    $lateToday++;
                    break;
                case 'Absent':
                    $absentToday++;
                    break;
            }
        }
        
        // Employees on leave today
        $leaveToday = $leaveRequestModel->where([
            'status' => 'Approved'
        ])->get();
        
        // Filter leave requests that cover today
        $onLeave = 0;
        foreach ($leaveToday as $leave) {
            $startDate = $leave['start_date'] ?? '';
            $endDate = $leave['end_date'] ?? '';
            
            if ($startDate <= $today && $endDate >= $today) {
                $onLeave++;
            }
        }
        
        $accountedFor = $presentToday + $lateToday + $absentToday + $onLeave;
        $untrackedAbsences = max(0, $totalEmployees - $accountedFor);
        $absentToday += $untrackedAbsences;
        
        return [
            'totalEmployees' => $totalEmployees,
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'onLeave' => $onLeave,
            'absentToday' => $absentToday
        ];
    }
    
    /**
     * Get dashboard chart data
     * 
     * @return array Chart data
     */
    private function getDashboardCharts(): array
    {
        return [
            'departments' => $this->getDepartmentHeadcount(),
            'attendanceTrend' => $this->getAttendanceTrend(7)
        ];
    }
    
    /**
     * Get department headcount breakdown
     * 
     * @return array Department headcount data
     */
    private function getDepartmentHeadcount(): array
    {
        // Get employee model from container
        $employeeModel = $this->container->resolve(\Models\Employee::class);
        
        // Get all active employees
        $employees = $employeeModel->where(['is_active' => true])->get();
        
        // Aggregate by department
        $departments = [];
        
        foreach ($employees as $employee) {
            $dept = $employee['department'] ?? 'Unassigned';
            
            if (!isset($departments[$dept])) {
                $departments[$dept] = 0;
            }
            
            $departments[$dept]++;
        }
        
        // Sort by count descending
        arsort($departments);
        
        return $departments;
    }
    
    /**
     * Get attendance trend for the last N days
     * 
     * @param int $days Number of days to retrieve
     * @return array Attendance trend data
     */
    private function getAttendanceTrend(int $days = 7): array
    {
        // Get models from container
        $attendanceModel = $this->container->resolve(\Models\Attendance::class);
        $employeeModel = $this->container->resolve(\Models\Employee::class);
        
        // Get all active employee IDs
        $activeEmployees = $employeeModel->where(['is_active' => true])->get();
        $activeEmployeeIds = array_column($activeEmployees, 'id');
        
        $trend = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            
            // Get attendance records for this date
            $attendanceRecords = $attendanceModel->where(['date' => $date])->get();
            
            $dayData = [
                'date' => $date,
                'present' => 0,
                'late' => 0,
                'absent' => 0
            ];
            
            foreach ($attendanceRecords as $record) {
                // Skip attendance records for inactive employees
                if (!in_array($record['employee_id'], $activeEmployeeIds)) {
                    continue;
                }
                
                $status = $record['status'] ?? '';
                
                switch ($status) {
                    case 'Present':
                        $dayData['present']++;
                        break;
                    case 'Late':
                        $dayData['late']++;
                        break;
                    case 'Absent':
                        $dayData['absent']++;
                        break;
                }
            }
            
            $trend[] = $dayData;
        }
        
        return $trend;
    }
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin(): Response
    {
        return $this->redirect(base_url('/login'));
    }
    
    /**
     * Return access denied page
     */
    private function accessDenied(): Response
    {
        $html = $this->view->render('errors/403', [
            'title' => 'Access Denied - HRIS MVP',
            'message' => 'You do not have permission to access this resource.'
        ]);
        
        return new Response($html, 403, ['Content-Type' => 'text/html']);
    }
}
