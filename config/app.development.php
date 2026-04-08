<?php

/**
 * Development Environment Configuration Overrides
 * 
 * These settings override the base app.php configuration in development
 */

return [
    'debug' => true,
    'environment' => 'development',
    
    // More verbose logging in development
    'logging' => [
        'level' => 'DEBUG',
        'file' => 'logs/development.log',
    ],
    
    // Relaxed security for development
    'security' => [
        'csrf_token_expiry' => 3600, // 1 hour for easier development
        'max_login_attempts' => 10,
        'lockout_duration' => 300, // 5 minutes
    ],
    
    // Longer session timeout for development
    'session' => [
        'lifetime' => 480, // 8 hours
        'timeout' => 28800, // 8 hours
    ],
    
    // Cache disabled in development for fresh data
    'cache' => [
        'ttl' => 60, // 1 minute for quick testing
    ],
];