<?php

namespace Core;

/**
 * Response Class - Represents an HTTP response
 * 
 * This class encapsulates HTTP response data and provides methods to set
 * headers, status codes, and body content.
 */
class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];
    private bool $sent = false;
    
    /**
     * HTTP status code messages
     */
    private const STATUS_MESSAGES = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable'
    ];
    
    /**
     * Constructor
     *
     * @param string $content Response content
     * @param int $statusCode HTTP status code
     * @param array $headers HTTP headers
     */
    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
    
    /**
     * Set response content
     *
     * @param string $content Response content
     * @return Response This instance for method chaining
     */
    public function setContent(string $content): Response
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Get response content
     *
     * @return string Response content
     */
    public function getContent(): string
    {
        return $this->content;
    }
    
    /**
     * Set HTTP status code
     *
     * @param int $statusCode HTTP status code
     * @return Response This instance for method chaining
     */
    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    /**
     * Get HTTP status code
     *
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Set HTTP header
     *
     * @param string $name Header name
     * @param string $value Header value
     * @return Response This instance for method chaining
     */
    public function setHeader(string $name, string $value): Response
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Get HTTP header
     *
     * @param string $name Header name
     * @param string|null $default Default value if header not found
     * @return string|null Header value
     */
    public function getHeader(string $name, ?string $default = null): ?string
    {
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
     * Remove header
     *
     * @param string $name Header name
     * @return Response This instance for method chaining
     */
    public function removeHeader(string $name): Response
    {
        unset($this->headers[$name]);
        return $this;
    }
    
    /**
     * Set multiple headers
     *
     * @param array $headers Headers array
     * @return Response This instance for method chaining
     */
    public function setHeaders(array $headers): Response
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }
    
    /**
     * Create JSON response
     *
     * @param array $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @param int $flags JSON encoding flags
     * @return Response This instance for method chaining
     */
    public function json(array $data, int $statusCode = 200, int $flags = JSON_UNESCAPED_UNICODE): Response
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data, $flags));
        
        return $this;
    }
    
    /**
     * Create HTML response
     *
     * @param string $html HTML content
     * @param int $statusCode HTTP status code
     * @return Response This instance for method chaining
     */
    public function html(string $html, int $statusCode = 200): Response
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->setContent($html);
        
        return $this;
    }
    
    /**
     * Create plain text response
     *
     * @param string $text Plain text content
     * @param int $statusCode HTTP status code
     * @return Response This instance for method chaining
     */
    public function text(string $text, int $statusCode = 200): Response
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->setContent($text);
        
        return $this;
    }
    
    /**
     * Create redirect response
     *
     * @param string $url Redirect URL
     * @param int $statusCode HTTP status code (301, 302, etc.)
     * @return Response This instance for method chaining
     */
    public function redirect(string $url, int $statusCode = 302): Response
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->setContent('');
        
        return $this;
    }
    
    /**
     * Create file download response
     *
     * @param string $filePath Path to file
     * @param string|null $filename Download filename (optional)
     * @param array $headers Additional headers
     * @return Response This instance for method chaining
     */
    public function download(string $filePath, ?string $filename = null, array $headers = []): Response
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }
        
        $filename = $filename ?: basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        
        $this->setStatusCode(200);
        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string) filesize($filePath));
        $this->setHeaders($headers);
        $this->setContent(file_get_contents($filePath));
        
        return $this;
    }
    
    /**
     * Set cache headers
     *
     * @param int $maxAge Cache max age in seconds
     * @param bool $public Whether cache is public
     * @return Response This instance for method chaining
     */
    public function cache(int $maxAge, bool $public = true): Response
    {
        $cacheControl = $public ? 'public' : 'private';
        $cacheControl .= ", max-age={$maxAge}";
        
        $this->setHeader('Cache-Control', $cacheControl);
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
        
        return $this;
    }
    
    /**
     * Disable caching
     *
     * @return Response This instance for method chaining
     */
    public function noCache(): Response
    {
        $this->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Expires', '0');
        
        return $this;
    }
    
    /**
     * Set ETag header for conditional requests
     *
     * @param string|null $etag ETag value (auto-generated from content if null)
     * @return Response This instance for method chaining
     */
    public function etag(?string $etag = null): Response
    {
        if ($etag === null) {
            $etag = md5($this->content);
        }
        
        $this->setHeader('ETag', '"' . $etag . '"');
        
        return $this;
    }
    
    /**
     * Set Last-Modified header
     *
     * @param int|string $timestamp Unix timestamp or date string
     * @return Response This instance for method chaining
     */
    public function lastModified($timestamp): Response
    {
        if (is_string($timestamp)) {
            $timestamp = strtotime($timestamp);
        }
        
        $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $timestamp) . ' GMT');
        
        return $this;
    }
    
    /**
     * Check if response is not modified based on request headers
     *
     * @param Request $request HTTP request
     * @return bool True if not modified
     */
    public function isNotModified(Request $request): bool
    {
        // Check If-None-Match (ETag)
        $ifNoneMatch = $request->getHeader('if-none-match');
        if ($ifNoneMatch && $this->getHeader('ETag')) {
            if ($ifNoneMatch === $this->getHeader('ETag')) {
                return true;
            }
        }
        
        // Check If-Modified-Since
        $ifModifiedSince = $request->getHeader('if-modified-since');
        if ($ifModifiedSince && $this->getHeader('Last-Modified')) {
            $modifiedTime = strtotime($this->getHeader('Last-Modified'));
            $requestTime = strtotime($ifModifiedSince);
            
            if ($modifiedTime <= $requestTime) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Send 304 Not Modified response
     *
     * @return Response This instance for method chaining
     */
    public function notModified(): Response
    {
        $this->setStatusCode(304);
        $this->setContent('');
        
        return $this;
    }
    
    /**
     * Set CORS headers
     *
     * @param string $origin Allowed origin (* for all)
     * @param array $methods Allowed methods
     * @param array $headers Allowed headers
     * @param int $maxAge Preflight cache max age
     * @return Response This instance for method chaining
     */
    public function cors(string $origin = '*', array $methods = ['GET', 'POST', 'PUT', 'DELETE'], array $headers = ['Content-Type', 'Authorization'], int $maxAge = 86400): Response
    {
        $this->setHeader('Access-Control-Allow-Origin', $origin);
        $this->setHeader('Access-Control-Allow-Methods', implode(', ', $methods));
        $this->setHeader('Access-Control-Allow-Headers', implode(', ', $headers));
        $this->setHeader('Access-Control-Max-Age', (string) $maxAge);
        
        return $this;
    }
    
    /**
     * Send the response
     *
     * @return void
     */
    public function send(): void
    {
        if ($this->sent) {
            return;
        }
        
        // Send status code
        http_response_code($this->statusCode);
        
        // Send headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Send content
        echo $this->content;
        
        $this->sent = true;
    }
    
    /**
     * Check if response has been sent
     *
     * @return bool True if sent
     */
    public function isSent(): bool
    {
        return $this->sent;
    }
    
    /**
     * Get status message for status code
     *
     * @param int|null $statusCode Status code (uses current if null)
     * @return string Status message
     */
    public function getStatusMessage(?int $statusCode = null): string
    {
        $code = $statusCode ?? $this->statusCode;
        return self::STATUS_MESSAGES[$code] ?? 'Unknown Status';
    }
    
    /**
     * Create error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     * @return Response Error response
     */
    public static function error(string $message, int $statusCode = 500, array $errors = []): Response
    {
        $data = [
            'success' => false,
            'message' => $message,
            'error' => true
        ];
        
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        
        return (new self())->json($data, $statusCode);
    }
    
    /**
     * Create success response
     *
     * @param array $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return Response Success response
     */
    public static function success(array $data = [], string $message = 'Success', int $statusCode = 200): Response
    {
        return (new self())->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Convert response to string
     *
     * @return string Response content
     */
    public function __toString(): string
    {
        return $this->content;
    }
}