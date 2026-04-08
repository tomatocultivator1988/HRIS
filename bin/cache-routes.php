#!/usr/bin/env php
<?php

/**
 * Cache Routes Command
 * 
 * This command caches all application routes for production use.
 * Run this after deploying to production or after route changes.
 * 
 * Usage: php bin/cache-routes.php
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Container;
use Core\Router;
use Core\RouteCache;

echo "Caching application routes...\n";

try {
    // Get router instance
    $container = Container::getInstance();
    $router = $container->resolve(Router::class);
    
    // Load routes from config
    $routesConfig = require __DIR__ . '/../config/routes.php';
    
    // Register all routes
    foreach ($routesConfig as $route) {
        $method = $route[0];
        $pattern = $route[1];
        $handler = $route[2];
        $middleware = $route[3] ?? [];
        
        $router->addRoute($method, $pattern, $handler, $middleware);
    }
    
    // Cache the routes
    $routeCache = RouteCache::getInstance();
    
    if ($routeCache->isEnabled()) {
        if ($router->cacheRoutes()) {
            echo "✓ Routes cached successfully!\n";
            echo "  Cache file: " . $routeCache->getCacheFile() . "\n";
            echo "  Total routes: " . count($router->getRoutes()) . "\n";
        } else {
            echo "✗ Failed to cache routes\n";
            exit(1);
        }
    } else {
        echo "⚠ Route caching is disabled (not in production environment)\n";
        echo "  Set APP_ENV=production to enable route caching\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
