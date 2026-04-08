-- Migration: Create System Audit Log Table
-- Purpose: Store audit logs for security-sensitive operations
-- Task: 7.4 - Security Enhancements

CREATE TABLE IF NOT EXISTS system_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(255) NULL,
    user_role VARCHAR(50) NULL,
    action VARCHAR(100) NOT NULL,
    context TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    request_uri VARCHAR(500) NULL,
    request_method VARCHAR(10) NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp),
    INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comments for documentation
ALTER TABLE system_audit_log 
    COMMENT = 'Stores audit logs for security-sensitive operations and user actions';

-- Sample queries for audit log analysis

-- Get all actions by a specific user
-- SELECT * FROM system_audit_log WHERE user_id = 'user-id' ORDER BY timestamp DESC;

-- Get all failed login attempts
-- SELECT * FROM system_audit_log WHERE action = 'LOGIN_FAILED' ORDER BY timestamp DESC;

-- Get all admin actions
-- SELECT * FROM system_audit_log WHERE user_role = 'admin' ORDER BY timestamp DESC;

-- Get all actions from a specific IP
-- SELECT * FROM system_audit_log WHERE ip_address = '192.168.1.1' ORDER BY timestamp DESC;

-- Get security events
-- SELECT * FROM system_audit_log WHERE action LIKE '%SECURITY%' OR action LIKE '%FAILED%' ORDER BY timestamp DESC;

-- Clean up old logs (older than 1 year)
-- DELETE FROM system_audit_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 YEAR);
