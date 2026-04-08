<?php

/**
 * Application Configuration
 * 
 * Core application settings for the MVC framework
 */

// Helper function to get storage path (only declare if not exists)
if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        $basePath = dirname(__DIR__) . '/storage';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

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
    'name' => env('APP_NAME', 'HRIS System'),
    'version' => '2.0.0',
    'environment' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'base_path' => env('APP_BASE_PATH', ''),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    
    // Security settings
    'key' => env('APP_KEY', 'your-secret-key-here'),
    'cipher' => 'AES-256-CBC',
    
    // JWT settings
    'jwt' => [
        'secret' => env('JWT_SECRET', env('APP_KEY', 'your-jwt-secret-here')),
        'ttl' => env('JWT_TTL', 3600), // 1 hour
        'refresh_ttl' => env('JWT_REFRESH_TTL', 86400), // 24 hours
        'algorithm' => 'HS256',
    ],
    
    // Session settings
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'timeout' => env('SESSION_TIMEOUT', 3600), // seconds
        'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
        'encrypt' => env('SESSION_ENCRYPT', false),
        'files' => storage_path('framework/sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => null,
        'lottery' => [2, 100],
        'cookie' => env('SESSION_COOKIE', 'hris_session'),
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE', false),
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    // API settings
    'api' => [
        'rate_limit' => env('API_RATE_LIMIT', 100),
        'timeout' => env('API_TIMEOUT', 30),
        'version' => 'v1',
        'prefix' => 'api',
    ],
    
    // Security settings
    'security' => [
        'csrf_token_expiry' => env('CSRF_TOKEN_EXPIRY', 1800),
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 900), // 15 minutes
    ],
    
    // File upload settings
    'upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 5242880), // 5MB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,doc,docx')),
        'path' => storage_path('uploads'),
    ],
    
    // Logging settings
    'logging' => [
        'level' => env('LOG_LEVEL', 'INFO'),
        'file' => env('LOG_FILE', 'logs/app.log'),
        'max_files' => env('LOG_MAX_FILES', 5),
        'max_size' => env('LOG_MAX_SIZE', 10485760), // 10MB
    ],
    
    // Cache settings
    'cache' => [
        'default' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('CACHE_TTL', 3600),
        'prefix' => env('CACHE_PREFIX', 'hris_'),
        'path' => storage_path('cache'),
    ],
];