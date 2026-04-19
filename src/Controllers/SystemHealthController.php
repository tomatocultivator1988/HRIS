<?php

namespace Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\MemoryRateLimiter;
use Core\PerformanceMonitor;

/**
 * System Health Dashboard Controller
 * 
 * Provides a visual dashboard for monitoring system health,
 * errors, performance, and rate limiting.
 * 
 * Access: Developers/System Administrators only
 * URL: /system/health-dashboard
 */
class SystemHealthController extends Controller
{
    /**
     * Display system health dashboard
     * GET /system/health-dashboard
     */
    public function dashboard(Request $request): Response
    {
        try {
            // Get system metrics
            $metrics = $this->getSystemMetrics();
            
            // Render dashboard view
            ob_start();
            include __DIR__ . '/../Views/system/health-dashboard.php';
            $html = ob_get_clean();
            
            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return $this->error('Failed to load system health dashboard: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get system metrics (API endpoint for dashboard refresh)
     * GET /api/system/metrics
     */
    public function metrics(Request $request): Response
    {
        try {
            $metrics = $this->getSystemMetrics();
            return $this->json($metrics);
            
        } catch (\Exception $e) {
            return $this->error('Failed to get system metrics: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get all system metrics
     */
    private function getSystemMetrics(): array
    {
        return [
            'system' => $this->getSystemInfo(),
            'errors' => $this->getRecentErrors(),
            'performance' => $this->getPerformanceMetrics(),
            'rate_limiting' => $this->getRateLimitingStats(),
            'health' => $this->getHealthStatus()
        ];
    }
    
    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsedPercent = (1 - ($diskFree / $diskTotal)) * 100;
        
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'disk_free' => $this->formatBytes($diskFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used_percent' => round($diskUsedPercent, 2),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get recent errors from log file
     */
    private function getRecentErrors(): array
    {
        $logFile = dirname(__DIR__, 2) . '/logs/app.log';
        
        if (!file_exists($logFile)) {
            return [
                'total' => 0,
                'critical' => 0,
                'errors' => 0,
                'warnings' => 0,
                'recent' => []
            ];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -200); // Last 200 lines
        
        $critical = 0;
        $errors = 0;
        $warnings = 0;
        $recent = [];
        
        foreach (array_reverse($lines) as $line) {
            if (stripos($line, 'CRITICAL') !== false) {
                $critical++;
                if (count($recent) < 10) {
                    $recent[] = ['level' => 'critical', 'message' => $line];
                }
            } elseif (stripos($line, 'ERROR') !== false) {
                $errors++;
                if (count($recent) < 10) {
                    $recent[] = ['level' => 'error', 'message' => $line];
                }
            } elseif (stripos($line, 'WARNING') !== false) {
                $warnings++;
                if (count($recent) < 10) {
                    $recent[] = ['level' => 'warning', 'message' => $line];
                }
            }
        }
        
        return [
            'total' => $critical + $errors + $warnings,
            'critical' => $critical,
            'errors' => $errors,
            'warnings' => $warnings,
            'recent' => array_slice($recent, 0, 10)
        ];
    }
    
    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $logFile = dirname(__DIR__, 2) . '/logs/app.log';
        
        if (!file_exists($logFile)) {
            return [
                'slow_requests' => 0,
                'avg_response_time' => 0,
                'recent_slow' => []
            ];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -200); // Last 200 lines
        
        $slowRequests = 0;
        $recentSlow = [];
        
        foreach (array_reverse($lines) as $line) {
            if (stripos($line, 'SLOW') !== false || stripos($line, 'took') !== false) {
                $slowRequests++;
                if (count($recentSlow) < 5) {
                    $recentSlow[] = $line;
                }
            }
        }
        
        return [
            'slow_requests' => $slowRequests,
            'threshold' => '1000ms',
            'recent_slow' => $recentSlow
        ];
    }
    
    /**
     * Get rate limiting statistics
     */
    private function getRateLimitingStats(): array
    {
        try {
            $clientCount = MemoryRateLimiter::getClientCount();
            $config = MemoryRateLimiter::getConfig();
            
            // Try to read file-based rate limit data as backup
            $rateLimitFile = dirname(__DIR__, 2) . '/logs/rate_limit.json';
            $blockedIps = [];
            
            if (file_exists($rateLimitFile)) {
                $data = json_decode(file_get_contents($rateLimitFile), true);
                if (is_array($data)) {
                    $now = time();
                    foreach ($data as $ip => $clientData) {
                        if (isset($clientData['blocked_until']) && $clientData['blocked_until'] > $now) {
                            $blockedIps[] = [
                                'ip' => $ip,
                                'blocked_until' => date('Y-m-d H:i:s', $clientData['blocked_until'])
                            ];
                        }
                    }
                }
            }
            
            return [
                'active_clients' => $clientCount,
                'blocked_ips' => count($blockedIps),
                'blocked_list' => array_slice($blockedIps, 0, 10),
                'requests_per_minute' => $config['requests_per_window'] ?? 100,
                'burst_limit' => $config['burst_limit'] ?? 200
            ];
        } catch (\Exception $e) {
            return [
                'active_clients' => 0,
                'blocked_ips' => 0,
                'blocked_list' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get health check status
     */
    private function getHealthStatus(): array
    {
        $status = 'healthy';
        $checks = [];
        
        // Check database
        try {
            $db = $this->container->resolve(\Core\SupabaseConnection::class);
            $result = $db->select('employees', [], ['limit' => 1]);
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
            $status = 'unhealthy';
        }
        
        // Check disk space
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $usagePercent = (1 - ($diskFree / $diskTotal)) * 100;
        
        $checks['disk'] = [
            'status' => $usagePercent < 90 ? 'ok' : 'warning',
            'usage_percent' => round($usagePercent, 2)
        ];
        
        if ($usagePercent >= 90) {
            $status = 'warning';
        }
        
        // Check logs directory
        $logsDir = dirname(__DIR__, 2) . '/logs';
        $checks['logs'] = is_writable($logsDir) ? 'ok' : 'error';
        
        if (!is_writable($logsDir)) {
            $status = 'unhealthy';
        }
        
        return [
            'status' => $status,
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Format bytes to human-readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
