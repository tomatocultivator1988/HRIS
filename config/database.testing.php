<?php

/**
 * Testing Database Configuration Overrides
 */

return [
    // Use in-memory SQLite for testing
    'default' => 'testing',
    
    // Disable query logging during tests for performance
    'query' => [
        'slow_query_threshold' => 10000, // 10 seconds
        'log_queries' => false,
        'explain_queries' => false,
    ],
    
    // Minimal connection pool for testing
    'pool' => [
        'max_connections' => 2,
        'min_connections' => 1,
        'connection_timeout' => 5,
        'idle_timeout' => 30,
    ],
];