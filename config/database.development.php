<?php

/**
 * Development Database Configuration Overrides
 */

return [
    // Enable query logging in development
    'query' => [
        'slow_query_threshold' => 100, // 100ms
        'log_queries' => true,
        'explain_queries' => true,
    ],
    
    // Smaller connection pool for development
    'pool' => [
        'max_connections' => 5,
        'min_connections' => 1,
        'connection_timeout' => 10,
        'idle_timeout' => 60,
    ],
];