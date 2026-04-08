<?php
/**
 * Test Redirect After Login
 * This script simulates what happens after login
 */

// Set up environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/HRIS/dashboard/admin';
$_SERVER['SCRIPT_NAME'] = '/HRIS/public/index.php';

// Include bootstrap
require_once __DIR__ . '/src/bootstrap.php';

echo "=== Testing Dashboard Redirect ===\n\n";

// Create request
$request = new Core\Request();

echo "Request Details:\n";
echo "  Method: " . $request->getMethod() . "\n";
echo "  URI: " . $request->getUri() . "\n";
echo "  REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n\n";

// Get router from container
$router = $container->resolve(Core\Router::class);

echo "=== Attempting to Route ===\n";

try {
    // Match the route
    $route = $router->match($request->getMethod(), $request->getUri());
    
    if ($route === null) {
        echo "❌ ROUTE NOT FOUND!\n";
        echo "No matching route for: " . $request->getMethod() . " " . $request->getUri() . "\n";
    } else {
        echo "✅ ROUTE MATCHED!\n";
        echo "Controller: " . $route->getController() . "\n";
        echo "Action: " . $route->getAction() . "\n";
        echo "Middleware: " . implode(', ', $route->getMiddleware()) . "\n\n";
        
        // Try to dispatch (will fail due to auth, but that's expected)
        echo "=== Attempting to Dispatch ===\n";
        try {
            $response = $router->dispatch($route, $request);
            
            echo "Response Status: " . $response->getStatusCode() . "\n";
            echo "Content Type: " . $response->getHeader('Content-Type') . "\n\n";
            
            $content = $response->getContent();
            
            if ($response->getStatusCode() === 401) {
                echo "⚠️  AUTHENTICATION REQUIRED (Expected)\n";
            } else if ($response->getStatusCode() === 200) {
                echo "✅ DISPATCH SUCCESSFUL!\n";
                echo "Response length: " . strlen($content) . " bytes\n";
            }
        } catch (Exception $e) {
            echo "⚠️  Dispatch error (expected due to auth): " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Test Complete ===\n";
