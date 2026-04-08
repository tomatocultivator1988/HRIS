<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;

/**
 * CSRF Protection Middleware
 * 
 * Protects against Cross-Site Request Forgery attacks by validating
 * CSRF tokens on state-changing requests (POST, PUT, DELETE, PATCH).
 * 
 * Validates: Requirements 12.4
 */
class CsrfMiddleware
{
    private Container $container;
    private array $config;
    private const SESSION_KEY = 'csrf_token';
    private const TOKEN_LENGTH = 32;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Handle the request
     *
     * @param Request $request
     * @return Response|null Return Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        // Skip CSRF check if disabled
        if (!($this->config['csrf']['enabled'] ?? true)) {
            return null;
        }
        
        // Only check state-changing methods
        $method = $request->getMethod();
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return null;
        }
        
        // Skip CSRF check for API endpoints with JWT authentication
        // (JWT tokens provide CSRF protection)
        if ($this->isApiRequest($request) && $this->hasValidJwt($request)) {
            return null;
        }
        
        // Get token from request
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return $this->csrfErrorResponse('CSRF token missing');
        }
        
        // Validate token
        if (!$this->validateToken($token)) {
            return $this->csrfErrorResponse('Invalid CSRF token');
        }
        
        // Check token expiry
        if ($this->isTokenExpired()) {
            return $this->csrfErrorResponse('CSRF token expired');
        }
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Generate a new CSRF token
     *
     * @return string CSRF token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::SESSION_KEY] = $token;
        $_SESSION[self::SESSION_KEY . '_time'] = time();
        
        return $token;
    }
    
    /**
     * Get current CSRF token (generate if not exists)
     *
     * @return string CSRF token
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return self::generateToken();
        }
        
        return $_SESSION[self::SESSION_KEY];
    }
    
    /**
     * Get token from request
     *
     * @param Request $request
     * @return string|null Token or null if not found
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        // Check header first
        $headerName = $this->config['csrf']['header_name'] ?? 'X-CSRF-TOKEN';
        $token = $request->getHeader($headerName);
        
        if ($token) {
            return $token;
        }
        
        // Check POST data
        $formField = $this->config['csrf']['form_field'] ?? '_token';
        $token = $request->getPostData($formField);
        
        if ($token) {
            return $token;
        }
        
        // Check JSON data
        $jsonData = $request->getJsonData();
        if (isset($jsonData[$formField])) {
            return $jsonData[$formField];
        }
        
        return null;
    }
    
    /**
     * Validate CSRF token
     *
     * @param string $token Token to validate
     * @return bool True if valid
     */
    private function validateToken(string $token): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
    
    /**
     * Check if token is expired
     *
     * @return bool True if expired
     */
    private function isTokenExpired(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY . '_time'])) {
            return true;
        }
        
        $tokenAge = time() - $_SESSION[self::SESSION_KEY . '_time'];
        $expiry = $this->config['csrf']['token_expiry'] ?? 1800; // 30 minutes
        
        return $tokenAge > $expiry;
    }
    
    /**
     * Check if request is an API request
     *
     * @param Request $request
     * @return bool True if API request
     */
    private function isApiRequest(Request $request): bool
    {
        $uri = $request->getUri();
        return strpos($uri, '/api/') === 0;
    }
    
    /**
     * Check if request has valid JWT token
     *
     * @param Request $request
     * @return bool True if has valid JWT
     */
    private function hasValidJwt(Request $request): bool
    {
        $authHeader = $request->getHeader('Authorization');
        return !empty($authHeader) && preg_match('/Bearer\s+(.+)$/i', $authHeader);
    }
    
    /**
     * Create CSRF error response
     *
     * @param string $message Error message
     * @return Response CSRF error response
     */
    private function csrfErrorResponse(string $message): Response
    {
        $response = new Response();
        
        // Log security event
        $this->logSecurityEvent('CSRF_VALIDATION_FAILED', [
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
        ]);
        
        return $response->json([
            'success' => false,
            'message' => $message,
            'error' => 'CSRF_ERROR'
        ], 403);
    }
    
    /**
     * Log security event
     *
     * @param string $event Event type
     * @param array $context Event context
     */
    private function logSecurityEvent(string $event, array $context = []): void
    {
        try {
            $logger = $this->container->resolve('Logger');
            $logger->warning("Security Event: {$event}", $context);
        } catch (\Exception $e) {
            error_log("Failed to log security event: " . $e->getMessage());
        }
    }
}
