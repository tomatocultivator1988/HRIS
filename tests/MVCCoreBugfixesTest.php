<?php

/**
 * MVC Core Bugfixes Property-Based Test
 * 
 * **Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 6.1, 6.2, 6.3, 6.5, 6.6, 7.1, 7.4, 8.1, 8.2**
 * 
 * This test validates that all 8 bug categories in the MVC core have been fixed:
 * 1. Request Injection - Router calls setRequest() before controller methods
 * 2. Class Redeclaration - No duplicate Request/Response classes
 * 3. Legacy Dependencies - Controllers use proper MVC patterns
 * 4. Method Signatures - All controller methods have Request parameter
 * 5. Route Parameters - Controllers read IDs from route params
 * 6. View Data Access - Templates use extracted variables
 * 7. Asset Paths - Dynamic base path for subdirectory deployment
 * 8. Consistency - Uniform redirect patterns
 * 
 * This test encodes the EXPECTED BEHAVIOR (after fixes).
 * When run on FIXED code, this test should PASS.
 */

require_once __DIR__ . '/../src/autoload.php';

use Core\Router;
use Core\Request;
use Core\Response;
use Core\Container;
use Core\View;

class MVCCoreBugfixesTest
{
    private int $testsPassed = 0;
    private int $testsFailed = 0;
    private array $failureDetails = [];
    
    public function run(): void
    {
        echo "\n=== MVC Core Bugfixes Property-Based Test ===\n";
        echo "Testing that all 8 bug categories are fixed.\n\n";
        
        // Category 1: Request Injection
        $this->testRequestInjection();
        
        // Category 2: Class Redeclaration
        $this->testNoClassRedeclaration();
        
        // Category 3: Legacy Dependencies
        $this->testNoLegacyDependencies();
        
        // Category 4: Method Signatures
        $this->testMethodSignatures();
        
        // Category 5: Route Parameters
        $this->testRouteParameters();
        
        // Category 6: View Data Access
        $this->testViewDataAccess();
        
        // Category 7: Asset Paths
        $this->testAssetPaths();
        
        // Category 8: Consistency
        $this->testConsistency();
        
        $this->printSummary();
    }
    
