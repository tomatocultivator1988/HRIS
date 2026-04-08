<?php

namespace Middleware;

use Core\Request;
use Core\Response;

/**
 * PerformanceMiddleware - Monitors and optimizes request performance
 * 
 * Tracks request execution time, adds performance headers,
 * and provides performance metrics.
 */
class PerformanceMiddleware
{
    private float $startTime;
    private int $startMemory;
    
    /**
     * Handle the request
     *
     * @param Request $request HTTP request
     * @return Response|null Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        // Record start time and memory
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
        
        // Store in request for later use
        $request->setStartTime($this->startTime);
        
        // Continue to next middleware/controller
        return null;
    }
    
    /**
     * Add performance headers to response
     *
     * @param Response $response HTTP response
     * @param Request $request HTTP request
     * @return Response Modified response
     */
    public static function addPerformanceHeaders(Response $response, Request $request): Response
    {
        $startTime = $request->getStartTime();
        
        if ($startTime !== null) {
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
            $memoryUsage = memory_get_usage() - memory_get_peak_usage();
            
            // Add performance headers
            $response->setHeader('X-Execution-Time', round($executionTime, 2) . 'ms');
            $response->setHeader('X-Memory-Usage', round($memoryUsage / 1024 / 1024, 2) . 'MB');
            $response->setHeader('X-Peak-Memory', round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB');
        }
        
        return $response;
    }
    
    /**
     * Log performance metrics
     *
     * @param Request $request HTTP request
     * @param Response $response HTTP response
     */
    public static function logPerformance(Request $request, Response $response): void
    {
        $startTime = $request->getStartTime();
        
        if ($startTime === null) {
            return;
        }
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        $memoryUsage = memory_get_usage();
        
        // Log slow requests (> 1 second)
        if ($executionTime > 1000) {
            $logFile = dirname(__DIR__, 2) . '/logs/slow_requests.log';
            $logDir = dirname($logFile);
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logEntry = sprintf(
                "[%s] Slow request (%dms, %dMB): %s %s\n",
                date('Y-m-d H:i:s'),
                round($executionTime),
                round($memoryUsage / 1024 / 1024),
                $request->getMethod(),
                $request->getUri()
            );
            
            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
}
