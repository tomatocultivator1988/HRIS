<?php

/**
 * Testing Environment Configuration Overrides
 * 
 * These settings override the base app.php configuration during testing
 */

return [
    'debug' => true,
    'environment' => 'testing',
    
    // Fast sessions for testing
    'session' => [
        'lifetime' => 60, // 1 hour
        'timeout' => 3600,
        'cookie' => 'hris_test_session',
    ],
    
    // Minimal logging during tests
    'logging' => [
        'level' => 'ERROR',
        'file' => 'logs/testing.log',
    ],
    
    // Relaxed security for testing
    'security' => [
        'csrf_token_expiry' => 3600,
        'max_login_attempts' => 100, // Don't lock out during tests
        'lockout_duration' => 1,
    ],
    
    // No caching during tests
    'cache' => [
        'ttl' => 1,
    ],
    
    // Fast JWT tokens for testing
    'jwt' => [
        'ttl' => 3600,
        'refresh_ttl' => 7200,
    ],
];