    /**
     * Property 1: Request Injection
     * 
     * For any controller method invocation where Router::dispatch() is called,
     * the fixed Router SHALL call $controller->setRequest($request) before
     * invoking $controller->$method($request), ensuring $this->request is properly set.
     */
    private function testRequestInjection(): void
    {
        echo "Test 1: Request Injection - Router calls setRequest() before controller methods\n";
        
        try {
            // Check that Router::dispatch() contains setRequest() call
            $routerFile = file_get_contents(__DIR__ . '/../src/Core/Router.php');
            
            // Verify setRequest() is called in dispatch method
            $hasSetRequest = preg_match('/\$controller->setRequest\(\$request\)/', $routerFile);
            
            if (!$hasSetRequest) {
                $this->fail('Test 1', 'Router::dispatch() does not call setRequest() on controller');
                return;
            }
            
            // Verify setRequest() is called BEFORE the controller method invocation
            // Extract the dispatch method
            if (preg_match('/function dispatch\(.*?\):.*?\{(.*?)\n    \}/s', $routerFile, $matches)) {
                $dispatchMethod = $matches[1];
                
                // Find positions of setRequest and method invocation
                $setRequestPos = strpos($dispatchMethod, '$controller->setRequest($request)');
                $methodCallPos = strpos($dispatchMethod, '$controller->$method($request)');
                
                if ($setRequestPos === false) {
                    $this->fail('Test 1', 'setRequest() call not found in dispatch method');
                    return;
                }
                
                if ($methodCallPos === false) {
                    $this->fail('Test 1', 'Controller method invocation not found in dispatch method');
                    return;
                }
                
                if ($setRequestPos >= $methodCallPos) {
                    $this->fail('Test 1', 'setRequest() is called AFTER controller method invocation (should be before)');
                    return;
                }
            }
            
            $this->pass('Test 1');
            
        } catch (Exception $e) {
            $this->fail('Test 1', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 2: Class Redeclaration
     * 
     * For any application bootstrap where both Router.php and standalone
     * Request.php/Response.php are loaded, the fixed code SHALL load only
     * the standalone classes without redeclaration errors because stub
     * classes are removed from Router.php.
     */
    private function testNoClassRedeclaration(): void
    {
        echo "Test 2: Class Redeclaration - No duplicate Request/Response classes\n";
        
        try {
            // Check that standalone Request.php and Response.php exist
            $requestFile = __DIR__ . '/../src/Core/Request.php';
            $responseFile = __DIR__ . '/../src/Core/Response.php';
            
            if (!file_exists($requestFile)) {
                $this->fail('Test 2', 'Standalone Request.php does not exist');
                return;
            }
            
            if (!file_exists($responseFile)) {
                $this->fail('Test 2', 'Standalone Response.php does not exist');
                return;
            }
            
            // Check that Router.php does NOT contain duplicate Request/Response class definitions
            $routerFile = file_get_contents(__DIR__ . '/../src/Core/Router.php');
            
            // Count occurrences of "class Request" and "class Response" in Router.php
            // Note: We expect 0 occurrences since they should be in standalone files
            // However, the Route class should still be there
            $requestClassCount = preg_match_all('/^class Request\b/m', $routerFile);
            $responseClassCount = preg_match_all('/^class Response\b/m', $routerFile);
            
            if ($requestClassCount > 0) {
                $this->fail('Test 2', "Router.php contains {$requestClassCount} Request class definition(s) - should be 0 (moved to standalone file)");
                return;
            }
            
            if ($responseClassCount > 0) {
                $this->fail('Test 2', "Router.php contains {$responseClassCount} Response class definition(s) - should be 0 (moved to standalone file)");
                return;
            }
            
            // Verify that Request and Response classes can be loaded without errors
            if (!class_exists('Core\\Request')) {
                $this->fail('Test 2', 'Core\\Request class cannot be loaded');
                return;
            }
            
            if (!class_exists('Core\\Response')) {
                $this->fail('Test 2', 'Core\\Response class cannot be loaded');
                return;
            }
            
            // Check that Request class has setStartTime() and getStartTime() methods
            $requestReflection = new ReflectionClass('Core\\Request');
            if (!$requestReflection->hasMethod('setStartTime')) {
                $this->fail('Test 2', 'Request class missing setStartTime() method');
                return;
            }
            
            if (!$requestReflection->hasMethod('getStartTime')) {
                $this->fail('Test 2', 'Request class missing getStartTime() method');
                return;
            }
            
            $this->pass('Test 2');
            
        } catch (Exception $e) {
            $this->fail('Test 2', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 3: Legacy Dependencies
     * 
     * For any DashboardController or AnnouncementController method invocation,
     * the fixed methods SHALL use proper MVC Service layer and Model classes
     * instead of trying to require non-existent legacy API files or call
     * non-existent helper classes.
     */
    private function testNoLegacyDependencies(): void
    {
        echo "Test 3: Legacy Dependencies - Controllers use proper MVC patterns\n";
        
        try {
            // Check DashboardController
            $dashboardFile = file_get_contents(__DIR__ . '/../src/Controllers/DashboardController.php');
            
            // Should NOT contain legacy file requires
            if (preg_match('/require.*\/api\/config\/database\.php/', $dashboardFile)) {
                $this->fail('Test 3', 'DashboardController still requires legacy /api/config/database.php');
                return;
            }
            
            // Should NOT contain DatabaseHelper calls
            if (preg_match('/DatabaseHelper::/', $dashboardFile)) {
                $this->fail('Test 3', 'DashboardController still calls DatabaseHelper (non-existent class)');
                return;
            }
            
            // Should NOT contain file_get_contents for HTML files
            if (preg_match('/file_get_contents.*\.html/', $dashboardFile)) {
                $this->fail('Test 3', 'DashboardController still uses file_get_contents for HTML files');
                return;
            }
            
            // Should use View::render() instead
            if (!preg_match('/View::render|->view->render|\$this->view\(/', $dashboardFile)) {
                $this->fail('Test 3', 'DashboardController does not use View::render() for rendering');
                return;
            }
            
            // Check AnnouncementController
            $announcementFile = file_get_contents(__DIR__ . '/../src/Controllers/AnnouncementController.php');
            
            // Should NOT contain legacy AnnouncementManager requires
            if (preg_match('/require.*AnnouncementManager\.php/', $announcementFile)) {
                $this->fail('Test 3', 'AnnouncementController still requires legacy AnnouncementManager.php');
                return;
            }
            
            // Should NOT contain AnnouncementManager static calls
            if (preg_match('/\\\\AnnouncementManager::/', $announcementFile)) {
                $this->fail('Test 3', 'AnnouncementController still calls AnnouncementManager (non-existent class)');
                return;
            }
            
            // Should use AnnouncementService instead
            if (!preg_match('/AnnouncementService|announcementService/', $announcementFile)) {
                $this->fail('Test 3', 'AnnouncementController does not use AnnouncementService');
                return;
            }
            
            // Verify AnnouncementService exists
            if (!file_exists(__DIR__ . '/../src/Services/AnnouncementService.php')) {
                $this->fail('Test 3', 'AnnouncementService.php does not exist');
                return;
            }
            
            $this->pass('Test 3');
            
        } catch (Exception $e) {
            $this->fail('Test 3', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 4: Method Signatures
     * 
     * For any controller method that Router calls with $controller->$method($request),
     * the fixed method SHALL declare (Request $request): Response signature to
     * properly receive the request parameter.
     */
    private function testMethodSignatures(): void
    {
        echo "Test 4: Method Signatures - All controller methods have Request parameter\n";
        
        try {
            $controllers = [
                'AttendanceController',
                'DashboardController',
                'EmployeeController',
                'LeaveController',
                'ReportController'
            ];
            
            foreach ($controllers as $controllerName) {
                $controllerClass = "Controllers\\{$controllerName}";
                
                if (!class_exists($controllerClass)) {
                    $this->fail('Test 4', "{$controllerClass} does not exist");
                    return;
                }
                
                $reflection = new ReflectionClass($controllerClass);
                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                
                foreach ($methods as $method) {
                    // Skip constructor and inherited methods
                    if ($method->getName() === '__construct' || $method->getDeclaringClass()->getName() !== $controllerClass) {
                        continue;
                    }
                    
                    // Skip methods that are clearly not route handlers (helpers, private, etc.)
                    if (strpos($method->getName(), '__') === 0) {
                        continue;
                    }
                    
                    $parameters = $method->getParameters();
                    
                    // Route handler methods should have at least one parameter (Request)
                    if (count($parameters) === 0) {
                        $this->fail('Test 4', "{$controllerClass}::{$method->getName()}() has no parameters (should have Request parameter)");
                        return;
                    }
                    
                    // First parameter should be Request
                    $firstParam = $parameters[0];
                    $paramType = $firstParam->getType();
                    
                    if ($paramType === null) {
                        $this->fail('Test 4', "{$controllerClass}::{$method->getName()}() first parameter has no type hint (should be Request)");
                        return;
                    }
                    
                    $typeName = $paramType->getName();
                    if ($typeName !== 'Core\\Request' && $typeName !== 'Request') {
                        $this->fail('Test 4', "{$controllerClass}::{$method->getName()}() first parameter is {$typeName} (should be Request)");
                        return;
                    }
                }
            }
            
            $this->pass('Test 4');
            
        } catch (Exception $e) {
            $this->fail('Test 4', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 5: Route Parameters
     * 
     * For any RESTful endpoint with ID in URL path (e.g., /api/leave/{id}/approve),
     * the fixed controller method SHALL read the ID from route parameter using
     * getRouteParam('id') instead of requiring it in JSON body.
     */
    private function testRouteParameters(): void
    {
        echo "Test 5: Route Parameters - Controllers read IDs from route params\n";
        
        try {
            $leaveFile = file_get_contents(__DIR__ . '/../src/Controllers/LeaveController.php');
            
            // Check approve() method
            if (preg_match('/function approve.*?\{(.*?)function\s+\w+/s', $leaveFile, $matches)) {
                $approveMethod = $matches[1];
                
                // Should use getRouteParam('id')
                if (!preg_match('/getRouteParam\([\'"]id[\'"]\)/', $approveMethod)) {
                    $this->fail('Test 5', 'LeaveController::approve() does not use getRouteParam(\'id\')');
                    return;
                }
                
                // Should NOT read request_id from JSON body as the primary ID source
                // (it's OK to have it as fallback, but route param should be primary)
                if (preg_match('/\$data\[[\'"]request_id[\'"]\]/', $approveMethod) && 
                    !preg_match('/getRouteParam\([\'"]id[\'"]\)/', $approveMethod)) {
                    $this->fail('Test 5', 'LeaveController::approve() reads request_id from JSON body instead of route parameter');
                    return;
                }
            }
            
            // Check deny() method
            if (preg_match('/function deny.*?\{(.*?)function\s+\w+/s', $leaveFile, $matches)) {
                $denyMethod = $matches[1];
                
                // Should use getRouteParam('id')
                if (!preg_match('/getRouteParam\([\'"]id[\'"]\)/', $denyMethod)) {
                    $this->fail('Test 5', 'LeaveController::deny() does not use getRouteParam(\'id\')');
                    return;
                }
            }
            
            // Check that missing methods are implemented
            $leaveReflection = new ReflectionClass('Controllers\\LeaveController');
            
            $requiredMethods = ['balance', 'types', 'credits'];
            foreach ($requiredMethods as $methodName) {
                if (!$leaveReflection->hasMethod($methodName)) {
                    $this->fail('Test 5', "LeaveController::{$methodName}() method does not exist (required by routes)");
                    return;
                }
            }
            
            $this->pass('Test 5');
            
        } catch (Exception $e) {
            $this->fail('Test 5', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 6: View Data Access
     * 
     * For any template rendered by View::render() with data array, the fixed
     * template SHALL access variables directly as $employees and $employee
     * instead of $data['employees'] and $data['employee'].
     */
    private function testViewDataAccess(): void
    {
        echo "Test 6: View Data Access - Templates use extracted variables\n";
        
        try {
            // Check employees/list.php
            $listFile = file_get_contents(__DIR__ . '/../src/Views/employees/list.php');
            
            // Should NOT access $data array
            if (preg_match('/\$data\[[\'"]employees[\'"]\]/', $listFile)) {
                $this->fail('Test 6', 'employees/list.php still accesses $data[\'employees\'] (should use $employees)');
                return;
            }
            
            if (preg_match('/\$data\[[\'"]pagination[\'"]\]/', $listFile)) {
                $this->fail('Test 6', 'employees/list.php still accesses $data[\'pagination\'] (should use $pagination)');
                return;
            }
            
            // Should use extracted variables
            if (!preg_match('/\$employees/', $listFile)) {
                $this->fail('Test 6', 'employees/list.php does not use $employees variable');
                return;
            }
            
            // Check employees/profile.php
            $profileFile = file_get_contents(__DIR__ . '/../src/Views/employees/profile.php');
            
            // Should NOT access $data array
            if (preg_match('/\$data\[[\'"]employee[\'"]\]/', $profileFile)) {
                $this->fail('Test 6', 'employees/profile.php still accesses $data[\'employee\'] (should use $employee)');
                return;
            }
            
            // Should use extracted variables
            if (!preg_match('/\$employee/', $profileFile)) {
                $this->fail('Test 6', 'employees/profile.php does not use $employee variable');
                return;
            }
            
            $this->pass('Test 6');
            
        } catch (Exception $e) {
            $this->fail('Test 6', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 7: Asset Paths
     * 
     * For any deployment on XAMPP at /HRIS/ subdirectory, the fixed templates
     * and JavaScript SHALL use dynamic base path (AppConfig.basePath or
     * base_url() helper) to generate correct asset paths.
     */
    private function testAssetPaths(): void
    {
        echo "Test 7: Asset Paths - Dynamic base path for subdirectory deployment\n";
        
        try {
            // Check that base_url() helper exists
            if (!function_exists('base_url')) {
                $this->fail('Test 7', 'base_url() helper function does not exist');
                return;
            }
            
            // Check layouts/base.php
            if (file_exists(__DIR__ . '/../src/Views/layouts/base.php')) {
                $baseLayout = file_get_contents(__DIR__ . '/../src/Views/layouts/base.php');
                
                // Should use base_url() helper or similar dynamic path
                if (preg_match('/href=[\'"]\/assets\//', $baseLayout) && 
                    !preg_match('/base_url|basePath/', $baseLayout)) {
                    $this->fail('Test 7', 'layouts/base.php uses hardcoded /assets/ paths without base_url()');
                    return;
                }
            }
            
            // Check auth/login.php
            if (file_exists(__DIR__ . '/../src/Views/auth/login.php')) {
                $loginView = file_get_contents(__DIR__ . '/../src/Views/auth/login.php');
                
                // Should use base_url() helper or similar dynamic path
                if (preg_match('/href=[\'"]\/assets\//', $loginView) && 
                    !preg_match('/base_url|basePath/', $loginView)) {
                    $this->fail('Test 7', 'auth/login.php uses hardcoded /assets/ paths without base_url()');
                    return;
                }
            }
            
            // Check auth.js
            if (file_exists(__DIR__ . '/../public/assets/js/auth.js')) {
                $authJs = file_get_contents(__DIR__ . '/../public/assets/js/auth.js');
                
                // Should use AppConfig.basePath instead of hardcoded '/HRIS/'
                if (preg_match('/[\'"]\/HRIS\/[\'"]/', $authJs) && 
                    !preg_match('/AppConfig\.basePath/', $authJs)) {
                    $this->fail('Test 7', 'auth.js uses hardcoded \'/HRIS/\' instead of AppConfig.basePath');
                    return;
                }
            }
            
            $this->pass('Test 7');
            
        } catch (Exception $e) {
            $this->fail('Test 7', 'Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Property 8: Consistency
     * 
     * For any controller that catches AuthenticationException, the fixed
     * controller SHALL consistently redirect to '/login' for uniform user
     * experience.
     */
    private function testConsistency(): void
    {
        echo "Test 8: Consistency - Uniform redirect patterns\n";
        
        try {
            $controllers = [
                'DashboardController',
                'EmployeeController',
                'ReportController',
                'LeaveController',
                'AttendanceController'
            ];
            
            foreach ($controllers as $controllerName) {
                $controllerFile = file_get_contents(__DIR__ . "/../src/Controllers/{$controllerName}.php");
                
                // Check for redirectToLogin() method or redirect on AuthenticationException
                if (preg_match('/redirectToLogin|catch.*AuthenticationException/', $controllerFile)) {
                    // Should redirect to '/login', not '/'
                    if (preg_match('/redirect\([\'"]\/[\'"][\),]/', $controllerFile) && 
                        !preg_match('/redirect\([\'"]\/login[\'"][\),]/', $controllerFile)) {
                        $this->fail('Test 8', "{$controllerName} redirects to '/' instead of '/login' on authentication failure");
                        return;
                    }
                }
            }
            
            $this->pass('Test 8');
            
        } catch (Exception $e) {
            $this->fail('Test 8', 'Exception: ' . $e->getMessage());
        }
    }
    
    private function pass(string $testName): void
    {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $this->testsPassed++;
    }
    
    private function fail(string $testName, string $reason): void
    {
        echo "  \033[31m✗ FAILED\033[0m\n";
        echo "    - {$reason}\n";
        $this->testsFailed++;
        $this->failureDetails[$testName] = $reason;
    }
    
    private function printSummary(): void
    {
        echo "\n=== Test Summary ===\n";
        echo "Total Tests: " . ($this->testsPassed + $this->testsFailed) . "\n";
        echo "\033[32mPassed: {$this->testsPassed}\033[0m\n";
        echo "\033[31mFailed: {$this->testsFailed}\033[0m\n";
        
        if ($this->testsFailed > 0) {
            echo "\n\033[31m✗ TESTS FAILED\033[0m\n";
            echo "The following bugs are NOT fixed:\n";
            foreach ($this->failureDetails as $testName => $reason) {
                echo "\n{$testName}:\n";
                echo "  - {$reason}\n";
            }
            exit(1);
        } else {
            echo "\n\033[32m✓ ALL TESTS PASSED\033[0m\n";
            echo "All 8 bug categories have been successfully fixed!\n";
            exit(0);
        }
    }
}

// Run the test
$test = new MVCCoreBugfixesTest();
$test->run();
