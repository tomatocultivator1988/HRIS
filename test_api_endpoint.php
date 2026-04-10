<?php
/**
 * Test API endpoint directly
 */

// Simulate the request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/HRIS/api/recruitment/jobs';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer test-token'; // You'll need a real token

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application root
define('APP_ROOT', __DIR__);

// Load framework bootstrap
require_once __DIR__ . '/src/bootstrap.php';

use Core\Router;
use Core\Request;
use Core\Container;

try {
    $container = Container::getInstance();
    $router = $container->resolve(Router::class);
    $request = $container->resolve(Request::class);
    
    // Load routes
    $routeLoader = require __DIR__ . '/config/routes.php';
    $routeLoader($router);
    
    echo "Testing route: GET /api/recruitment/jobs\n\n";
    
    // Try to match the route
    $route = $router->match('GET', '/api/recruitment/jobs');
    
    if ($route === null) {
        echo "❌ Route NOT FOUND\n\n";
        
        // Debug: Show all registered routes
        echo "All registered routes:\n";
        $allRoutes = $router->getRoutes();
        foreach ($allRoutes as $r) {
            if (strpos($r['pattern'], 'recruitment') !== false) {
                echo "  {$r['method']} {$r['pattern']} -> {$r['handler']}\n";
            }
        }
    } else {
        echo "✓ Route FOUND\n";
        echo "Handler: {$route['handler']}\n";
        echo "Middleware: " . implode(', ', $route['middleware']) . "\n\n";
        
        // Try to dispatch
        echo "Attempting to dispatch...\n";
        try {
            $response = $router->dispatch($route, $request);
            echo "Response Status: " . $response->getStatusCode() . "\n";
            echo "Response Body: " . substr($response->getContent(), 0, 200) . "...\n";
        } catch (Exception $e) {
            echo "❌ Dispatch Error: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
