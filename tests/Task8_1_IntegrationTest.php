<?php

/**
 * Task 8.1 Integration Test - Wire all components together
 * 
 * Tests that all services, models, and controllers are properly registered
 * in the dependency injection container and that routing is complete.
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Container;
use Core\Router;
use Core\Request;

class Task8_1_IntegrationTest
{
    private Container $container;
    private Router $router;
    private array $results = [];
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->router = $this->container->resolve(Router::class);
        
        // Load routes
        $routeLoader = require __DIR__ . '/../config/routes.php';
        $routeLoader($this->router);
    }
    
    /**
     * Run all tests
     */
    public function runTests(): void
    {
        echo "=== Task 8.1 Integration Test ===\n\n";
        
        $this->testContainerRegistrations();
        $this->testModelRegistrations();
        $this->testServiceRegistrations();
        $this->testControllerRegistrations();
        $this->testRoutingTable();
        $this->testBackwardCompatibilityRoutes();
        $this->testMiddlewareConfiguration();
        
        $this->printResults();
    }
    
    /**
     * Test that core container registrations are present
     */
    private function testContainerRegistrations(): void
    {
        echo "Testing Container Registrations...\n";
        
        $coreBindings = [
            Container::class,
            'Container',
            Router::class,
            Request::class,
            'DatabaseConnection',
            'Logger',
            'Validator',
            'ViewRenderer'
        ];
        
        foreach ($coreBindings as $binding) {
            try {
                $bound = $this->container->bound($binding);
                $this->addResult("Container: {$binding}", $bound, "Should be bound in container");
            } catch (Exception $e) {
                $this->addResult("Container: {$binding}", false, $e->getMessage());
            }
        }
    }
    
    /**
     * Test that all models are registered
     */
    private function testModelRegistrations(): void
    {
        echo "Testing Model Registrations...\n";
        
        $models = [
            'Models\\User',
            'Models\\Employee',
            'Models\\Attendance',
            'Models\\LeaveRequest'
        ];
        
        foreach ($models as $model) {
            try {
                $bound = $this->container->bound($model);
                $this->addResult("Model: {$model}", $bound, "Should be registered as singleton");
                
                // Try to resolve
                if ($bound) {
                    $instance = $this->container->resolve($model);
                    $this->addResult("Model Resolve: {$model}", is_object($instance), "Should resolve to object");
                }
            } catch (Exception $e) {
                $this->addResult("Model: {$model}", false, $e->getMessage());
            }
        }
    }
    
    /**
     * Test that all services are registered
     */
    private function testServiceRegistrations(): void
    {
        echo "Testing Service Registrations...\n";
        
        $services = [
            'Services\\AuthService',
            'Services\\EmployeeService',
            'Services\\AttendanceService',
            'Services\\LeaveService',
            'Services\\ReportService',
            'Services\\AuditLogService'
        ];
        
        foreach ($services as $service) {
            try {
                $bound = $this->container->bound($service);
                $this->addResult("Service: {$service}", $bound, "Should be registered as singleton");
                
                // Try to resolve
                if ($bound) {
                    $instance = $this->container->resolve($service);
                    $this->addResult("Service Resolve: {$service}", is_object($instance), "Should resolve to object");
                }
            } catch (Exception $e) {
                $this->addResult("Service: {$service}", false, $e->getMessage());
            }
        }
    }
    
    /**
     * Test that all controllers are registered
     */
    private function testControllerRegistrations(): void
    {
        echo "Testing Controller Registrations...\n";
        
        $controllers = [
            'Controllers\\AuthController',
            'Controllers\\EmployeeController',
            'Controllers\\AttendanceController',
            'Controllers\\LeaveController',
            'Controllers\\DashboardController',
            'Controllers\\ReportController',
            'Controllers\\AnnouncementController'
        ];
        
        foreach ($controllers as $controller) {
            try {
                $bound = $this->container->bound($controller);
                $this->addResult("Controller: {$controller}", $bound, "Should be registered");
                
                // Try to resolve
                if ($bound) {
                    $instance = $this->container->resolve($controller);
                    $this->addResult("Controller Resolve: {$controller}", is_object($instance), "Should resolve to object");
                }
            } catch (Exception $e) {
                $this->addResult("Controller: {$controller}", false, $e->getMessage());
            }
        }
    }
    
    /**
     * Test that routing table is complete
     */
    private function testRoutingTable(): void
    {
        echo "Testing Routing Table...\n";
        
        $routes = $this->router->getRoutes();
        $routeCount = count($routes);
        
        $this->addResult("Route Count", $routeCount > 0, "Should have routes registered (found {$routeCount})");
        
        // Test key modern API routes
        $keyRoutes = [
            ['POST', '/api/auth/login', 'AuthController@login'],
            ['GET', '/api/employees', 'EmployeeController@apiIndex'],
            ['GET', '/api/dashboard/metrics', 'DashboardController@metrics'],
            ['GET', '/api/attendance/daily', 'AttendanceController@daily'],
            ['GET', '/api/leave/balance', 'LeaveController@balance'],
            ['GET', '/api/announcements', 'AnnouncementController@index'],
            ['GET', '/api/reports/attendance', 'ReportController@attendance']
        ];
        
        foreach ($keyRoutes as [$method, $uri, $handler]) {
            $route = $this->router->match($method, $uri);
            $matched = $route !== null && $route->getHandler() === $handler;
            $this->addResult("Route: {$method} {$uri}", $matched, "Should route to {$handler}");
        }
    }
    
    /**
     * Test backward compatibility routes
     */
    private function testBackwardCompatibilityRoutes(): void
    {
        echo "Testing Backward Compatibility Routes...\n";
        
        $legacyRoutes = [
            ['POST', '/api/auth/login.php', 'AuthController@login'],
            ['GET', '/api/employees/list.php', 'EmployeeController@apiIndex'],
            ['GET', '/api/dashboard/metrics.php', 'DashboardController@metrics'],
            ['GET', '/api/attendance/daily.php', 'AttendanceController@daily'],
            ['GET', '/api/leave/balance.php', 'LeaveController@balance'],
            ['GET', '/api/announcements/list.php', 'AnnouncementController@list'],
            ['GET', '/api/reports/attendance.php', 'ReportController@attendance']
        ];
        
        foreach ($legacyRoutes as [$method, $uri, $handler]) {
            $route = $this->router->match($method, $uri);
            $matched = $route !== null && $route->getHandler() === $handler;
            $this->addResult("Legacy Route: {$method} {$uri}", $matched, "Should route to {$handler}");
        }
    }
    
    /**
     * Test middleware configuration
     */
    private function testMiddlewareConfiguration(): void
    {
        echo "Testing Middleware Configuration...\n";
        
        // Test that protected routes have auth middleware
        $protectedRoute = $this->router->match('GET', '/api/employees');
        if ($protectedRoute) {
            $middleware = $protectedRoute->getMiddleware();
            $hasAuth = in_array('auth', $middleware);
            $this->addResult("Protected Route Middleware", $hasAuth, "Should have 'auth' middleware");
        }
        
        // Test that admin routes have role middleware
        $adminRoute = $this->router->match('POST', '/api/employees');
        if ($adminRoute) {
            $middleware = $adminRoute->getMiddleware();
            $hasRole = false;
            foreach ($middleware as $mw) {
                if (strpos($mw, 'role:') === 0) {
                    $hasRole = true;
                    break;
                }
            }
            $this->addResult("Admin Route Middleware", $hasRole, "Should have 'role:admin' middleware");
        }
        
        // Test that all routes have logging middleware
        $anyRoute = $this->router->match('POST', '/api/auth/login');
        if ($anyRoute) {
            $middleware = $anyRoute->getMiddleware();
            $hasLogging = in_array('logging', $middleware);
            $this->addResult("Logging Middleware", $hasLogging, "Should have 'logging' middleware");
        }
    }
    
    /**
     * Add test result
     */
    private function addResult(string $test, bool $passed, string $message): void
    {
        $this->results[] = [
            'test' => $test,
            'passed' => $passed,
            'message' => $message
        ];
        
        $status = $passed ? '✓' : '✗';
        $color = $passed ? "\033[32m" : "\033[31m";
        $reset = "\033[0m";
        
        echo "  {$color}{$status}{$reset} {$test}: {$message}\n";
    }
    
    /**
     * Print test results summary
     */
    private function printResults(): void
    {
        echo "\n=== Test Results Summary ===\n";
        
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['passed']));
        $failed = $total - $passed;
        
        echo "Total Tests: {$total}\n";
        echo "\033[32mPassed: {$passed}\033[0m\n";
        
        if ($failed > 0) {
            echo "\033[31mFailed: {$failed}\033[0m\n";
            echo "\nFailed Tests:\n";
            foreach ($this->results as $result) {
                if (!$result['passed']) {
                    echo "  - {$result['test']}: {$result['message']}\n";
                }
            }
        }
        
        $percentage = $total > 0 ? round(($passed / $total) * 100, 2) : 0;
        echo "\nSuccess Rate: {$percentage}%\n";
        
        if ($percentage === 100.0) {
            echo "\n\033[32m✓ All components wired successfully!\033[0m\n";
        } else {
            echo "\n\033[33m⚠ Some components need attention\033[0m\n";
        }
    }
}

// Run tests
try {
    $test = new Task8_1_IntegrationTest();
    $test->runTests();
} catch (Exception $e) {
    echo "\033[31mTest execution failed: {$e->getMessage()}\033[0m\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
