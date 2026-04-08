<?php

namespace Core;

/**
 * Request Class - Represents an HTTP request
 * 
 * This class encapsulates HTTP request data and provides methods to access
 * various parts of the request including headers, parameters, and body data.
 */
class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $queryParams;
    private array $postData;
    private array $routeParams = [];
    private ?array $jsonData = null;
    private ?array $user = null;
    private ?array $rateLimitInfo = null;
    private ?float $startTime = null;
    private ?string $body = null;
    
    /**
     * Constructor - Initialize request from global variables
     */
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->headers = $this->parseHeaders();
        $this->queryParams = $_GET ?? [];
        $this->postData = $_POST ?? [];
        $this->parseJsonData();
    }
    
    /**
     * Create request from globals
     *
     * @return Request Request instance
     */
    public static function createFromGlobals(): Request
    {
        return new self();
    }
    
    /**
     * Get HTTP method
     *
     * @return string HTTP method (GET, POST, PUT, DELETE, etc.)
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * Get request URI
     *
     * @return string Request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }
    
    /**
     * Get request header
     *
     * @param string $name Header name
     * @param string|null $default Default value if header not found
     * @return string|null Header value
     */
    public function getHeader(string $name, ?string $default = null): ?string
    {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }
    
    /**
     * Get all headers
     *
     * @return array All headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
    
    /**
     * Check if header exists
     *
     * @param string $name Header name
     * @return bool True if header exists
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }
    
    /**
     * Get query parameter
     *
     * @param string|null $name Parameter name (null to get all)
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value or all parameters
     */
    public function getQueryParameter(?string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->queryParams;
        }
        
        return $this->queryParams[$name] ?? $default;
    }
    
    /**
     * Get POST data
     *
     * @param string|null $name Field name (null to get all)
     * @param mixed $default Default value if field not found
     * @return mixed Field value or all POST data
     */
    public function getPostData(?string $name = null, $default = null)
    {
        if ($name === null) {
            return $this->postData;
        }
        
        return $this->postData[$name] ?? $default;
    }
    
    /**
     * Get JSON data from request body
     *
     * @return array JSON data as associative array
     */
    public function getJsonData(): array
    {
        return $this->jsonData ?? [];
    }
    
    /**
     * Get route parameter
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    public function getRouteParameter(string $name, $default = null)
    {
        return $this->routeParams[$name] ?? $default;
    }
    
    /**
     * Set route parameters (used by router)
     *
     * @param array $params Route parameters
     */
    public function setRouteParameters(array $params): void
    {
        $this->routeParams = $params;
    }
    
    /**
     * Get all route parameters
     *
     * @return array Route parameters
     */
    public function getRouteParameters(): array
    {
        return $this->routeParams;
    }
    
    /**
     * Get authenticated user (set by AuthMiddleware)
     *
     * @return array|null User data or null if not authenticated
     */
    public function getUser(): ?array
    {
        return $this->user;
    }
    
    /**
     * Set authenticated user (used by AuthMiddleware)
     *
     * @param array $user User data
     */
    public function setUser(array $user): void
    {
        $this->user = $user;
    }
    
    /**
     * Check if request is authenticated
     *
     * @return bool True if authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }
    
    /**
     * Check if request is AJAX
     *
     * @return bool True if AJAX request
     */
    public function isAjax(): bool
    {
        return $this->getHeader('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Check if request expects JSON response
     *
     * @return bool True if JSON expected
     */
    public function expectsJson(): bool
    {
        $accept = $this->getHeader('Accept', '');
        return strpos($accept, 'application/json') !== false || $this->isAjax();
    }
    
    /**
     * Get request body content
     *
     * @return string Raw request body
     */
    public function getBody(): string
    {
        if ($this->body === null) {
            $this->body = file_get_contents('php://input') ?: '';
        }

        return $this->body;
    }
    
    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    public function getClientIp(): string
    {
        // Check for various headers that might contain the real IP
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get user agent
     *
     * @return string User agent string
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * Parse request URI
     *
     * @return string Parsed URI
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Decode URL
        $uri = urldecode($uri);
        
        // Remove base path for XAMPP subdirectory installation
        // If app is installed in /HRIS/, strip that prefix
        $basePath = '/HRIS';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        return $uri;
    }
    
    /**
     * Parse HTTP headers
     *
     * @return array Parsed headers (lowercase keys)
     */
    private function parseHeaders(): array
    {
        $headers = [];
        
        // Get headers from $_SERVER
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = $value;
            }
        }
        
        // Add content-type and content-length if present
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }
        
        // Special handling for Authorization header (Apache doesn't pass it by default)
        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $apacheHeaders = apache_request_headers();
                if (isset($apacheHeaders['Authorization'])) {
                    $headers['authorization'] = $apacheHeaders['Authorization'];
                } elseif (isset($apacheHeaders['authorization'])) {
                    $headers['authorization'] = $apacheHeaders['authorization'];
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Set rate limit information (used by RateLimitMiddleware)
     *
     * @param array $info Rate limit information
     */
    public function setRateLimitInfo(array $info): void
    {
        $this->rateLimitInfo = $info;
    }
    
    /**
     * Get rate limit information
     *
     * @return array|null Rate limit information or null if not set
     */
    public function getRateLimitInfo(): ?array
    {
        return $this->rateLimitInfo;
    }
    
    /**
     * Set start time (used by LoggingMiddleware)
     *
     * @param float $startTime Request start time
     */
    public function setStartTime(float $startTime): void
    {
        $this->startTime = $startTime;
    }
    
    /**
     * Get start time
     *
     * @return float|null Start time or null if not set
     */
    public function getStartTime(): ?float
    {
        return $this->startTime;
    }
    
    /**
     * Parse JSON data from request body
     */
    private function parseJsonData(): void
    {
        $contentType = $this->getHeader('content-type', '');
        
        if (strpos($contentType, 'application/json') !== false) {
            $body = $this->getBody();
            
            if (!empty($body)) {
                $decoded = json_decode($body, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->jsonData = $decoded;
                }
            }
        }
    }
}
