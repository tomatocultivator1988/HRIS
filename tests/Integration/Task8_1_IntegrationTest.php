<?php

/**
 * Integration Test for Task 8.1 - Wire All Components Together
 * 
 * This test verifies that:
 * 1. All services and models are registered in the DI container
 * 2. All routes are properly configured
 * 3. Backward compatibility routes work correctly
 * 4. Middleware is properly configured
 */

require_once __DIR__ . '/../../src/bootstrap.php';

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
        $routeLoader = require __DIR__ . '/../../config/routes.php';
        $routeLoader($this->router);
    }
    
    public function runAllTests(): void
    {
        echo "=== Task 8.1 Integration Test ===\n\n";
        
        $this->testDependencyInjectionContainer();
        $this->testRoutingConfiguration();
        $this->testBackwardCompatibility();
        $this->testMiddlewareConfiguration();
        
        $this->printResults();
    }
    
    private function testDependencyInjectionContainer(): void
    {
        echo "Testing Dependency Injection Container...\n";
        
        // Test Models
        $models = [
            \Models\User::class,
            \Models\Employee::class,
            \Models\Attendance::class,
            \Models\LeaveRequest::class
        ];
        
        foreach ($models as $model) {
            try {
                $instance = $this->container->resolve($model);
                $this->addResult("✓ Model registered: " . basename(str_replace('\\', '/', $model)), true);
            } catch (\Exception $e) {
                $this->addResult("✗ Model NOT registered: " . basename(str_replace('\\', '/', $model)) . " - " . $e->getMessage(), false);
            }
        }
        
        // Test Services
        $services = [
            \Services\AuthService::class,
            \Services\EmployeeService::class,
            \Services\AttendanceService::class,
            \Services\LeaveService::class,
            \Services\ReportService::class,
            \Services\AuditLogService::class
        ];
        
        foreach ($services as $service) {
            try {
                $instance = $this->container->resolve($service);
                $this->addResult("✓ Service registered: " . basename(str_replace('\\', '/', $service)), true);
            } catch (\Exception $e) {
                $this->addResult("✗ Service NOT registered: " . basename(str_replace('\\', '/', $service)) . " - " . $e->getMessage(), false);
            }
        }
        
        // Test Controllers
        $controllers = [
            \Controllers\AuthController::class,
            \Controllers\EmployeeController::class,
            \Controllers\AttendanceController::class,
            \Controllers\LeaveController::class,
            \Controllers\DashboardController::class,
            \Controllers\ReportController::class,
            \Controllers\AnnouncementController::class
        ];
        
        foreach ($controllers as $controller) {
            try {
                $instance = $this->container->resolve($controller);
                $this->addResult("✓ Controller registered: " . basename(str_replace('\\', '/', $controller)), true);
            } catch (\Exception $e) {
                $this->addResult("✗ Controller NOT registered: " . basename(str_replace('\\', '/', $controller)) . " - " . $e->getMessage(), false);
            }
        }
        
        // Test Middleware
        $middlewares = [
            \Middleware\AuthMiddleware::class,
            \Middleware\RoleMiddleware::class,
            \Middleware\LoggingMiddleware::class,
            \Middleware\InputValidationMiddleware::class,
            \Middleware\CsrfMiddleware::class,
            \Middleware\RateLimitMiddleware::class,
            \Middleware\SecurityHeadersMiddleware::class
        ];
        
        foreach ($middlewares as $middleware) {
            try {
                $instance = $this->container->resolve($middleware);
                $this->addResult("✓ Middleware registered: " . basename(str_replace('\\', '/', $middleware)), true);
            } catch (\Exception $e) {
                $this->addResult("✗ Middleware NOT registered: " . basename(str_replace('\\', '/', $middleware)) . " - " . $e->getMessage(), false);
            }
        }
        
        echo "\n";
    }
    
    private function testRoutingConfiguration(): void
    {
        echo "Testing Routing Configuration...\n";
        
        $routes = $this->router->getRoutes();
        $this->addResult("✓ Total routes configured: " . count($routes), true);
        
        // Test critical API routes
        $criticalRoutes = [
            ['POST', '/api/auth/login', 'AuthController@login'],
            ['GET', '/api/employees', 'EmployeeController@apiIndex'],
            ['GET', '/api/dashboard/metrics', 'DashboardController@metrics'],
            ['GET', '/api/attendance/daily', 'AttendanceController@daily'],
            ['GET', '/api/leave/balance', 'LeaveController@balance'],
            ['GET', '/api/announcements', 'AnnouncementController@index'],
            ['GET', '/api/reports/attendance', 'ReportController@attendance']
        ];
        
        foreach ($criticalRoutes as [$method, $uri, $handler]) {
            $route = $this->router->match($method, $uri);
            if ($route && $route->getHandler() === $handler) {
                $this->addResult("✓ Route configured: $method $uri -> $handler", true);
            } else {
                $this->addResult("✗ Route NOT configured: $method $uri -> $handler", false);
            }
        }
        
        echo "\n";
    }
    
    private function testBackwardCompatibility(): void
    {
        echo "Testing Backward Compatibility Routes...\n";
        
        // Test legacy endpoint routes
        $legacyRoutes = [
            ['POST', '/api/auth/login.php', 'AuthController@login'],
            ['GET', '/api/employees/list.php', 'EmployeeController@apiIndex'],
            ['GET', '/api/dashboard/metrics.php', 'DashboardController@metrics'],
            ['GET', '/api/attendance/daily.php', 'AttendanceController@daily'],
            ['POST', '/api/attendance/timein.php', 'AttendanceController@timeIn'],
            ['GET', '/api/leave/balance.php', 'LeaveController@balance'],
            ['GET', '/api/announcements/list.php', 'AnnouncementController@list'],
            ['GET', '/api/reports/attendance.php', 'ReportController@attendance']
        ];
        
        foreach ($legacyRoutes as [$method, $uri, $handler]) {
            $route = $this->router->match($method, $uri);
            if ($route && $route->getHandler() === $handler) {
                $this->addResult("✓ Legacy route works: $method $uri -> $handler", true);
            } else {
                $this->addResult("✗ Legacy route BROKEN: $method $uri -> $handler", false);
            }
        }
        
        echo "\n";
    }
    
    private function testMiddlewareConfiguration(): void
    {
        echo "Testing Middleware Configuration...\n";
        
        // Test routes with middleware
        $routesWithMiddleware = [
            ['POST', '/api/auth/login', ['logging']],
            ['GET', '/api/employees', ['logging', 'auth']],
            ['POST', '/api/employees', ['logging', 'auth', 'role:admin']],
            ['GET', '/api/dashboard/metrics', ['logging', 'auth']],
            ['GET', '/api/reports/attendance', ['logging', 'auth', 'role:admin']]
        ];
        
        foreach ($routesWithMiddleware as [$method, $uri, $expectedMiddleware]) {
            $route = $this->router->match($method, $uri);
            if ($route) {
                $middleware = $route->getMiddleware();
                $middlewareMatch = $middleware === $expectedMiddleware;
                
                if ($middlewareMatch) {
                    $this->addResult("✓ Middleware configured: $method $uri -> [" . implode(', ', $middleware) . "]", true);
                } else {
                    $this->addResult("✗ Middleware mismatch: $method $uri -> Expected [" . implode(', ', $expectedMiddleware) . "], Got [" . implode(', ', $middleware) . "]", false);
                }
            } else {
                $this->addResult("✗ Route not found for middleware test: $method $uri", false);
            }
        }
        
        echo "\n";
    }
    
    private function addResult(string $message, bool $success): void
    {
        $this->results[] = ['message' => $message, 'success' => $success];
        echo $message . "\n";
    }
    
    private function printResults(): void
    {
        echo "\n=== Test Summary ===\n";
        
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['success']));
        $failed = $total - $passed;
        
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        
        if ($failed > 0) {
            echo "\n=== Failed Tests ===\n";
            foreach ($this->results as $result) {
                if (!$result['success']) {
                    echo $result['message'] . "\n";
                }
            }
        }
        
        echo "\n";
        
        if ($failed === 0) {
            echo "✓ All integration tests passed! Task 8.1 is complete.\n";
        } else {
            echo "✗ Some tests failed. Please review the failures above.\n";
        }
    }
}

// Run the tests
$test = new Task8_1_IntegrationTest();
$test->runAllTests();
