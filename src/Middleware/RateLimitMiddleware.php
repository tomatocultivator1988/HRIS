<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;

/**
 * Rate Limiting Middleware
 * 
 * Prevents abuse by limiting the number of requests from a single
 * IP address or user within a time window.
 * 
 * Validates: Requirements 12.6 (prevents abuse for security)
 */
class RateLimitMiddleware
{
    private Container $container;
    private array $config;
    private string $storageFile;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
        $this->storageFile = dirname(__DIR__, 2) . '/logs/rate_limit.json';
        
        // Ensure logs directory exists
        $logsDir = dirname($this->storageFile);
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
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
        // Skip if rate limiting is disabled
        if (!($this->config['rate_limit']['enabled'] ?? true)) {
            return null;
        }
        
        $clientIp = $request->getClientIp();
        
        // Check if IP is whitelisted
        $whitelist = $this->config['rate_limit']['whitelist'] ?? ['127.0.0.1', '::1'];
        if (in_array($clientIp, $whitelist)) {
            return null;
        }
        
        // Get rate limit configuration
        $requestsPerMinute = $this->config['rate_limit']['requests_per_minute'] ?? 100;
        $burstLimit = $this->config['rate_limit']['burst_limit'] ?? 200;
        
        // Check rate limit
        $result = $this->checkRateLimit($clientIp, $requestsPerMinute, $burstLimit);
        
        if (!$result['allowed']) {
            return $this->rateLimitErrorResponse($result);
        }
        
        // Add rate limit headers to response (will be added after controller execution)
        $request->setRateLimitInfo($result);
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Check if request is within rate limit
     *
     * @param string $clientIp Client IP address
     * @param int $requestsPerMinute Requests allowed per minute
     * @param int $burstLimit Maximum burst requests
     * @return array Rate limit result
     */
    private function checkRateLimit(string $clientIp, int $requestsPerMinute, int $burstLimit): array
    {
        $now = time();
        $windowSize = 60; // 1 minute window
        
        // Load rate limit data
        $data = $this->loadRateLimitData();
        
        // Initialize client data if not exists
        if (!isset($data[$clientIp])) {
            $data[$clientIp] = [
                'requests' => [],
                'blocked_until' => 0
            ];
        }
        
        $clientData = &$data[$clientIp];
        
        // Check if client is currently blocked
        if ($clientData['blocked_until'] > $now) {
            $retryAfter = $clientData['blocked_until'] - $now;
            return [
                'allowed' => false,
                'reason' => 'rate_limit_exceeded',
                'retry_after' => $retryAfter,
                'limit' => $requestsPerMinute,
                'remaining' => 0
            ];
        }
        
        // Remove old requests outside the window
        $clientData['requests'] = array_filter(
            $clientData['requests'],
            fn($timestamp) => $timestamp > ($now - $windowSize)
        );
        
        // Count requests in current window
        $requestCount = count($clientData['requests']);
        
        // Check burst limit
        if ($requestCount >= $burstLimit) {
            // Block for 5 minutes
            $clientData['blocked_until'] = $now + 300;
            $this->saveRateLimitData($data);
            
            // Log security event
            $this->logSecurityEvent('RATE_LIMIT_BURST_EXCEEDED', [
                'ip' => $clientIp,
                'requests' => $requestCount,
                'burst_limit' => $burstLimit
            ]);
            
            return [
                'allowed' => false,
                'reason' => 'burst_limit_exceeded',
                'retry_after' => 300,
                'limit' => $requestsPerMinute,
                'remaining' => 0
            ];
        }
        
        // Check normal rate limit
        if ($requestCount >= $requestsPerMinute) {
            // Calculate retry after (time until oldest request expires)
            $oldestRequest = min($clientData['requests']);
            $retryAfter = ($oldestRequest + $windowSize) - $now;
            
            // Log security event
            $this->logSecurityEvent('RATE_LIMIT_EXCEEDED', [
                'ip' => $clientIp,
                'requests' => $requestCount,
                'limit' => $requestsPerMinute
            ]);
            
            return [
                'allowed' => false,
                'reason' => 'rate_limit_exceeded',
                'retry_after' => max(1, $retryAfter),
                'limit' => $requestsPerMinute,
                'remaining' => 0
            ];
        }
        
        // Add current request
        $clientData['requests'][] = $now;
        $this->saveRateLimitData($data);
        
        return [
            'allowed' => true,
            'limit' => $requestsPerMinute,
            'remaining' => $requestsPerMinute - count($clientData['requests']),
            'reset' => $now + $windowSize
        ];
    }
    
    /**
     * Load rate limit data from storage
     *
     * @return array Rate limit data
     */
    private function loadRateLimitData(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        
        $content = file_get_contents($this->storageFile);
        if ($content === false) {
            return [];
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }
    
    /**
     * Save rate limit data to storage
     *
     * @param array $data Rate limit data
     */
    private function saveRateLimitData(array $data): void
    {
        // Clean up old data (remove entries older than 1 hour)
        $now = time();
        $cutoff = $now - 3600;
        
        foreach ($data as $ip => $clientData) {
            // Remove old requests
            $data[$ip]['requests'] = array_filter(
                $clientData['requests'],
                fn($timestamp) => $timestamp > $cutoff
            );
            
            // Remove client if no recent requests and not blocked
            if (empty($data[$ip]['requests']) && $clientData['blocked_until'] < $now) {
                unset($data[$ip]);
            }
        }
        
        file_put_contents($this->storageFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create rate limit error response
     *
     * @param array $result Rate limit result
     * @return Response Rate limit error response
     */
    private function rateLimitErrorResponse(array $result): Response
    {
        $response = new Response();
        
        $response->setHeader('X-RateLimit-Limit', (string)$result['limit']);
        $response->setHeader('X-RateLimit-Remaining', '0');
        $response->setHeader('Retry-After', (string)$result['retry_after']);
        
        return $response->json([
            'success' => false,
            'message' => 'Rate limit exceeded. Please try again later.',
            'error' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $result['retry_after']
        ], 429);
    }
    
    /**
     * Apply rate limit headers to response
     *
     * @param Response $response Response to add headers to
     * @param array $rateLimitInfo Rate limit information
     * @return Response Response with rate limit headers
     */
    public static function applyHeaders(Response $response, array $rateLimitInfo): Response
    {
        if (isset($rateLimitInfo['limit'])) {
            $response->setHeader('X-RateLimit-Limit', (string)$rateLimitInfo['limit']);
        }
        
        if (isset($rateLimitInfo['remaining'])) {
            $response->setHeader('X-RateLimit-Remaining', (string)$rateLimitInfo['remaining']);
        }
        
        if (isset($rateLimitInfo['reset'])) {
            $response->setHeader('X-RateLimit-Reset', (string)$rateLimitInfo['reset']);
        }
        
        return $response;
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
