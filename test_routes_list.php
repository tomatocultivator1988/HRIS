<?php
/**
 * List All Registered Routes
 */

// Set up environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/HRIS/';
$_SERVER['SCRIPT_NAME'] = '/HRIS/public/index.php';

// Include bootstrap
require_once __DIR__ . '/src/bootstrap.php';

echo "=== Registered Routes ===\n\n";

// Get router from container
$router = $container->resolve(Core\Router::class);

// Load route definitions
$routeLoader = require __DIR__ . '/config/routes.php';
$routeLoader($router);

// Use reflection to get routes
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "Total routes registered: " . count($routes) . "\n\n";

// Group by method
$byMethod = [];
foreach ($routes as $route) {
    $method = $route->getMethod();
    if (!isset($byMethod[$method])) {
        $byMethod[$method] = [];
    }
    $byMethod[$method][] = $route;
}

// Display routes
foreach ($byMethod as $method => $methodRoutes) {
    echo "=== $method Routes ===\n";
    foreach ($methodRoutes as $route) {
        $pattern = $route->getPattern();
        $controller = $route->getController();
        $action = $route->getAction();
        $middleware = implode(', ', $route->getMiddleware());
        
        echo "  $pattern\n";
        echo "    -> $controller@$action\n";
        if ($middleware) {
            echo "    Middleware: $middleware\n";
        }
        echo "\n";
    }
}

echo "\n=== Testing Specific Routes ===\n\n";

$testPaths = [
    '/dashboard/admin',
    '/dashboard/employee',
    '/login',
    '/',
    '/api/auth/login'
];

foreach ($testPaths as $path) {
    $route = $router->match('GET', $path);
    if ($route) {
        echo "✅ $path -> " . $route->getController() . "@" . $route->getAction() . "\n";
    } else {
        echo "❌ $path -> NOT FOUND\n";
    }
}

echo "\n=== Test Complete ===\n";
