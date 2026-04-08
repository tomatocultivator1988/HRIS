<?php

namespace Services;

use Exception;

/**
 * AuthService - Handles authentication business logic
 * 
 * This service encapsulates all authentication-related business logic including
 * user authentication, token management, role determination, and activity logging.
 */
class AuthService
{
    private array $config;
    private string $supabaseUrl;
    private string $supabaseAnonKey;
    private string $supabaseServiceKey;
    
    public function __construct()
    {
        $this->loadConfig();
    }
    
    /**
     * Load Supabase configuration
     */
    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/supabase.php';
        $this->config = require $configFile;
        
        $this->supabaseUrl = $this->config['url'];
        $this->supabaseAnonKey = $this->config['anon_key'];
        $this->supabaseServiceKey = $this->config['service_key'];
    }
    
    /**
     * Authenticate user with email and password
     *
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    public function authenticate(string $email, string $password): array
    {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required'
                ];
            }
            
            if (!$this->validateEmail($email)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format'
                ];
            }
            
            // Authenticate with Supabase Auth API
            $authResult = $this->authenticateWithSupabase($email, $password);
            
            if (!$authResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            $userData = $authResult['data'];
            $user = $userData['user'];
            
            // Get user role and details
            $roleResult = $this->getUserRole($user['id']);
            
            if (!$roleResult['success']) {
                return [
                    'success' => false,
                    'message' => 'User account not found or inactive'
                ];
            }
            
            // Log successful login
            $this->logActivity($roleResult['userDetails']['id'], $roleResult['role'], 'LOGIN');
            
            return [
                'success' => true,
                'access_token' => $userData['access_token'],
                'refresh_token' => $userData['refresh_token'] ?? null,
                'user' => [
                    'id' => $roleResult['userDetails']['id'],
                    'supabase_user_id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $roleResult['role'],
                    'name' => $this->formatUserName($roleResult['userDetails'], $roleResult['role']),
                    'first_name' => $roleResult['userDetails']['first_name'] ?? null,
                    'last_name' => $roleResult['userDetails']['last_name'] ?? null,
                    'employee_id' => $roleResult['userDetails']['employee_id'] ?? null,
                    'department' => $roleResult['userDetails']['department'] ?? null,
                    'position' => $roleResult['userDetails']['position'] ?? null,
                    'is_active' => (bool) ($roleResult['userDetails']['is_active'] ?? true)
                ]
            ];
            
        } catch (Exception $e) {
            error_log('AuthService::authenticate Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Authentication failed'
            ];
        }
    }
    
    /**
     * Validate JWT token
     *
     * @param string $token JWT token
     * @return array Validation result
     */
    public function validateToken(string $token): array
    {
        try {
            $verifyResult = $this->verifyJWTToken($token);
            
            if (!$verifyResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ];
            }
            
            $user = $verifyResult['user'];
            
            // Get user role and details
            $roleResult = $this->getUserRole($user['id']);
            
            if (!$roleResult['success']) {
                return [
                    'success' => false,
                    'message' => 'User account not found or inactive'
                ];
            }
            
            return [
                'success' => true,
                'user' => [
                    'id' => $roleResult['userDetails']['id'],
                    'supabase_user_id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $roleResult['role'],
                    'name' => $this->formatUserName($roleResult['userDetails'], $roleResult['role']),
                    'first_name' => $roleResult['userDetails']['first_name'] ?? null,
                    'last_name' => $roleResult['userDetails']['last_name'] ?? null,
                    'employee_id' => $roleResult['userDetails']['employee_id'] ?? null,
                    'department' => $roleResult['userDetails']['department'] ?? null,
                    'position' => $roleResult['userDetails']['position'] ?? null,
                    'is_active' => (bool) ($roleResult['userDetails']['is_active'] ?? true),
                    'force_password_change' => (bool) ($roleResult['userDetails']['force_password_change'] ?? false),
                    'password_changed_at' => $roleResult['userDetails']['password_changed_at'] ?? null
                ]
            ];
            
        } catch (Exception $e) {
            error_log('AuthService::validateToken Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Token validation failed'
            ];
        }
    }
    
    /**
     * Get user role and details from database
     *
     * @param string $supabaseUserId Supabase user ID
     * @return array Role result
     */
    public function getUserRole(string $supabaseUserId): array
    {
        try {
            // Check if user is an admin
            $adminCheck = $this->makeSupabaseRequest(
                $this->config['tables']['admins'] . '?supabase_user_id=eq.' . $supabaseUserId . '&is_active=eq.true',
                'GET',
                null,
                true
            );
            
            if ($adminCheck['success'] && !empty($adminCheck['data'])) {
                return [
                    'success' => true,
                    'role' => 'admin',
                    'userDetails' => $adminCheck['data'][0]
                ];
            }
            
            // Check if user is an employee
            $employeeCheck = $this->makeSupabaseRequest(
                $this->config['tables']['employees'] . '?supabase_user_id=eq.' . $supabaseUserId . '&is_active=eq.true',
                'GET',
                null,
                true
            );
            
            if ($employeeCheck['success'] && !empty($employeeCheck['data'])) {
                return [
                    'success' => true,
                    'role' => 'employee',
                    'userDetails' => $employeeCheck['data'][0]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'User not found or inactive'
            ];
            
        } catch (Exception $e) {
            error_log('AuthService::getUserRole Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to determine user role'
            ];
        }
    }
    
    /**
     * Log user activity
     *
     * @param string $userId User ID
     * @param string $userRole User role
     * @param string $action Action performed
     * @return void
     */
    public function logActivity(string $userId, string $userRole, string $action): void
    {
        try {
            $logData = [
                'user_id' => $userId,
                'user_role' => $userRole,
                'action' => $action,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $this->makeSupabaseRequest(
                $this->config['tables']['audit_log'],
                'POST',
                $logData,
                true
            );
            
        } catch (Exception $e) {
            error_log('AuthService::logActivity Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Authenticate user with Supabase Auth API
     *
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    private function authenticateWithSupabase(string $email, string $password): array
    {
        $url = $this->config['auth_url'] . 'token?grant_type=password';
        
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->supabaseAnonKey
        ];
        
        $response = $this->makeCurlRequest($url, 'POST', $data, $headers);
        
        if ($response['success'] && isset($response['data']['access_token'])) {
            return [
                'success' => true,
                'data' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Authentication failed'
        ];
    }
    
    /**
     * Verify JWT token with Supabase
     *
     * @param string $token JWT token
     * @return array Verification result
     */
    private function verifyJWTToken(string $token): array
    {
        $url = $this->config['auth_url'] . 'user';
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'apikey: ' . $this->supabaseAnonKey
        ];
        
        $response = $this->makeCurlRequest($url, 'GET', null, $headers);
        
        if ($response['success'] && isset($response['data']['id'])) {
            return [
                'success' => true,
                'user' => $response['data']
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Token verification failed'
        ];
    }
    
    /**
     * Make request to Supabase REST API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param bool $useServiceKey Use service key instead of anon key
     * @return array Response
     */
    private function makeSupabaseRequest(string $endpoint, string $method = 'GET', ?array $data = null, bool $useServiceKey = false): array
    {
        $url = $this->config['api_url'] . $endpoint;
        $apiKey = $useServiceKey ? $this->supabaseServiceKey : $this->supabaseAnonKey;
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey
        ];
        
        return $this->makeCurlRequest($url, $method, $data, $headers);
    }
    
    /**
     * Make HTTP request using cURL
     *
     * @param string $url Request URL
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param array $headers Request headers
     * @return array Response
     */
    private function makeCurlRequest(string $url, string $method, ?array $data, array $headers): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'] ?? 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->config['ssl_verify'] ?? true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'status_code' => 0
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $decodedResponse,
            'status_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    /**
     * Validate email format
     *
     * @param string $email Email to validate
     * @return bool True if valid
     */
    private function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Format user name based on role
     *
     * @param array $userDetails User details
     * @param string $role User role
     * @return string Formatted name
     */
    private function formatUserName(array $userDetails, string $role): string
    {
        if ($role === 'admin') {
            return $userDetails['name'] ?? 'Admin User';
        }
        
        $firstName = $userDetails['first_name'] ?? '';
        $lastName = $userDetails['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Employee';
    }
    
    /**
     * Sanitize input data
     *
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    private function sanitizeInput(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Change user password
     *
     * @param string $email User email
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Result
     */
    public function changePassword(string $email, string $currentPassword, string $newPassword): array
    {
        try {
            error_log('AuthService::changePassword - Starting password change for: ' . $email);
            
            // First authenticate with current password
            $authResult = $this->authenticateWithSupabase($email, $currentPassword);
            
            error_log('AuthService::changePassword - Auth result: ' . json_encode([
                'success' => $authResult['success']
            ]));
            
            if (!$authResult['success']) {
                error_log('AuthService::changePassword - Current password is incorrect');
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            $accessToken = $authResult['data']['access_token'];
            error_log('AuthService::changePassword - Got access token: ' . substr($accessToken, 0, 30) . '...');
            
            // Update password using Supabase Auth API
            $url = $this->config['auth_url'] . 'user';
            error_log('AuthService::changePassword - Update URL: ' . $url);
            
            $data = [
                'password' => $newPassword
            ];
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'apikey: ' . $this->supabaseAnonKey
            ];
            
            error_log('AuthService::changePassword - Calling Supabase to update password');
            $response = $this->makeCurlRequest($url, 'PUT', $data, $headers);
            
            error_log('AuthService::changePassword - Supabase response: ' . json_encode([
                'success' => $response['success'],
                'status_code' => $response['status_code'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'has_error' => isset($response['error']),
                'raw_response' => substr($response['raw_response'] ?? '', 0, 200)
            ]));
            
            if (isset($response['error'])) {
                error_log('AuthService::changePassword - Supabase error: ' . json_encode($response['error']));
            }
            
            if (isset($response['data'])) {
                error_log('AuthService::changePassword - Supabase data: ' . json_encode($response['data']));
            }
            
            if ($response['success']) {
                error_log('AuthService::changePassword - Password changed successfully');
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            }
            
            error_log('AuthService::changePassword - Failed to change password');
            return [
                'success' => false,
                'message' => 'Failed to change password'
            ];
            
        } catch (Exception $e) {
            error_log('AuthService::changePassword Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Password change failed'
            ];
        }
    }
    
    /**
     * Admin reset user password
     *
     * @param string $email User email
     * @param string $newPassword New password
     * @return array Result
     */
    public function adminResetPassword(string $email, string $newPassword): array
    {
        try {
            // Use Supabase Admin API to update password
            // Note: This requires service role key
            $url = $this->config['auth_url'] . 'admin/users';
            
            // First, get user by email
            $getUserUrl = $url . '?email=' . urlencode($email);
            
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->supabaseServiceKey,
                'apikey: ' . $this->supabaseServiceKey
            ];
            
            $getUserResponse = $this->makeCurlRequest($getUserUrl, 'GET', null, $headers);
            
            if (!$getUserResponse['success'] || empty($getUserResponse['data'])) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            $userId = $getUserResponse['data'][0]['id'] ?? null;
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'User ID not found'
                ];
            }
            
            // Update user password
            $updateUrl = $url . '/' . $userId;
            
            $data = [
                'password' => $newPassword
            ];
            
            $response = $this->makeCurlRequest($updateUrl, 'PATCH', $data, $headers);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'Password reset successfully'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to reset password'
            ];
            
        } catch (Exception $e) {
            error_log('AuthService::adminResetPassword Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Password reset failed'
            ];
        }
    }
}
