-- Fix system_audit_log table - add missing columns
-- These columns are used to store additional context and request information for audit log entries

DO $$ 
BEGIN
    -- Check if user_id column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'user_id'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN user_id UUID;
        
        COMMENT ON COLUMN system_audit_log.user_id IS 'ID of the user who performed the action';
    END IF;
    
    -- Check if user_role column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'user_role'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN user_role VARCHAR(50);
        
        COMMENT ON COLUMN system_audit_log.user_role IS 'Role of the user (admin, employee, etc.)';
    END IF;
    
    -- Check if action column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'action'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN action VARCHAR(100);
        
        COMMENT ON COLUMN system_audit_log.action IS 'Action performed by the user';
    END IF;
    
    -- Check if context column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'context'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN context TEXT;
        
        COMMENT ON COLUMN system_audit_log.context IS 'Additional context data for the audit log entry (JSON format)';
    END IF;
    
    -- Check if request_method column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'request_method'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN request_method VARCHAR(10);
        
        COMMENT ON COLUMN system_audit_log.request_method IS 'HTTP request method (GET, POST, PUT, DELETE, etc.)';
    END IF;
    
    -- Check if request_uri column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'request_uri'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN request_uri TEXT;
        
        COMMENT ON COLUMN system_audit_log.request_uri IS 'The URI of the request that triggered this audit log entry';
    END IF;
    
    -- Check if ip_address column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'ip_address'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN ip_address VARCHAR(45);
        
        COMMENT ON COLUMN system_audit_log.ip_address IS 'IP address of the user who performed the action';
    END IF;
    
    -- Check if user_agent column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'user_agent'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN user_agent TEXT;
        
        COMMENT ON COLUMN system_audit_log.user_agent IS 'User agent string of the browser/client';
    END IF;
    
    -- Check if timestamp column exists, if not add it
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'system_audit_log' 
        AND column_name = 'timestamp'
    ) THEN
        ALTER TABLE system_audit_log 
        ADD COLUMN timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        
        COMMENT ON COLUMN system_audit_log.timestamp IS 'Timestamp when the audit log entry was created';
    END IF;
END $$;
