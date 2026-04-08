#!/usr/bin/env php
<?php

/**
 * Clear Cache Command
 * 
 * This command clears all application caches.
 * 
 * Usage: php bin/clear-cache.php [type]
 * 
 * Types:
 *   all     - Clear all caches (default)
 *   routes  - Clear route cache only
 *   query   - Clear query cache only
 *   config  - Clear config cache only
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Cache;
use Core\RouteCache;
use Core\QueryOptimizer;
use Config\ConfigManager;

$type = $argv[1] ?? 'all';

echo "Clearing cache...\n";

try {
    $cleared = [];
    
    if ($type === 'all' || $type === 'routes') {
        $routeCache = RouteCache::getInstance();
        if ($routeCache->clear()) {
            $cleared[] = 'routes';
            echo "✓ Route cache cleared\n";
        }
    }
    
    if ($type === 'all' || $type === 'query') {
        $cache = Cache::getInstance();
        if ($cache->clear()) {
            $cleared[] = 'query';
            echo "✓ Query cache cleared\n";
        }
    }
    
    if ($type === 'all' || $type === 'config') {
        $configManager = ConfigManager::getInstance();
        $configManager->clearCache();
        $cleared[] = 'config';
        echo "✓ Config cache cleared\n";
    }
    
    if (empty($cleared)) {
        echo "⚠ No caches cleared. Valid types: all, routes, query, config\n";
        exit(1);
    }
    
    echo "\n✓ Cache cleared successfully!\n";
    echo "  Cleared: " . implode(', ', $cleared) . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
