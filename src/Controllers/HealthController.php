<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\SimpleCache;
use Core\PerformanceMonitor;

/**
 * HealthController - System health monitoring
 * 
 * Provides health check endpoints for monitoring services.
 * Use with UptimeRobot, Pingdom, or any monitoring service.
 * 
 * ZERO COST - Just status checks!
 */
class HealthController extends Controller
{
    /**
     * Basic health check
     * GET /health
     */
    public function check(Request $request): Response
    {
        $checks = [
            'status' => 'healthy',
            'timestamp' => date('c'),
            'checks' => []
        ];
        
        // Check database connectivity
        try {
            $db = $this->container->resolve(\Core\SupabaseConnection::class);
            $result = $db->select('employees', [], ['limit' => 1]);
            $checks['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $checks['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            $checks['status'] = 'unhealthy';
        }
        
        // Check disk space
        $freeSpace = @disk_free_space('/');
        $totalSpace = @disk_total_space('/');
        
        if ($freeSpace && $totalSpace) {
            $usagePercent = (1 - ($freeSpace / $totalSpace)) * 100;
            $checks['checks']['disk'] = [
                'status' => $usagePercent < 90 ? 'ok' : 'warning',
                'usage_percent' => round($usagePercent, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2)
            ];
            
            if ($usagePercent >= 95) {
                $checks['status'] = 'unhealthy';
            }
        }
        
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        
        if ($memoryLimitBytes > 0) {
            $memoryPercent = ($memoryUsage / $memoryLimitBytes) * 100;
            $checks['checks']['memory'] = [
                'status' => $memoryPercent < 90 ? 'ok' : 'warning',
                'usage_percent' => round($memoryPercent, 2),
                'usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'limit' => $memoryLimit
            ];
        }
        
        // Return appropriate status code
        $statusCode = $checks['status'] === 'healthy' ? 200 : 503;
        
        return $this->json($checks, $statusCode);
    }
    
    /**
     * Detailed health check with performance stats
     * GET /health/detailed
     */
    public function detailed(Request $request): Response
    {
        // Run basic checks first
        $basicHealth = json_decode($this->check($request)->getContent(), true);
        
        // Add cache statistics
        $basicHealth['cache'] = SimpleCache::getStats();
        
        // Add performance statistics
        $basicHealth['performance'] = PerformanceMonitor::getStats();
        
        // Add PHP info
        $basicHealth['php'] = [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ];
        
        // Add server info
        $basicHealth['server'] = [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'unknown',
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null
        ];
        
        $statusCode = $basicHealth['status'] === 'healthy' ? 200 : 503;
        
        return $this->json($basicHealth, $statusCode);
    }
    
    /**
     * Convert PHP memory limit to bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int) $value;
        
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}
