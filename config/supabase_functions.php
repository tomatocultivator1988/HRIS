<?php

/**
 * Supabase Configuration
 * 
 * Configuration settings for Supabase integration
 */

return [
    'url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co'),
    'anon_key' => env('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzUzNzYyMjEsImV4cCI6MjA5MDk1MjIyMX0.f9xE60kT4-K5kJLF374ykw9UvqgWtEI4nwxSmASuEt4'),
    'service_key' => env('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTM3NjIyMSwiZXhwIjoyMDkwOTUyMjIxfQ.EQnmstpF-wEKSMBEKBcwvCwtorbKNUQ6L86Alw_TP2I'),
    
    // API Configuration
    'api_url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co') . '/rest/v1/',
    'auth_url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co') . '/auth/v1/',
    
    // Session Configuration
    'session_timeout' => env('SESSION_TIMEOUT', 3600), // 1 hour in seconds
    'jwt_expiry' => env('JWT_EXPIRY', 3600), // JWT token expiry time
    
    // Security Configuration
    'api_rate_limit' => env('API_RATE_LIMIT', 100), // requests per minute per IP
    'csrf_token_expiry' => env('CSRF_TOKEN_EXPIRY', 1800), // 30 minutes
    
    // Database Table Names
    'tables' => [
        'employees' => env('TABLE_EMPLOYEES', 'employees'),
        'admins' => env('TABLE_ADMINS', 'admins'),
        'attendance' => env('TABLE_ATTENDANCE', 'attendance'),
        'leave_types' => env('TABLE_LEAVE_TYPES', 'leave_types'),
        'leave_requests' => env('TABLE_LEAVE_REQUESTS', 'leave_requests'),
        'leave_credits' => env('TABLE_LEAVE_CREDITS', 'leave_credits'),
        'payroll_periods' => env('TABLE_PAYROLL_PERIODS', 'payroll_periods'),
        'employee_compensation' => env('TABLE_EMPLOYEE_COMPENSATION', 'employee_compensation'),
        'payroll_runs' => env('TABLE_PAYROLL_RUNS', 'payroll_runs'),
        'payroll_line_items' => env('TABLE_PAYROLL_LINE_ITEMS', 'payroll_line_items'),
        'payroll_adjustments' => env('TABLE_PAYROLL_ADJUSTMENTS', 'payroll_adjustments'),
        'announcements' => env('TABLE_ANNOUNCEMENTS', 'announcements'),
        'work_calendar' => env('TABLE_WORK_CALENDAR', 'work_calendar'),
        'user_sessions' => env('TABLE_USER_SESSIONS', 'user_sessions'),
        'audit_log' => env('TABLE_AUDIT_LOG', 'system_audit_log'),
    ],
    
    // Connection settings
    'timeout' => env('SUPABASE_TIMEOUT', 30),
    'ssl_verify' => env('SUPABASE_SSL_VERIFY', true),
    'retry_attempts' => env('SUPABASE_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SUPABASE_RETRY_DELAY', 1000), // milliseconds
];
