<?php

namespace Core;

/**
 * StructuredLogger - JSON-based logging for better analysis
 * 
 * Logs in JSON format for easy parsing and analysis.
 * Each log entry includes context, request ID, and user info.
 * 
 * ZERO COST - Just better log format!
 */
class StructuredLogger
{
    private string $logFile;
    private static ?string $requestId = null;
    
    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/app.json';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Generate request ID if not set
        if (self::$requestId === null) {
            self::$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid('req_', true);
        }
    }
    
    /**
     * Log a message with context
     *
     * @param string $level Log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'timestamp' => date('c'), // ISO 8601 format
            'level' => strtoupper($level),
            'message' => $message,
            'request_id' => self::$requestId,
            'context' => $context
        ];
        
        // Add request info
        if (isset($_SERVER['REQUEST_URI'])) {
            $entry['request'] = [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'uri' => $_SERVER['REQUEST_URI'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
        }
        
        // Add user info if available
        if (isset($_SESSION['user_id'])) {
            $entry['user'] = [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['user_role'] ?? 'unknown'
            ];
        }
        
        // Add memory usage
        $entry['memory_mb'] = round(memory_get_usage(true) / 1024 / 1024, 2);
        
        // Write as single-line JSON
        $jsonLine = json_encode($entry, JSON_UNESCAPED_SLASHES) . "\n";
        file_put_contents($this->logFile, $jsonLine, FILE_APPEND | LOCK_EX);
        
        // Also log to PHP error log for critical errors
        if ($level === 'ERROR' || $level === 'CRITICAL') {
            error_log("[{$level}] {$message}");
        }
    }
    
    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Get current request ID
     */
    public static function getRequestId(): string
    {
        return self::$requestId ?? 'unknown';
    }
    
    /**
     * Set request ID (useful for testing)
     */
    public static function setRequestId(string $requestId): void
    {
        self::$requestId = $requestId;
    }
}
