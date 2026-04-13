<?php

/**
 * Security Configuration
 * 
 * Security-related settings for the MVC framework
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
    // Authentication settings
    'auth' => [
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'password_require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'password_require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'password_require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'password_require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', false),
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 900), // 15 minutes
        'session_timeout' => env('SESSION_TIMEOUT', 3600), // 1 hour
    ],
    
    // CSRF protection
    'csrf' => [
        'enabled' => env('CSRF_ENABLED', true),
        'token_expiry' => env('CSRF_TOKEN_EXPIRY', 1800), // 30 minutes
        'regenerate_on_login' => env('CSRF_REGENERATE_ON_LOGIN', true),
        'header_name' => 'X-CSRF-TOKEN',
        'form_field' => '_token',
    ],
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        'requests_per_minute' => env('API_RATE_LIMIT', 100),
        'burst_limit' => env('RATE_LIMIT_BURST', 200),
        'whitelist' => explode(',', env('RATE_LIMIT_WHITELIST', '127.0.0.1,::1')),
    ],
    
    // Input validation and sanitization
    'input' => [
        'max_input_length' => env('MAX_INPUT_LENGTH', 10000),
        'allowed_html_tags' => explode(',', env('ALLOWED_HTML_TAGS', 'p,br,strong,em,ul,ol,li')),
        'strip_dangerous_protocols' => env('STRIP_DANGEROUS_PROTOCOLS', true),
        'validate_utf8' => env('VALIDATE_UTF8', true),
    ],
    
    // File upload security
    'upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 5242880), // 5MB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'allowed_extensions' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx')),
        'scan_for_viruses' => env('SCAN_UPLOADS_FOR_VIRUSES', false),
        'quarantine_suspicious' => env('QUARANTINE_SUSPICIOUS_FILES', true),
    ],
    
    // SQL injection protection
    'database' => [
        'use_prepared_statements' => true,
        'escape_identifiers' => true,
        'validate_table_names' => true,
        'allowed_functions' => ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX', 'NOW', 'CURDATE'],
    ],
    
    // XSS protection (UPDATED FOR BETTER CSP)
    'xss' => [
        'auto_escape_output' => env('AUTO_ESCAPE_OUTPUT', true),
        'content_security_policy' => env('CSP_ENABLED', true),
        'csp_directives' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net",
            'style-src' => "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com",
            'font-src' => "'self' https://fonts.gstatic.com",
            'img-src' => "'self' data: https:",
            'connect-src' => "'self' https://cdn.jsdelivr.net https://cdn.tailwindcss.com",
            'frame-ancestors' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self'",
            'upgrade-insecure-requests' => '',
        ],
    ],
    
    // Security headers (UPDATED FOR BETTER SECURITY SCORE)
    'headers' => [
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
        'strict_transport_security' => env('HSTS_ENABLED', false) ? 'max-age=31536000; includeSubDomains; preload' : null,
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('PERMISSIONS_POLICY', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()'),
    ],
    
    // Encryption settings
    'encryption' => [
        'cipher' => env('ENCRYPTION_CIPHER', 'AES-256-CBC'),
        'key' => env('ENCRYPTION_KEY', env('APP_KEY')),
        'rotate_keys' => env('ROTATE_ENCRYPTION_KEYS', false),
        'key_rotation_interval' => env('KEY_ROTATION_INTERVAL', 2592000), // 30 days
    ],
    
    // Audit logging
    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_successful_logins' => env('LOG_SUCCESSFUL_LOGINS', true),
        'log_failed_logins' => env('LOG_FAILED_LOGINS', true),
        'log_data_changes' => env('LOG_DATA_CHANGES', true),
        'log_admin_actions' => env('LOG_ADMIN_ACTIONS', true),
        'retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 365),
    ],
    
    // IP filtering
    'ip_filtering' => [
        'enabled' => env('IP_FILTERING_ENABLED', false),
        'whitelist' => explode(',', env('IP_WHITELIST', '')),
        'blacklist' => explode(',', env('IP_BLACKLIST', '')),
        'block_tor_exits' => env('BLOCK_TOR_EXITS', false),
        'block_proxies' => env('BLOCK_PROXIES', false),
    ],
    
    // API security
    'api' => [
        'require_https' => env('API_REQUIRE_HTTPS', true),
        'cors_enabled' => env('CORS_ENABLED', false),
        'cors_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
        'cors_methods' => explode(',', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,OPTIONS')),
        'cors_headers' => explode(',', env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With')),
        'api_key_required' => env('API_KEY_REQUIRED', false),
        'jwt_required' => env('JWT_REQUIRED', true),
    ],
];