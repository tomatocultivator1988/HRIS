<?php

namespace Controllers;

use Core\Controller;
use Core\Container;
use Core\Request;
use Core\Response;
use Services\AuthService;
use Exception;

/**
 * AuthController - Handles authentication-related requests
 * 
 * This controller manages login, logout, and authentication verification
 * for both web and API requests.
 */
class AuthController extends Controller
{
    private AuthService $authService;
    
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container->resolve(AuthService::class);
    }
    
    /**
     * Show login form (web route)
     *
     * @param Request $request
     * @return Response
     */
    public function loginForm(Request $request): Response
    {
        return $this->view('auth/login');
    }
    
    /**
     * Handle login API request
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        try {
            $input = $request->getJsonData();

            if (empty($input)) {
                $input = $request->getPostData();
            }
            
            $email = $this->sanitizeInput($input['email'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                return $this->error('Email and password are required', 400);
            }
            
            // Authenticate user
            $authResult = $this->authService->authenticate($email, $password);
            
            if (!$authResult['success']) {
                // Log failed login attempt
                $this->logActivity('FAILED_LOGIN', [
                    'email' => $email,
                    'ip_address' => $request->getClientIp(),
                    'user_agent' => $request->getUserAgent()
                ]);
                
                return $this->error($authResult['message'], 401);
            }
            
            // Log successful login
            $this->logActivity('SUCCESSFUL_LOGIN', [
                'user_id' => $authResult['user']['id'],
                'email' => $email,
                'role' => $authResult['user']['role']
            ]);
            
            // Check if employee needs to change password
            if ($authResult['user']['role'] === 'employee') {
                $userModel = $this->container->resolve(\Models\User::class);
                $employee = $userModel->findByEmail($email);
                
                if ($employee && ($employee['force_password_change'] ?? false)) {
                    $authResult['user']['force_password_change'] = true;
                    $authResult['user']['password_changed_at'] = $employee['password_changed_at'] ?? null;
                }
            }
            
            // Return success response
            return $this->success($authResult, 'Login successful');
            
        } catch (Exception $e) {
            error_log('AuthController::login Error: ' . $e->getMessage());
            return $this->error('Authentication failed', 500);
        }
    }
    
    /**
     * Handle logout API request
     *
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        try {
            // Get authorization header
            $authHeader = $request->getHeader('Authorization');
            
            if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $this->error('Authorization token required', 401);
            }
            
            $token = $matches[1];
            
            // Verify token and get user info for logging
            $verifyResult = $this->authService->validateToken($token);
            
            if ($verifyResult['success']) {
                $user = $verifyResult['user'];
                
                // Log logout activity
                $this->authService->logActivity($user['id'], $user['role'], 'LOGOUT');
                
                $this->logActivity('LOGOUT', [
                    'user_id' => $user['id'],
                    'role' => $user['role']
                ]);
            }
            
            // Note: Supabase handles token invalidation on the server side
            // We don't need to explicitly invalidate the token here
            
            return $this->success([], 'Logged out successfully');
            
        } catch (Exception $e) {
            error_log('AuthController::logout Error: ' . $e->getMessage());
            return $this->error('Logout failed', 500);
        }
    }
    
    /**
     * Verify authentication token
     *
     * @param Request $request
     * @return Response
     */
    public function verify(Request $request): Response
    {
        try {
            // Get authorization header
            $authHeader = $request->getHeader('Authorization');
            
            if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $this->error('Authorization token required', 401);
            }
            
            $token = $matches[1];
            
            // Validate token
            $verifyResult = $this->authService->validateToken($token);
            
            if (!$verifyResult['success']) {
                return $this->error($verifyResult['message'], 401);
            }
            
            // Return user information
            return $this->success($verifyResult, 'Token verified successfully');
            
        } catch (Exception $e) {
            error_log('AuthController::verify Error: ' . $e->getMessage());
            return $this->error('Token verification failed', 500);
        }
    }
    
    /**
     * Refresh authentication token
     *
     * @param Request $request
     * @return Response
     */
    public function refresh(Request $request): Response
    {
        try {
            // Get JSON input
            $input = $request->getJsonData();
            
            if (!$input || empty($input['refresh_token'])) {
                return $this->error('Refresh token required', 400);
            }
            
            $refreshToken = $input['refresh_token'];
            
            // Refresh token with Supabase
            $refreshResult = $this->refreshTokenWithSupabase($refreshToken);
            
            if (!$refreshResult['success']) {
                return $this->error('Token refresh failed', 401);
            }
            
            // Verify the new token and get user info
            $verifyResult = $this->authService->validateToken($refreshResult['access_token']);
            
            if (!$verifyResult['success']) {
                return $this->error('Token verification failed after refresh', 401);
            }
            
            $responseData = [
                'access_token' => $refreshResult['access_token'],
                'refresh_token' => $refreshResult['refresh_token'] ?? $refreshToken,
                'user' => $verifyResult['user']
            ];
            
            return $this->success($responseData, 'Token refreshed successfully');
            
        } catch (Exception $e) {
            error_log('AuthController::refresh Error: ' . $e->getMessage());
            return $this->error('Token refresh failed', 500);
        }
    }
    
    /**
     * Refresh token with Supabase Auth API
     *
     * @param string $refreshToken Refresh token
     * @return array Refresh result
     */
    private function refreshTokenWithSupabase(string $refreshToken): array
    {
        $config = require dirname(__DIR__, 2) . '/config/supabase.php';
        $url = $config['auth_url'] . 'token?grant_type=refresh_token';
        
        $data = [
            'refresh_token' => $refreshToken
        ];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $config['anon_key']
        ];
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $config['timeout'] ?? 30,
            CURLOPT_SSL_VERIFYPEER => $config['ssl_verify'] ?? true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300 && isset($decodedResponse['access_token'])) {
            return [
                'success' => true,
                'access_token' => $decodedResponse['access_token'],
                'refresh_token' => $decodedResponse['refresh_token'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Token refresh failed'
        ];
    }
    
    /**
     * Sanitize input data
     *
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    protected function sanitizeInput(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
}
