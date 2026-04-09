<?php

/**
 * Database Configuration
 * 
 * Database connection settings for the MVC framework
 */

// Helper function to get environment variable with type conversion
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        // Convert string boolean values
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }
        }

        return $value;
    }
}

return [
    'default' => env('DB_CONNECTION', 'supabase'),
    
    'connections' => [
        'supabase' => [
            'driver' => 'supabase',
            'url' => env('SUPABASE_URL', 'https://xtfekjcusnnadfgcrzht.supabase.co'),
            'anon_key' => env('SUPABASE_ANON_KEY', ''),
            'service_key' => env('SUPABASE_SERVICE_KEY', ''),
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
        ],
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'hris_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => env('DB_PREFIX', ''),
            'strict' => env('DB_STRICT_MODE', true),
            'engine' => env('DB_ENGINE', null),
            'timezone' => env('DB_TIMEZONE', '+00:00'),
            'options' => [
                // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_SQLITE_PATH', dirname(__DIR__) . '/storage/database.sqlite'),
            'prefix' => env('DB_PREFIX', ''),
        ],
        
        'testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ],
    ],
    
    // Migration settings
    'migrations' => env('DB_MIGRATIONS_TABLE', 'migrations'),
    
    // Connection pool settings
    'pool' => [
        'max_connections' => env('DB_MAX_CONNECTIONS', 10),
        'min_connections' => env('DB_MIN_CONNECTIONS', 1),
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 30),
        'idle_timeout' => env('DB_IDLE_TIMEOUT', 300),
    ],
    
    // Query settings
    'query' => [
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'log_queries' => env('DB_LOG_QUERIES', false),
        'explain_queries' => env('DB_EXPLAIN_QUERIES', false),
    ],
    
    // Redis configuration (for caching and sessions)
    'redis' => [
        'client' => 'predis',
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
            'timeout' => env('REDIS_TIMEOUT', 5),
        ],
        'cache' => [
            'host' => env('REDIS_CACHE_HOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('REDIS_CACHE_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_CACHE_PORT', env('REDIS_PORT', 6379)),
            'database' => env('REDIS_CACHE_DB', 1),
            'timeout' => env('REDIS_TIMEOUT', 5),
        ],
        'session' => [
            'host' => env('REDIS_SESSION_HOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('REDIS_SESSION_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_SESSION_PORT', env('REDIS_PORT', 6379)),
            'database' => env('REDIS_SESSION_DB', 2),
            'timeout' => env('REDIS_TIMEOUT', 5),
        ],
    ],
];
