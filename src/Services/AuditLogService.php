<?php

namespace Services;

use Core\Container;
use Models\User;

/**
 * Audit Log Service
 * 
 * Provides centralized audit logging for security-sensitive operations
 * across the application.
 * 
 * Validates: Requirements 12.6
 */
class AuditLogService
{
    private Container $container;
    private array $config;
    private $db;
    
    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? Container::getInstance();
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
        
        try {
            $this->db = $this->container->resolve('DatabaseConnection');
        } catch (\Exception $e) {
            // Database connection might not be available in all contexts
            $this->db = null;
        }
    }
    
    /**
     * Log a security-sensitive operation
     *
     * @param string $action Action performed
     * @param array $context Additional context data
     * @param string|null $userId User ID (null for anonymous)
     * @param string|null $userRole User role
     * @return bool True if logged successfully
     */
    public function log(string $action, array $context = [], ?string $userId = null, ?string $userRole = null): bool
    {
        // Check if audit logging is enabled
        if (!($this->config['audit']['enabled'] ?? true)) {
            return false;
        }
        
        // Check if this type of action should be logged
        if (!$this->shouldLogAction($action)) {
            return false;
        }
        
        try {
            $logEntry = [
                'user_id' => $userId,
                'user_role' => $userRole,
                'action' => $action,
                'context' => json_encode($context),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Insert into audit log table (if database is available)
            if ($this->db) {
                $result = $this->db->insert('system_audit_log', $logEntry);
            } else {
                $result = false;
            }
            
            // Also log to file for redundancy
            $this->logToFile($action, $logEntry);
            
            return $result !== false;
            
        } catch (\Exception $e) {
            error_log("AuditLogService::log Error: " . $e->getMessage());
            
            // Fallback to file logging if database fails
            $this->logToFile($action, [
                'user_id' => $userId,
                'action' => $action,
                'context' => $context,
                'error' => 'Database logging failed: ' . $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Log successful login
     *
     * @param string $userId User ID
     * @param string $userRole User role
     * @param array $context Additional context
     * @return bool True if logged successfully
     */
    public function logLogin(string $userId, string $userRole, array $context = []): bool
    {
        if (!($this->config['audit']['log_successful_logins'] ?? true)) {
            return false;
        }
        
        return $this->log('LOGIN_SUCCESS', $context, $userId, $userRole);
    }
    
    /**
     * Log failed login attempt
     *
     * @param string $email Email attempted
     * @param array $context Additional context
     * @return bool True if logged successfully
     */
    public function logFailedLogin(string $email, array $context = []): bool
    {
        if (!($this->config['audit']['log_failed_logins'] ?? true)) {
            return false;
        }
        
        $context['email'] = $email;
        return $this->log('LOGIN_FAILED', $context);
    }
    
    /**
     * Log logout
     *
     * @param string $userId User ID
     * @param string $userRole User role
     * @param array $context Additional context
     * @return bool True if logged successfully
     */
    public function logLogout(string $userId, string $userRole, array $context = []): bool
    {
        return $this->log('LOGOUT', $context, $userId, $userRole);
    }
    
    /**
     * Log data change operation
     *
     * @param string $entity Entity type (e.g., 'employee', 'leave_request')
     * @param string $operation Operation type (e.g., 'create', 'update', 'delete')
     * @param string $entityId Entity ID
     * @param array $changes Changes made
     * @param string|null $userId User ID
     * @param string|null $userRole User role
     * @return bool True if logged successfully
     */
    public function logDataChange(
        string $entity,
        string $operation,
        string $entityId,
        array $changes,
        ?string $userId = null,
        ?string $userRole = null
    ): bool {
        if (!($this->config['audit']['log_data_changes'] ?? true)) {
            return false;
        }
        
        $action = strtoupper("{$entity}_{$operation}");
        $context = [
            'entity' => $entity,
            'operation' => $operation,
            'entity_id' => $entityId,
            'changes' => $changes
        ];
        
        return $this->log($action, $context, $userId, $userRole);
    }
    
    /**
     * Log admin action
     *
     * @param string $action Action performed
     * @param array $context Additional context
     * @param string $userId User ID
     * @return bool True if logged successfully
     */
    public function logAdminAction(string $action, array $context, string $userId): bool
    {
        if (!($this->config['audit']['log_admin_actions'] ?? true)) {
            return false;
        }
        
        $context['admin_action'] = true;
        return $this->log($action, $context, $userId, 'admin');
    }
    
    /**
     * Log security event
     *
     * @param string $event Event type
     * @param array $context Event context
     * @param string|null $userId User ID
     * @return bool True if logged successfully
     */
    public function logSecurityEvent(string $event, array $context = [], ?string $userId = null): bool
    {
        $context['security_event'] = true;
        return $this->log($event, $context, $userId);
    }
    
    /**
     * Get audit logs with filtering
     *
     * @param array $filters Filter criteria
     * @return array Audit logs
     */
    public function getAuditLogs(array $filters = []): array
    {
        try {
            $query = "SELECT * FROM system_audit_log WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['user_id'])) {
                $query .= " AND user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['action'])) {
                $query .= " AND action = ?";
                $params[] = $filters['action'];
            }
            
            if (!empty($filters['start_date'])) {
                $query .= " AND timestamp >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $query .= " AND timestamp <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['ip_address'])) {
                $query .= " AND ip_address = ?";
                $params[] = $filters['ip_address'];
            }
            
            // Order by timestamp descending
            $query .= " ORDER BY timestamp DESC";
            
            // Apply limit
            $limit = $filters['limit'] ?? 100;
            $query .= " LIMIT ?";
            $params[] = $limit;
            
            $result = $this->db->query($query, $params);
            
            return $result ?: [];
            
        } catch (\Exception $e) {
            error_log("AuditLogService::getAuditLogs Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean up old audit logs based on retention policy
     *
     * @return int Number of logs deleted
     */
    public function cleanupOldLogs(): int
    {
        try {
            $retentionDays = $this->config['audit']['retention_days'] ?? 365;
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
            
            $query = "DELETE FROM system_audit_log WHERE timestamp < ?";
            $result = $this->db->execute($query, [$cutoffDate]);
            
            return $result ? $this->db->affectedRows() : 0;
            
        } catch (\Exception $e) {
            error_log("AuditLogService::cleanupOldLogs Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if action should be logged
     *
     * @param string $action Action to check
     * @return bool True if should be logged
     */
    private function shouldLogAction(string $action): bool
    {
        // Always log security events
        $securityActions = [
            'LOGIN_SUCCESS', 'LOGIN_FAILED', 'LOGOUT',
            'UNAUTHORIZED_ACCESS', 'AUTHORIZATION_FAILED',
            'CSRF_VALIDATION_FAILED', 'INPUT_VALIDATION_FAILED',
            'RATE_LIMIT_EXCEEDED', 'RATE_LIMIT_BURST_EXCEEDED'
        ];
        
        if (in_array($action, $securityActions)) {
            return true;
        }
        
        // Check configuration for other actions
        if (str_contains($action, 'ADMIN_') && !($this->config['audit']['log_admin_actions'] ?? true)) {
            return false;
        }
        
        if (str_contains($action, 'DATA_') && !($this->config['audit']['log_data_changes'] ?? true)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log to file as backup
     *
     * @param string $action Action performed
     * @param array $data Log data
     */
    private function logToFile(string $action, array $data): void
    {
        try {
            $logDir = dirname(__DIR__, 2) . '/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/audit.log';
            $timestamp = date('Y-m-d H:i:s');
            $logLine = "[{$timestamp}] {$action}: " . json_encode($data) . PHP_EOL;
            
            file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
            
        } catch (\Exception $e) {
            error_log("Failed to write to audit log file: " . $e->getMessage());
        }
    }
}
