<?php

/**
 * Supabase Configuration and Helper Functions
 * 
 * Configuration settings and helper functions for Supabase integration
 */

// Helper function to get environment variable with type conversion
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        // Convert string boolean values
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }
        }

        return $value;
    }
}

// Load configuration
$config = [
    'url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co'),
    'anon_key' => env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzUzNzYyMjEsImV4cCI6MjA5MDk1MjIyMX0.f9xE60kT4-K5kJLF374ykw9UvqgWtEI4nwxSmASuEt4'),
    'service_key' => env('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTM3NjIyMSwiZXhwIjoyMDkwOTUyMjIxfQ.EQnmstpF-wEKSMBEKBcwvCwtorbKNUQ6L86Alw_TP2I'),
    
    // API Configuration
    'api_url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co') . '/rest/v1/',
    'auth_url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co') . '/auth/v1/',
    
    // Session Configuration
    'session_timeout' => env('SESSION_TIMEOUT', 3600), // 1 hour in seconds
    'jwt_expiry' => env('JWT_EXPIRY', 3600), // JWT token expiry time
    
    // Security Configuration
    'api_rate_limit' => env('API_RATE_LIMIT', 100), // requests per minute per IP
    'csrf_token_expiry' => env('CSRF_TOKEN_EXPIRY', 1800), // 30 minutes
    
    // Database Table Names
    'tables' => [
        'employees' => env('TABLE_EMPLOYEES', 'employees'),
        'admins' => env('TABLE_ADMINS', 'admins'),
        'attendance' => env('TABLE_ATTENDANCE', 'attendance'),
        'leave_types' => env('TABLE_LEAVE_TYPES', 'leave_types'),
        'leave_requests' => env('TABLE_LEAVE_REQUESTS', 'leave_requests'),
        'leave_credits' => env('TABLE_LEAVE_CREDITS', 'leave_credits'),
        'announcements' => env('TABLE_ANNOUNCEMENTS', 'announcements'),
        'work_calendar' => env('TABLE_WORK_CALENDAR', 'work_calendar'),
        'user_sessions' => env('TABLE_USER_SESSIONS', 'user_sessions'),
        'audit_log' => env('TABLE_AUDIT_LOG', 'system_audit_log'),
    ],
    
    // Connection settings
    'timeout' => env('SUPABASE_TIMEOUT', 30),
    'ssl_verify' => env('SUPABASE_SSL_VERIFY', true),
    'retry_attempts' => env('SUPABASE_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SUPABASE_RETRY_DELAY', 1000), // milliseconds
];

// Define constants for backward compatibility with existing auth files
if (!defined('SUPABASE_URL')) define('SUPABASE_URL', $config['url']);
if (!defined('SUPABASE_ANON_KEY')) define('SUPABASE_ANON_KEY', $config['anon_key']);
if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', $config['service_key']);
if (!defined('SUPABASE_API_URL')) define('SUPABASE_API_URL', $config['api_url']);
if (!defined('SUPABASE_AUTH_URL')) define('SUPABASE_AUTH_URL', $config['auth_url']);

// Define table name constants
if (!defined('TABLE_EMPLOYEES')) define('TABLE_EMPLOYEES', $config['tables']['employees']);
if (!defined('TABLE_ADMINS')) define('TABLE_ADMINS', $config['tables']['admins']);
if (!defined('TABLE_ATTENDANCE')) define('TABLE_ATTENDANCE', $config['tables']['attendance']);
if (!defined('TABLE_LEAVE_TYPES')) define('TABLE_LEAVE_TYPES', $config['tables']['leave_types']);
if (!defined('TABLE_LEAVE_REQUESTS')) define('TABLE_LEAVE_REQUESTS', $config['tables']['leave_requests']);
if (!defined('TABLE_LEAVE_CREDITS')) define('TABLE_LEAVE_CREDITS', $config['tables']['leave_credits']);
if (!defined('TABLE_ANNOUNCEMENTS')) define('TABLE_ANNOUNCEMENTS', $config['tables']['announcements']);
if (!defined('TABLE_WORK_CALENDAR')) define('TABLE_WORK_CALENDAR', $config['tables']['work_calendar']);
if (!defined('TABLE_USER_SESSIONS')) define('TABLE_USER_SESSIONS', $config['tables']['user_sessions']);
if (!defined('TABLE_SYSTEM_AUDIT_LOG')) define('TABLE_SYSTEM_AUDIT_LOG', $config['tables']['audit_log']);

/**
 * Helper functions for backward compatibility with existing auth files
 */

if (!function_exists('makeSupabaseRequest')) {
    /**
     * Make request to Supabase REST API
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param bool $useServiceKey Use service key instead of anon key
     * @return array Response
     */
    function makeSupabaseRequest(string $endpoint, string $method = 'GET', ?array $data = null, bool $useServiceKey = false): array
    {
        global $config;
        
        $url = $config['api_url'] . $endpoint;
        $apiKey = $useServiceKey ? $config['service_key'] : $config['anon_key'];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey
        ];
        
        return makeCurlRequest($url, $method, $data, $headers);
    }
}

if (!function_exists('authenticateUser')) {
    /**
     * Authenticate user with Supabase Auth API
     *
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    function authenticateUser(string $email, string $password): array
    {
        global $config;
        
        $url = $config['auth_url'] . 'token?grant_type=password';
        
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $config['anon_key']
        ];
        
        $response = makeCurlRequest($url, 'POST', $data, $headers);
        
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
}

if (!function_exists('verifyJWTToken')) {
    /**
     * Verify JWT token with Supabase
     *
     * @param string $token JWT token
     * @return array Verification result
     */
    function verifyJWTToken(string $token): array
    {
        global $config;
        
        $url = $config['auth_url'] . 'user';
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'apikey: ' . $config['anon_key']
        ];
        
        $response = makeCurlRequest($url, 'GET', null, $headers);
        
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
}

if (!function_exists('logSystemActivity')) {
    /**
     * Log system activity
     *
     * @param int $userId User ID
     * @param string $userRole User role
     * @param string $action Action performed
     * @return void
     */
    function logSystemActivity(int $userId, string $userRole, string $action): void
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
            
            makeSupabaseRequest(TABLE_SYSTEM_AUDIT_LOG, 'POST', $logData, true);
            
        } catch (Exception $e) {
            error_log('logSystemActivity Error: ' . $e->getMessage());
        }
    }
}

if (!function_exists('sanitizeInput')) {
    /**
     * Sanitize input data
     *
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    function sanitizeInput(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
}

if (!function_exists('validateEmail')) {
    /**
     * Validate email format
     *
     * @param string $email Email to validate
     * @return bool True if valid
     */
    function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('makeCurlRequest')) {
    /**
     * Make HTTP request using cURL
     *
     * @param string $url Request URL
     * @param string $method HTTP method
     * @param array|null $data Request data
     * @param array $headers Request headers
     * @return array Response
     */
    function makeCurlRequest(string $url, string $method, ?array $data, array $headers): array
    {
        global $config;
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $config['timeout'] ?? 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $config['ssl_verify'] ?? true
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
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
}

// Return configuration for use in other files
return $config;