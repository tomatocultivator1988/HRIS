<?php

namespace Controllers;

use Core\Controller;
use Core\Container;
use Core\Request;
use Core\Response;
use Services\AuthService;
use Models\User;
use Exception;

/**
 * PasswordController - Handles password management
 * 
 * Features:
 * - Change password (for all users)
 * - Force password change on first login (employees only)
 * - Admin password reset (admins can reset employee passwords)
 */
class PasswordController extends Controller
{
    private AuthService $authService;
    private User $userModel;
    
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container->resolve(AuthService::class);
        $this->userModel = $container->resolve(User::class);
    }
    
    /**
     * Show change password form
     *
     * @param Request $request
     * @return Response
     */
    public function changePasswordForm(Request $request): Response
    {
        // Get user from localStorage (frontend will handle this)
        // No need to check authentication here since this page should be accessible
        // even without full auth (for force password change scenario)
        
        return $this->view('auth/change-password', [
            'force_change' => false // Will be determined by frontend
        ]);
    }
    
    /**
     * Handle change password request
     *
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request): Response
    {
        try {
            $user = $request->getUser();
            
            if (!$user) {
                error_log('PasswordController::changePassword - No user found in request');
                return $this->error('Unauthorized', 401);
            }
            
            error_log('PasswordController::changePassword - User: ' . json_encode([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]));
            
            $input = $request->getJsonData();
            if (empty($input)) {
                $input = $request->getPostData();
            }
            
            error_log('PasswordController::changePassword - Input received: ' . json_encode(array_keys($input)));
            
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            
            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                error_log('PasswordController::changePassword - Missing required fields');
                return $this->error('All fields are required', 400);
            }
            
            if ($newPassword !== $confirmPassword) {
                error_log('PasswordController::changePassword - Passwords do not match');
                return $this->error('New passwords do not match', 400);
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if (!$passwordValidation['valid']) {
                error_log('PasswordController::changePassword - Password validation failed: ' . $passwordValidation['message']);
                return $this->error($passwordValidation['message'], 400);
            }
            
            error_log('PasswordController::changePassword - Calling authService->changePassword');
            
            // Change password via Supabase
            $result = $this->authService->changePassword(
                $user['email'],
                $currentPassword,
                $newPassword
            );
            
            error_log('PasswordController::changePassword - AuthService result: ' . json_encode($result));
            
            if (!$result['success']) {
                error_log('PasswordController::changePassword - Password change failed: ' . $result['message']);
                return $this->error($result['message'], 400);
            }
            
            // Update force_password_change flag if it was set
            error_log('PasswordController::changePassword - Checking force_password_change: ' . json_encode([
                'role' => $user['role'],
                'force_password_change' => $user['force_password_change'] ?? 'NOT SET',
                'condition_result' => ($user['role'] === 'employee' && ($user['force_password_change'] ?? false))
            ]));
            
            if ($user['role'] === 'employee' && ($user['force_password_change'] ?? false)) {
                error_log('PasswordController::changePassword - Updating force_password_change flag');
                $this->userModel->updateForcePasswordChange($user['id'], false);
            }
            
            // Update password_changed_at timestamp
            error_log('PasswordController::changePassword - Updating password_changed_at timestamp');
            $this->userModel->updatePasswordChangedAt($user['id'], $user['role']);
            
            // Log activity
            $this->logActivity('PASSWORD_CHANGED', [
                'user_id' => $user['id'],
                'role' => $user['role']
            ]);
            
            error_log('PasswordController::changePassword - Success!');
            return $this->success([], 'Password changed successfully');
            
        } catch (Exception $e) {
            error_log('PasswordController::changePassword Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->error('Failed to change password', 500);
        }
    }
    
    /**
     * Admin reset employee password
     *
     * @param Request $request
     * @return Response
     */
    public function adminResetPassword(Request $request): Response
    {
        try {
            $user = $request->getUser();
            
            // Only admins can reset passwords
            if (!$user || $user['role'] !== 'admin') {
                return $this->error('Unauthorized', 403);
            }
            
            $input = $request->getJsonData();
            if (empty($input)) {
                $input = $request->getPostData();
            }
            
            $employeeId = $input['employee_id'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $forceChange = $input['force_change'] ?? true;
            
            if (empty($employeeId) || empty($newPassword)) {
                return $this->error('Employee ID and new password are required', 400);
            }
            
            // Validate password strength
            $passwordValidation = $this->validatePasswordStrength($newPassword);
            if (!$passwordValidation['valid']) {
                return $this->error($passwordValidation['message'], 400);
            }
            
            // Get employee details
            $employee = $this->userModel->findByIdAndRole($employeeId, 'employee');
            
            if (!$employee) {
                return $this->error('Employee not found', 404);
            }
            
            // Reset password via Supabase (admin action)
            $result = $this->authService->adminResetPassword(
                $employee['email'],
                $newPassword
            );
            
            if (!$result['success']) {
                return $this->error($result['message'], 400);
            }
            
            // Set force_password_change flag
            $this->userModel->updateForcePasswordChange($employeeId, $forceChange);
            
            // Update password_changed_at timestamp
            $this->userModel->updatePasswordChangedAt($employeeId, 'employee');
            
            // Log activity
            $this->logActivity('ADMIN_PASSWORD_RESET', [
                'admin_id' => $user['id'],
                'employee_id' => $employeeId,
                'force_change' => $forceChange
            ]);
            
            return $this->success([
                'employee_id' => $employeeId,
                'force_change' => $forceChange
            ], 'Password reset successfully');
            
        } catch (Exception $e) {
            error_log('PasswordController::adminResetPassword Error: ' . $e->getMessage());
            return $this->error('Failed to reset password', 500);
        }
    }
    
    /**
     * Check if user needs to change password
     *
     * @param Request $request
     * @return Response
     */
    public function checkForcePasswordChange(Request $request): Response
    {
        try {
            $user = $request->getUser();
            
            if (!$user) {
                return $this->error('Unauthorized', 401);
            }
            
            $forceChange = false;
            
            // Only employees can be forced to change password
            if ($user['role'] === 'employee') {
                $forceChange = $user['force_password_change'] ?? false;
            }
            
            return $this->success([
                'force_password_change' => $forceChange,
                'password_changed_at' => $user['password_changed_at'] ?? null
            ]);
            
        } catch (Exception $e) {
            error_log('PasswordController::checkForcePasswordChange Error: ' . $e->getMessage());
            return $this->error('Failed to check password status', 500);
        }
    }
    
    /**
     * Validate password strength
     *
     * @param string $password
     * @return array
     */
    private function validatePasswordStrength(string $password): array
    {
        if (strlen($password) < 8) {
            return [
                'valid' => false,
                'message' => 'Password must be at least 8 characters long'
            ];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one uppercase letter'
            ];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one lowercase letter'
            ];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one number'
            ];
        }
        
        return ['valid' => true];
    }
}
