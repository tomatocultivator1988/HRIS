<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;

/**
 * Logging Middleware
 * 
 * Logs HTTP requests and responses for debugging and audit purposes
 */
class LoggingMiddleware
{
    private Container $container;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
    }
    
    /**
     * Handle the request
     *
     * @param Request $request
     * @return Response|null Return Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        $startTime = microtime(true);
        
        // Log request
        $this->logRequest($request);
        
        // Store start time for response logging
        $request->setStartTime($startTime);
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Log the incoming request
     *
     * @param Request $request
     */
    private function logRequest(Request $request): void
    {
        try {
            $logger = $this->container->resolve('Logger');
            
            $logData = [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $request->getHeader('user-agent', 'unknown'),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Add authentication info if available
            $authHeader = $request->getHeader('authorization');
            if ($authHeader) {
                $logData['authenticated'] = true;
            }
            
            $logger->info('HTTP Request', $logData);
            
        } catch (Exception $e) {
            // Fail silently - don't break the request if logging fails
            error_log("Logging middleware error: " . $e->getMessage());
        }
    }
    
    /**
     * Log the response (called after controller execution)
     *
     * @param Request $request
     * @param Response $response
     */
    public function logResponse(Request $request, Response $response): void
    {
        try {
            $logger = $this->container->resolve('Logger');
            
            $endTime = microtime(true);
            $startTime = $request->getStartTime() ?? $endTime;
            $duration = round(($endTime - $startTime) * 1000, 2); // milliseconds
            
            $logData = [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Log level based on status code
            if ($response->getStatusCode() >= 500) {
                $logger->error('HTTP Response', $logData);
            } elseif ($response->getStatusCode() >= 400) {
                $logger->warning('HTTP Response', $logData);
            } else {
                $logger->info('HTTP Response', $logData);
            }
            
        } catch (Exception $e) {
            // Fail silently - don't break the request if logging fails
            error_log("Response logging error: " . $e->getMessage());
        }
    }
}