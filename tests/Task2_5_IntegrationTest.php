<?php
/**
 * Complete integration test for Task 2.5
 * Tests routing system with authentication middleware integration
 */

// Define application root
define('APP_ROOT', '.');

// Load framework bootstrap
require_once 'src/bootstrap.php';

use Core\Router;
use Core\Request;
use Core\Container;

function testSection($title) {
    echo "\n" . str_repeat("=", 50) . "\n";
    echo $title . "\n";
    echo str_repeat("=", 50) . "\n";
}

function testResult($test, $success, $details = '') {
    $status = $success ? "✓" : "✗";
    echo "  $status $test";
    if ($details) {
        echo " - $details";
    }
    echo "\n";
    return $success;
}

try {
    echo "TASK 2.5 INTEGRATION TEST\n";
    echo "Testing routing system with authentication middleware\n";
    
    $container = Container::getInstance();
    $totalTests = 0;
    $passedTests = 0;
    
    // Test 1: Framework Bootstrap
    testSection("1. Framework Bootstrap");
    
    $totalTests++;
    $success = $container instanceof Container;
    $passedTests += testResult("Container initialization", $success) ? 1 : 0;
    
    $totalTests++;
    $router = $container->resolve(Router::class);
    $success = $router instanceof Router;
    $passedTests += testResult("Router resolution", $success) ? 1 : 0;
    
    // Test 2: Route Configuration Loading
    testSection("2. Route Configuration");
    
    $totalTests++;
    $routeLoader = require 'config/routes.php';
    $success = is_callable($routeLoader);
    $passedTests += testResult("Route loader callable", $success) ? 1 : 0;
    
    $totalTests++;
    try {
        $routeLoader($router);
        $routes = $router->getRoutes();
        $success = count($routes) > 0;
        $passedTests += testResult("Routes loaded", $success, count($routes) . " routes") ? 1 : 0;
    } catch (Exception $e) {
        $passedTests += testResult("Routes loaded", false, $e->getMessage()) ? 1 : 0;
    }
    $totalTests++;
    
    // Test 3: Authentication Routes
    testSection("3. Authentication Routes");
    
    $authRoutes = [
        ['POST', '/api/auth/login', 'AuthController@login'],
        ['POST', '/api/auth/logout', 'AuthController@logout'],
        ['GET', '/api/auth/verify', 'AuthController@verify'],
        ['POST', '/api/auth/refresh', 'AuthController@refresh']
    ];
    
    foreach ($authRoutes as [$method, $uri, $expectedHandler]) {
        $totalTests++;
        $route = $router->match($method, $uri);
        $success = $route && $route->getHandler() === $expectedHandler;
        $passedTests += testResult("$method $uri", $success, $success ? $route->getHandler() : "not found") ? 1 : 0;
    }
    
    // Test 4: Protected Routes with Middleware
    testSection("4. Protected Routes with Middleware");
    
    $protectedRoutes = [
        ['GET', '/api/employees', ['logging', 'auth']],
        ['GET', '/dashboard/admin', ['logging', 'auth', 'role:admin']],
        ['POST', '/api/employees', ['logging', 'auth', 'role:admin']],
        ['GET', '/api/dashboard/metrics', ['logging', 'auth']]
    ];
    
    foreach ($protectedRoutes as [$method, $uri, $expectedMiddleware]) {
        $totalTests++;
        $route = $router->match($method, $uri);
        if ($route) {
            $middleware = $route->getMiddleware();
            $success = $middleware === $expectedMiddleware;
            $details = $success ? implode(', ', $middleware) : "middleware mismatch";
        } else {
            $success = false;
            $details = "route not found";
        }
        $passedTests += testResult("$method $uri middleware", $success, $details) ? 1 : 0;
    }
    
    // Test 5: Backward Compatibility Routes
    testSection("5. Backward Compatibility Routes");
    
    $legacyRoutes = [
        'POST /api/auth/login.php',
        'POST /api/auth/logout.php',
        'GET /api/auth/verify.php',
        'POST /api/auth/refresh.php',
        'GET /api/employees/list.php',
        'POST /api/employees/create.php',
        'GET /api/employees/search.php',
        'GET /api/dashboard/metrics.php'
    ];
    
    foreach ($legacyRoutes as $routeSpec) {
        [$method, $uri] = explode(' ', $routeSpec, 2);
        $totalTests++;
        $route = $router->match($method, $uri);
        $success = $route !== null;
        $details = $success ? $route->getHandler() : "not found";
        $passedTests += testResult("$method $uri", $success, $details) ? 1 : 0;
    }
    
    // Test 6: Middleware Classes
    testSection("6. Middleware Classes");
    
    $totalTests++;
    try {
        $authMiddleware = new \Middleware\AuthMiddleware();
        $success = $authMiddleware instanceof \Middleware\AuthMiddleware;
        $passedTests += testResult("AuthMiddleware instantiation", $success) ? 1 : 0;
    } catch (Exception $e) {
        $passedTests += testResult("AuthMiddleware instantiation", false, $e->getMessage()) ? 1 : 0;
    }
    
    $totalTests++;
    try {
        $roleMiddleware = \Middleware\RoleMiddleware::role('admin');
        $success = $roleMiddleware instanceof \Middleware\RoleMiddleware;
        $passedTests += testResult("RoleMiddleware instantiation", $success) ? 1 : 0;
    } catch (Exception $e) {
        $passedTests += testResult("RoleMiddleware instantiation", false, $e->getMessage()) ? 1 : 0;
    }
    
    // Test 7: Request/Response Classes
    testSection("7. Request/Response Enhancement");
    
    $totalTests++;
    $request = new \Core\Request();
    $request->setUser(['id' => 1, 'role' => 'admin']);
    $user = $request->getUser();
    $success = $user && $user['role'] === 'admin';
    $passedTests += testResult("Request user context", $success) ? 1 : 0;
    
    $totalTests++;
    $response = new \Core\Response();
    $response->html('<h1>Test</h1>');
    $success = $response->getHeaders()['Content-Type'] === 'text/html; charset=utf-8';
    $passedTests += testResult("Response HTML method", $success) ? 1 : 0;
    
    // Final Results
    testSection("FINAL RESULTS");
    
    $percentage = round(($passedTests / $totalTests) * 100, 1);
    echo "Tests Passed: $passedTests / $totalTests ($percentage%)\n";
    
    if ($passedTests === $totalTests) {
        echo "\n🎉 ALL TESTS PASSED! Task 2.5 integration is successful.\n";
        echo "\nKey achievements:\n";
        echo "  ✓ Routing system updated with middleware support\n";
        echo "  ✓ Authentication middleware integrated\n";
        echo "  ✓ Role-based access control implemented\n";
        echo "  ✓ Backward compatibility maintained\n";
        echo "  ✓ Request/Response classes enhanced\n";
    } else {
        echo "\n⚠️  Some tests failed. Please review the issues above.\n";
    }
    
} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}