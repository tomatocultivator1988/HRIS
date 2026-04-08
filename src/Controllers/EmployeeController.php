<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\NotFoundException;
use Core\View;
use Services\EmployeeService;

/**
 * EmployeeController - Handles HTTP requests for employee management
 * 
 * This controller coordinates employee-related operations, handling HTTP requests
 * and delegating business logic to the EmployeeService. Supports both web views
 * and API responses with consistent error handling.
 */
class EmployeeController extends Controller
{
    private EmployeeService $employeeService;
    private View $view;
    
    /**
     * Constructor - Initialize with EmployeeService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->employeeService = $container->resolve(EmployeeService::class);
        $this->view = new View();
    }
    
    /**
     * List employees with filtering and pagination
     * GET /api/employees
     */
    public function index(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get query parameters
            $filters = [
                'search' => $this->getQueryParam('search', ''),
                'department' => $this->getQueryParam('department', ''),
                'status' => $this->getQueryParam('status', ''),
                'employment_status' => $this->getQueryParam('employment_status', ''),
                'position' => $this->getQueryParam('position', ''),
                'limit' => min(max(intval($this->getQueryParam('limit', 50)), 1), 100),
                'offset' => max(intval($this->getQueryParam('offset', 0)), 0),
                'order_by' => $this->getQueryParam('order_by', 'created_at'),
                'order_dir' => strtoupper($this->getQueryParam('order_dir', 'DESC'))
            ];
            
            // Validate order direction
            if (!in_array($filters['order_dir'], ['ASC', 'DESC'])) {
                $filters['order_dir'] = 'DESC';
            }
            
            // Validate order by field
            $allowedOrderFields = [
                'employee_id', 'first_name', 'last_name', 'department', 
                'position', 'employment_status', 'date_hired', 'created_at'
            ];
            
            if (!in_array($filters['order_by'], $allowedOrderFields)) {
                $filters['order_by'] = 'created_at';
            }
            
            // Validate employment status if provided
            if (!empty($filters['employment_status'])) {
                $validStatuses = ['Regular', 'Probationary', 'Contractual', 'Part-time'];
                if (!in_array($filters['employment_status'], $validStatuses)) {
                    return $this->error('Invalid employment status. Must be one of: ' . implode(', ', $validStatuses), 400);
                }
            }
            
            // Get employees from service
            $result = $this->employeeService->getEmployees($filters);
            
            // Log activity
            $this->logActivity('VIEW_EMPLOYEES', [
                'filters' => $filters,
                'results_count' => count($result['employees'])
            ]);
            
            return $this->success($result, 'Employee list retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get specific employee by ID
     * GET /api/employees/{id}
     */
    public function show(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $employeeId = $this->getRouteParam('id');
            
            if (empty($employeeId)) {
                return $this->error('Employee ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $employeeId)) {
                return $this->error('Invalid employee ID format', 400);
            }
            
            $employee = $this->employeeService->getEmployeeById($employeeId);
            
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }
            
            // Log activity
            $this->logActivity('VIEW_EMPLOYEE', ['employee_id' => $employeeId]);
            
            return $this->success(['employee' => $employee], 'Employee retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Create new employee
     * POST /api/employees
     */
    public function create(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Create employee through service
            $employee = $this->employeeService->createEmployee($data);
            
            // Log activity
            $this->logActivity('CREATE_EMPLOYEE', [
                'employee_id' => $employee['employee_id']
            ]);
            
            return $this->success(['employee' => $employee], 'Employee created successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Update existing employee
     * PUT /api/employees/{id}
     */
    public function update(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $employeeId = $this->getRouteParam('id');
            $data = $this->getJsonData();
            
            if (empty($employeeId)) {
                return $this->error('Employee ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $employeeId)) {
                return $this->error('Invalid employee ID format', 400);
            }
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Update employee through service
            $employee = $this->employeeService->updateEmployee($employeeId, $data);
            
            // Log activity
            $this->logActivity('UPDATE_EMPLOYEE', [
                'employee_id' => $employeeId,
                'fields_updated' => array_keys($data)
            ]);
            
            return $this->success(['employee' => $employee], 'Employee updated successfully');
            
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
     * Delete (deactivate) employee
     * DELETE /api/employees/{id}
     */
    public function delete(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $employeeId = $this->getRouteParam('id');
            
            if (empty($employeeId)) {
                return $this->error('Employee ID is required', 400);
            }
            
            // Validate UUID format
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $employeeId)) {
                return $this->error('Invalid employee ID format', 400);
            }
            
            // Deactivate employee through service
            $result = $this->employeeService->deactivateEmployee($employeeId);
            
            // Log activity
            $this->logActivity('DEACTIVATE_EMPLOYEE', [
                'employee_id' => $employeeId
            ]);
            
            return $this->success($result, 'Employee deactivated successfully (soft delete)');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Search employees with advanced filtering
     * GET /api/employees/search
     */
    public function search(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // Get search parameters
            $searchParams = [
                'query' => $this->getQueryParam('q', ''),
                'department' => $this->getQueryParam('department', ''),
                'status' => $this->getQueryParam('status', ''),
                'employment_status' => $this->getQueryParam('employment_status', ''),
                'position' => $this->getQueryParam('position', ''),
                'date_hired_from' => $this->getQueryParam('date_hired_from', ''),
                'date_hired_to' => $this->getQueryParam('date_hired_to', ''),
                'limit' => min(max(intval($this->getQueryParam('limit', 20)), 1), 100),
                'offset' => max(intval($this->getQueryParam('offset', 0)), 0),
                'sort_by' => $this->getQueryParam('sort_by', 'created_at'),
                'sort_order' => strtoupper($this->getQueryParam('sort_order', 'DESC'))
            ];
            
            // Validate sort parameters
            $allowedSortFields = [
                'employee_id', 'first_name', 'last_name', 'department', 
                'position', 'employment_status', 'date_hired', 'created_at'
            ];
            
            if (!in_array($searchParams['sort_by'], $allowedSortFields)) {
                $searchParams['sort_by'] = 'created_at';
            }
            
            if (!in_array($searchParams['sort_order'], ['ASC', 'DESC'])) {
                $searchParams['sort_order'] = 'DESC';
            }
            
            // Validate date parameters
            if (!empty($searchParams['date_hired_from']) && !strtotime($searchParams['date_hired_from'])) {
                return $this->error('Invalid date_hired_from format', 400);
            }
            
            if (!empty($searchParams['date_hired_to']) && !strtotime($searchParams['date_hired_to'])) {
                return $this->error('Invalid date_hired_to format', 400);
            }
            
            // Search employees through service
            $result = $this->employeeService->searchEmployees($searchParams);
            
            // Log activity
            $this->logActivity('SEARCH_EMPLOYEES', [
                'query' => $searchParams['query'],
                'results_count' => count($result['employees'])
            ]);
            
            return $this->success($result, 'Employee search completed successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get employee profile (for self-service or admin)
     * GET /api/employees/profile
     */
    public function profile(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $employeeId = null;
            
            // For admin users, allow getting specific employee profile by ID
            if ($user['role'] === 'admin' && !empty($this->getQueryParam('id'))) {
                $employeeId = $this->getQueryParam('id');
            }
            
            // Get profile through service
            $profile = $this->employeeService->getEmployeeProfile($user, $employeeId);
            
            // Log activity
            $this->logActivity('VIEW_PROFILE', [
                'target_employee_id' => $employeeId ?? 'self'
            ]);
            
            return $this->success(['employee' => $profile], 'Profile retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Update employee profile (for self-service or admin)
     * PUT /api/employees/profile
     */
    public function updateProfile(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // Update profile through service
            $profile = $this->employeeService->updateEmployeeProfile($user, $data);
            
            // Log activity
            $this->logActivity('UPDATE_PROFILE', [
                'fields_updated' => array_keys($data)
            ]);
            
            return $this->success(['employee' => $profile], 'Profile updated successfully');
            
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
    
    // API methods for backward compatibility with legacy endpoints
    
    /**
     * API version of index method (for /api/employees/list.php compatibility)
     */
    public function apiIndex(Request $request): Response
    {
        return $this->index($request);
    }
    
    /**
     * API version of search method (for /api/employees/search.php compatibility)
     */
    public function apiSearch(Request $request): Response
    {
        return $this->search($request);
    }
    
    /**
     * API version of create method (for /api/employees/create.php compatibility)
     */
    public function apiCreate(Request $request): Response
    {
        return $this->create($request);
    }
    
    /**
     * API version of show method (for /api/employees/profile.php compatibility)
     */
    public function apiShow(Request $request): Response
    {
        // Check if this is a profile request (no ID parameter)
        $employeeId = $this->getRouteParam('id');
        
        if (empty($employeeId)) {
            return $this->profile($request);
        }
        
        return $this->show($request);
    }
    
    /**
     * API version of update method (for /api/employees/update.php compatibility)
     */
    public function apiUpdate(Request $request): Response
    {
        // Handle both PUT and POST methods for backward compatibility
        $data = $this->getJsonData();
        
        if (empty($data)) {
            return $this->error('Request body is required', 400);
        }
        
        // Check if ID is in the data (legacy format)
        $employeeId = $this->getRouteParam('id') ?? $data['id'] ?? null;
        
        if (empty($employeeId)) {
            return $this->error('Employee ID is required', 400);
        }
        
        // Set the route parameter if it came from the data
        if (!$this->getRouteParam('id') && !empty($data['id'])) {
            $currentParams = $this->request->getRouteParameters();
            $currentParams['id'] = $data['id'];
            $this->request->setRouteParameters($currentParams);
        }
        
        return $this->update($request);
    }
    
    /**
     * API version of delete method (for /api/employees/delete.php compatibility)
     */
    public function apiDelete(Request $request): Response
    {
        // Handle both DELETE and POST methods for backward compatibility
        $employeeId = $this->getRouteParam('id');
        
        if (empty($employeeId)) {
            // Try to get ID from query parameter or JSON body
            $employeeId = $this->getQueryParam('id');
            
            if (empty($employeeId) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = $this->getJsonData();
                $employeeId = $data['id'] ?? null;
            }
            
            if (empty($employeeId)) {
                return $this->error('Employee ID is required', 400);
            }
            
            // Set the route parameter
            $currentParams = $this->request->getRouteParameters();
            $currentParams['id'] = $employeeId;
            $this->request->setRouteParameters($currentParams);
        }
        
        return $this->delete($request);
    }
    
    // HTML view methods for web interface
    
    /**
     * Display employee list page
     * GET /employees
     */
    public function indexView(Request $request): Response
    {
        try {
            // Authentication is handled by JavaScript on the client side
            // No backend auth required for web pages
            
            // Render the employees index view directly without base layout
            ob_start();
            include __DIR__ . '/../Views/employees/index.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (AuthenticationException $e) {
            return $this->redirectToLogin();
        } catch (AuthorizationException $e) {
            return $this->accessDenied();
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display employee profile page
     * GET /employees/{id}
     */
    public function showView(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $employeeId = $this->getRouteParam('id');
            
            if (empty($employeeId)) {
                return $this->notFound();
            }
            
            // Check permissions
            $canEdit = false;
            $isOwnProfile = false;
            
            if ($user['role'] === 'admin') {
                $canEdit = true;
            } else {
                // Check if this is the user's own profile
                $userEmployee = $this->employeeService->getEmployeeById($user['id']);
                if ($userEmployee && $userEmployee['id'] === $employeeId) {
                    $isOwnProfile = true;
                    $canEdit = true; // Limited editing for own profile
                }
            }
            
            // Get employee data
            $employee = $this->employeeService->getEmployeeById($employeeId);
            
            if (!$employee) {
                return $this->notFound();
            }
            
            // Get manager name if available
            if (!empty($employee['manager_id'])) {
                $manager = $this->employeeService->getEmployeeById($employee['manager_id']);
                $employee['manager_name'] = $manager ? $manager['full_name'] : null;
            }
            
            // Render the view
            $html = $this->view->render('employees/profile', [
                'title' => ($isOwnProfile ? 'My Profile' : 'Employee Profile') . ' - HRIS MVP',
                'user' => $user,
                'employee' => $employee,
                'canEdit' => $canEdit,
                'isOwnProfile' => $isOwnProfile
            ]);
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (AuthenticationException $e) {
            return $this->redirectToLogin();
        } catch (NotFoundException $e) {
            return $this->notFound();
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    /**
     * Display employee creation form
     * GET /employees/create
     */
    public function createForm(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            // For now, redirect to the list page with modal trigger
            // In a full implementation, this could be a separate page
            return $this->redirect('/employees?action=create');
            
        } catch (AuthenticationException $e) {
            return $this->redirectToLogin();
        } catch (AuthorizationException $e) {
            return $this->accessDenied();
        }
    }
    
    /**
     * Display employee edit form
     * GET /employees/{id}/edit
     */
    public function editForm(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $employeeId = $this->getRouteParam('id');
            
            if (empty($employeeId)) {
                return $this->notFound();
            }
            
            // For now, redirect to the profile page with edit mode
            // In a full implementation, this could be a separate page
            return $this->redirect("/employees/{$employeeId}?action=edit");
            
        } catch (AuthenticationException $e) {
            return $this->redirectToLogin();
        } catch (AuthorizationException $e) {
            return $this->accessDenied();
        }
    }
    
    /**
     * Display employee profile page (web route)
     * GET /profile
     */
    public function profileView(Request $request): Response
    {
        try {
            // For web routes, authentication is handled by JavaScript
            // The page will render, and auth.js will verify the token and redirect if needed
            
            // Render the profile view directly without base layout
            // Since profile.php is now a complete standalone HTML page
            ob_start();
            include __DIR__ . '/../Views/employees/profile.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->handleViewException($e);
        }
    }
    
    // Helper methods for view responses
    
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
     * Return not found page
     */
    private function notFound(): Response
    {
        $html = $this->view->render('errors/404', [
            'title' => 'Not Found - HRIS MVP',
            'message' => 'The requested resource was not found.'
        ]);
        
        return new Response($html, 404, ['Content-Type' => 'text/html']);
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
}
