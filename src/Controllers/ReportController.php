<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\View;
use Services\ReportService;

/**
 * ReportController - Handles report generation requests
 * 
 * This controller manages report generation for attendance, leave, and headcount
 * with consistent error handling and response formatting.
 * 
 * Requirements: 3.1, 3.4, 4.1, 4.3
 */
class ReportController extends Controller
{
    private ReportService $reportService;
    private View $view;
    
    /**
     * Constructor - Initialize with ReportService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->reportService = $container->resolve(ReportService::class);
        $this->view = new View();
    }
    
    /**
     * Display reports page (web route)
     * GET /reports
     */
    public function index(Request $request): Response
    {
        try {
            // Authentication is handled by JavaScript on the client side
            // No backend auth required for web pages
            
            // Render the reports index view directly without base layout
            ob_start();
            include __DIR__ . '/../Views/reports/index.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display attendance reports page
     * GET /reports/attendance
     */
    public function attendanceView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/reports/attendance.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display leave reports page
     * GET /reports/leave
     */
    public function leaveView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/reports/leave.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display employee analytics page
     * GET /reports/employees
     */
    public function employeesView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/reports/employees.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display productivity metrics page
     * GET /reports/productivity
     */
    public function productivityView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/reports/productivity.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin(): Response
    {
        return $this->redirect('/login');
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
    
    /**
     * Handle view exceptions
     */
    private function handleViewException(\Exception $e): Response
    {
        error_log('View Exception: ' . $e->getMessage());
        
        $html = $this->view->render('errors/500', [
            'title' => 'Server Error - HRIS MVP',
            'message' => 'An internal server error occurred.'
        ]);
        
        return new Response($html, 500, ['Content-Type' => 'text/html']);
    }
    
    /**
     * Generate attendance report
     * GET /api/reports/attendance
     */
    public function attendance(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get request parameters
            $startDate = $this->getQueryParam('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $this->getQueryParam('end_date', date('Y-m-d'));
            
            // Validate dates
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || 
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                return $this->error('Invalid date format. Use YYYY-MM-DD', 400);
            }
            
            // Build filters
            $filters = [];
            
            if (!empty($this->getQueryParam('employee_id'))) {
                $filters['employee_id'] = intval($this->getQueryParam('employee_id'));
            }
            
            if (!empty($this->getQueryParam('department'))) {
                $filters['department'] = trim($this->getQueryParam('department'));
            }
            
            if (!empty($this->getQueryParam('status'))) {
                $filters['status'] = trim($this->getQueryParam('status'));
            }
            
            // Generate report
            $report = $this->reportService->generateAttendanceReport($startDate, $endDate, $filters);
            
            // Log activity
            $this->logActivity('GENERATE_ATTENDANCE_REPORT', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters
            ]);
            
            return $this->success(['report' => $report], 'Attendance report generated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Generate leave report
     * GET /api/reports/leave
     */
    public function leave(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get request parameters
            $startDate = $this->getQueryParam('start_date', date('Y-m-d', strtotime('-30 days')));
            $endDate = $this->getQueryParam('end_date', date('Y-m-d'));
            
            // Validate dates
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || 
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                return $this->error('Invalid date format. Use YYYY-MM-DD', 400);
            }
            
            // Build filters
            $filters = [];
            
            if (!empty($this->getQueryParam('employee_id'))) {
                $filters['employee_id'] = intval($this->getQueryParam('employee_id'));
            }
            
            if (!empty($this->getQueryParam('department'))) {
                $filters['department'] = trim($this->getQueryParam('department'));
            }
            
            if (!empty($this->getQueryParam('leave_type_id'))) {
                $filters['leave_type_id'] = intval($this->getQueryParam('leave_type_id'));
            }
            
            if (!empty($this->getQueryParam('status'))) {
                $filters['status'] = trim($this->getQueryParam('status'));
            }
            
            // Generate report
            $report = $this->reportService->generateLeaveReport($startDate, $endDate, $filters);
            
            // Log activity
            $this->logActivity('GENERATE_LEAVE_REPORT', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filters' => $filters
            ]);
            
            return $this->success(['report' => $report], 'Leave report generated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Generate headcount report
     * GET /api/reports/headcount
     */
    public function headcount(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Build filters
            $filters = [];
            
            if (!empty($this->getQueryParam('department'))) {
                $filters['department'] = trim($this->getQueryParam('department'));
            }
            
            if (!empty($this->getQueryParam('employment_status'))) {
                $filters['employment_status'] = trim($this->getQueryParam('employment_status'));
            }
            
            if ($this->getQueryParam('is_active') !== null) {
                $filters['is_active'] = filter_var($this->getQueryParam('is_active'), FILTER_VALIDATE_BOOLEAN);
            }
            
            // Generate report
            $report = $this->reportService->generateHeadcountReport($filters);
            
            // Log activity
            $this->logActivity('GENERATE_HEADCOUNT_REPORT', [
                'filters' => $filters
            ]);
            
            return $this->success(['report' => $report], 'Headcount report generated successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
