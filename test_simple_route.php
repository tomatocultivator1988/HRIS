<?php
/**
 * Simple Route Test
 */

// Set up environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/HRIS/dashboard/admin';
$_SERVER['SCRIPT_NAME'] = '/HRIS/public/index.php';

// Include bootstrap
require_once __DIR__ . '/src/bootstrap.php';

echo "=== Simple Route Test ===\n\n";

// Get router from container
$router = $container->resolve(Core\Router::class);

// Load route definitions
$routeLoader = require __DIR__ . '/config/routes.php';
$routeLoader($router);

// Test cleanUri method
$reflection = new ReflectionClass($router);
$cleanUriMethod = $reflection->getMethod('cleanUri');
$cleanUriMethod->setAccessible(true);

$testUris = [
    '/HRIS/dashboard/admin',
    '/dashboard/admin',
    '/HRIS/login',
    '/login',
    '/HRIS/',
    '/'
];

echo "=== Testing cleanUri ===\n";
foreach ($testUris as $uri) {
    $cleaned = $cleanUriMethod->invoke($router, $uri);
    echo "$uri -> $cleaned\n";
}

echo "\n=== Testing Route Matching ===\n";
$testRoutes = [
    ['GET', '/dashboard/admin'],
    ['GET', '/dashboard/employee'],
    ['GET', '/login'],
    ['GET', '/'],
    ['POST', '/api/auth/login']
];

foreach ($testRoutes as list($method, $uri)) {
    $route = $router->match($method, $uri);
    if ($route) {
        echo "✅ $method $uri -> " . $route->getHandler() . "\n";
    } else {
        echo "❌ $method $uri -> NOT FOUND\n";
    }
}

echo "\n=== Checking Routes Array ===\n";
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "Total routes: " . count($routes) . "\n";

// Find dashboard routes
echo "\nDashboard routes:\n";
foreach ($routes as $route) {
    if (strpos($route['pattern'], 'dashboard') !== false) {
        echo "  " . $route['method'] . " " . $route['pattern'] . " -> " . $route['handler'] . "\n";
        echo "    Regex: " . $route['regex'] . "\n";
    }
}

echo "\n=== Test Complete ===\n";
