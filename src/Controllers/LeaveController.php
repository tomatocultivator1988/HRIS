<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\NotFoundException;
use Services\LeaveService;

/**
 * LeaveController - Handles HTTP requests for leave management
 * 
 * This controller coordinates leave-related operations, handling HTTP requests
 * and delegating business logic to the LeaveService.
 */
class LeaveController extends Controller
{
    private LeaveService $leaveService;
    
    /**
     * Constructor - Initialize with LeaveService dependency
     */
    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->leaveService = $container->resolve(LeaveService::class);
    }
    
    /**
     * Submit a new leave request
     * POST /api/leave/request
     */
    public function request(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            $data = $this->getJsonData();
            
            if (empty($data)) {
                return $this->error('Request body is required', 400);
            }
            
            // For employee users, ensure they can only submit requests for themselves
            if ($user['role'] !== 'admin') {
                $data['employee_id'] = $user['id'];
            } elseif (empty($data['employee_id'])) {
                return $this->error('Employee ID is required for admin users', 400);
            }
            
            // Submit the leave request
            $result = $this->leaveService->submitLeaveRequest($data);
            
            // Log activity
            $this->logActivity('SUBMIT_LEAVE_REQUEST', [
                'leave_request_id' => $result['id'],
                'employee_id' => $result['employee_id']
            ]);
            
            return $this->success(['leave_request' => $result], 'Leave request submitted successfully');
            
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
     * Approve a leave request
     * PUT /api/leave/{id}/approve
     */
    public function approve(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $user = $this->getAuthenticatedUser();
            
            // Get request ID from route parameter
            $requestId = $this->getRouteParam('id');
            
            if (empty($requestId)) {
                return $this->error('Leave request ID is required', 400);
            }
            
            $reviewerId = $user['id'];
            
            // Approve the leave request
            $result = $this->leaveService->approveLeaveRequest($requestId, $reviewerId);
            
            // Log activity
            $this->logActivity('APPROVE_LEAVE_REQUEST', [
                'leave_request_id' => $requestId,
                'reviewer_id' => $reviewerId
            ]);
            
            return $this->success(['leave_request' => $result], 'Leave request approved successfully');
            
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
     * Deny a leave request
     * PUT /api/leave/{id}/deny
     */
    public function deny(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $user = $this->getAuthenticatedUser();
            $data = $this->getJsonData();
            
            // Get request ID from route parameter
            $requestId = $this->getRouteParam('id');
            
            if (empty($requestId)) {
                return $this->error('Leave request ID is required', 400);
            }
            
            $reviewerId = $user['id'];
            $denialReason = $data['denial_reason'] ?? '';
            
            // Deny the leave request
            $result = $this->leaveService->denyLeaveRequest($requestId, $reviewerId, $denialReason);
            
            // Log activity
            $this->logActivity('DENY_LEAVE_REQUEST', [
                'leave_request_id' => $requestId,
                'reviewer_id' => $reviewerId
            ]);
            
            return $this->success(['leave_request' => $result], 'Leave request denied successfully');
            
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
     * Get pending leave requests (admin only)
     * GET /api/leave/pending
     */
    public function pending(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $result = $this->leaveService->getPendingLeaveRequests();
            
            // Log activity
            $this->logActivity('VIEW_PENDING_LEAVE_REQUESTS', [
                'count' => count($result)
            ]);
            
            return $this->success([
                'pending_requests' => $result,
                'total' => count($result)
            ], 'Pending leave requests retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get approved leave requests (admin only)
     * GET /api/leave/approved
     */
    public function approved(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $result = $this->leaveService->getApprovedLeaveRequests();
            
            return $this->success([
                'approved_requests' => $result,
                'total' => count($result)
            ], 'Approved leave requests retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get denied leave requests (admin only)
     * GET /api/leave/denied
     */
    public function denied(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $result = $this->leaveService->getDeniedLeaveRequests();
            
            return $this->success([
                'denied_requests' => $result,
                'total' => count($result)
            ], 'Denied leave requests retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get all leave requests with optional filters (admin only)
     * GET /api/leave/all
     */
    public function all(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $search = $request->getQueryParameter('search');
            $status = $request->getQueryParameter('status');
            $leaveType = $request->getQueryParameter('leave_type');
            
            $result = $this->leaveService->getAllLeaveRequests($search, $status, $leaveType);
            
            return $this->success($result, 'Leave requests retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get leave request history
     * GET /api/leave/history
     */
    public function history(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can view any employee's history, or their own if no employee_id provided
                $employeeId = $this->getQueryParam('employee_id');
                if (empty($employeeId)) {
                    // If no employee_id provided, show admin's own history
                    $employeeId = $user['id'];
                }
            } else {
                // Employee can only view their own history
                $employeeId = $user['id'];
            }
            
            $limit = min(max(intval($this->getQueryParam('limit', 50)), 1), 500);
            $offset = max(intval($this->getQueryParam('offset', 0)), 0);
            
            $result = $this->leaveService->getLeaveHistory($employeeId, $limit, $offset);
            
            // Log activity
            $this->logActivity('VIEW_LEAVE_HISTORY', [
                'employee_id' => $employeeId
            ]);
            
            return $this->success($result, 'Leave history retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get employee leave balance
     * GET /api/leave/balance
     */
    public function balance(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can view any employee's balance
                $employeeId = $this->getQueryParam('employee_id');
                if (empty($employeeId)) {
                    return $this->error('Employee ID is required for admin users', 400);
                }
            } else {
                // Employee can only view their own balance
                $employeeId = $user['id'];
            }
            
            // Get leave balance from service
            $balance = $this->leaveService->getLeaveBalance($employeeId);
            
            // Log activity
            $this->logActivity('VIEW_LEAVE_BALANCE', [
                'employee_id' => $employeeId
            ]);
            
            return $this->success(['balance' => $balance], 'Leave balance retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get available leave types
     * GET /api/leave/types
     */
    public function types(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            // Get leave types from service
            $types = $this->leaveService->getLeaveTypes();
            
            // Log activity
            $this->logActivity('VIEW_LEAVE_TYPES');
            
            return $this->success(['types' => $types], 'Leave types retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Get employee leave credits summary
     * GET /api/leave/credits
     */
    public function credits(Request $request): Response
    {
        try {
            $this->requireAuth();
            
            $user = $this->getAuthenticatedUser();
            
            // Determine employee ID
            $employeeId = null;
            
            if ($user['role'] === 'admin') {
                // Admin can view any employee's credits
                $employeeId = $this->getQueryParam('employee_id');
                if (empty($employeeId)) {
                    return $this->error('Employee ID is required for admin users', 400);
                }
            } else {
                // Employee can only view their own credits
                $employeeId = $user['id'];
            }
            
            // Get leave credits from service
            $credits = $this->leaveService->getLeaveCredits($employeeId);
            
            // Log activity
            $this->logActivity('VIEW_LEAVE_CREDITS', [
                'employee_id' => $employeeId
            ]);
            
            return $this->success(['credits' => $credits], 'Leave credits retrieved successfully');
            
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    /**
     * Display leave requests page
     * GET /leave
     */
    public function indexView(Request $request): Response
    {
        try {
            // Authentication is handled by JavaScript on the client side
            // No backend auth required for web pages
            
            // Render the leave index view directly without base layout
            ob_start();
            include __DIR__ . '/../Views/leave/index.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            error_log('View Exception: ' . $e->getMessage());
            return new Response('Internal Server Error', 500, ['Content-Type' => 'text/html']);
        }
    }
}
