<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;

/**
 * Input Validation and Sanitization Middleware
 * 
 * Validates and sanitizes all user input to prevent injection attacks
 * and ensure data integrity across all controllers.
 * 
 * Validates: Requirements 12.3, 12.4
 */
class InputValidationMiddleware
{
    private Container $container;
    private array $config;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
    }
    
    /**
     * Handle the request
     *
     * @param Request $request
     * @return Response|null Return Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        // Validate and sanitize query parameters
        $queryParams = $request->getQueryParameter();
        if (!empty($queryParams)) {
            $sanitized = $this->sanitizeArray($queryParams);
            if ($sanitized === false) {
                return $this->validationErrorResponse('Invalid query parameters detected');
            }
        }
        
        // Validate and sanitize POST data
        $postData = $request->getPostData();
        if (!empty($postData)) {
            $sanitized = $this->sanitizeArray($postData);
            if ($sanitized === false) {
                return $this->validationErrorResponse('Invalid POST data detected');
            }
        }
        
        // Validate and sanitize JSON data
        $jsonData = $request->getJsonData();
        if (!empty($jsonData)) {
            $sanitized = $this->sanitizeArray($jsonData);
            if ($sanitized === false) {
                return $this->validationErrorResponse('Invalid JSON data detected');
            }
        }
        
        // Validate content length
        $contentLength = $request->getHeader('content-length', 0);
        $maxLength = $this->config['input']['max_input_length'] ?? 10000;
        
        if ($contentLength > $maxLength) {
            return $this->validationErrorResponse('Request body too large');
        }
        
        // Validate UTF-8 encoding if enabled
        if ($this->config['input']['validate_utf8'] ?? true) {
            $body = $request->getBody();
            if (!empty($body) && !mb_check_encoding($body, 'UTF-8')) {
                return $this->validationErrorResponse('Invalid UTF-8 encoding');
            }
        }
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Sanitize array of data recursively
     *
     * @param array $data Data to sanitize
     * @return array|false Sanitized data or false if validation fails
     */
    private function sanitizeArray(array $data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Sanitize key
            $cleanKey = $this->sanitizeString($key);
            if ($cleanKey === false) {
                return false;
            }
            
            // Sanitize value
            if (is_array($value)) {
                $cleanValue = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $cleanValue = $this->sanitizeString($value);
            } else {
                $cleanValue = $value;
            }
            
            if ($cleanValue === false) {
                return false;
            }
            
            $sanitized[$cleanKey] = $cleanValue;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize string input
     *
     * @param string $input Input to sanitize
     * @return string|false Sanitized string or false if validation fails
     */
    private function sanitizeString(string $input)
    {
        // Check for null bytes (potential injection)
        if (strpos($input, "\0") !== false) {
            return false;
        }
        
        // Strip dangerous protocols if enabled
        if ($this->config['input']['strip_dangerous_protocols'] ?? true) {
            $dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
            foreach ($dangerousProtocols as $protocol) {
                if (stripos($input, $protocol) !== false) {
                    return false;
                }
            }
        }
        
        // Check for SQL injection patterns
        if ($this->containsSqlInjection($input)) {
            return false;
        }
        
        // Check for XSS patterns
        if ($this->containsXssPattern($input)) {
            // For XSS, we sanitize rather than reject
            $input = $this->sanitizeXss($input);
        }
        
        return $input;
    }
    
    /**
     * Check if input contains SQL injection patterns
     *
     * @param string $input Input to check
     * @return bool True if SQL injection detected
     */
    private function containsSqlInjection(string $input): bool
    {
        // Common SQL injection patterns
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b.*\bWHERE\b)/i',
            '/(\bINSERT\b.*\bINTO\b.*\bVALUES\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(;.*(-{2}|\/\*))/i', // SQL comments
            '/(\bOR\b.*=.*)/i', // OR 1=1 patterns
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if input contains XSS patterns
     *
     * @param string $input Input to check
     * @return bool True if XSS pattern detected
     */
    private function containsXssPattern(string $input): bool
    {
        // Common XSS patterns
        $patterns = [
            '/<script[^>]*>.*<\/script>/is',
            '/<iframe[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // Event handlers like onclick=
            '/<embed[^>]*>/i',
            '/<object[^>]*>/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize XSS from input
     *
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    private function sanitizeXss(string $input): string
    {
        // Use htmlspecialchars for basic XSS protection
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Create validation error response
     *
     * @param string $message Error message
     * @return Response Validation error response
     */
    private function validationErrorResponse(string $message): Response
    {
        $response = new Response();
        
        // Log security event
        $this->logSecurityEvent('INPUT_VALIDATION_FAILED', [
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        return $response->json([
            'success' => false,
            'message' => $message,
            'error' => 'VALIDATION_ERROR'
        ], 400);
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
