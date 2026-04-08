<?php
/**
 * Integration Test for Phase 4: Dashboard and Reporting Migration
 * 
 * Tests:
 * - Task 5.1: DashboardController and navigation
 * - Task 5.3: ReportService functionality
 * - Task 5.5: Dashboard metrics API
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../api/config/database.php';

use Core\Container;
use Core\Request;
use Controllers\DashboardController;
use Controllers\ReportController;
use Services\ReportService;

class Task5_IntegrationTest
{
    private Container $container;
    private array $results = [];
    
    public function __construct()
    {
        $this->container = Container::getInstance();
    }
    
    /**
     * Run all tests
     */
    public function runAll(): void
    {
        echo "=== Phase 4: Dashboard and Reporting Migration Tests ===\n\n";
        
        $this->testDashboardControllerInstantiation();
        $this->testReportServiceInstantiation();
        $this->testReportControllerInstantiation();
        $this->testReportServiceMethods();
        $this->testDashboardMetricsStructure();
        
        $this->printResults();
    }
    
    /**
     * Test 5.1: DashboardController can be instantiated
     */
    private function testDashboardControllerInstantiation(): void
    {
        echo "Test 5.1: DashboardController Instantiation\n";
        
        try {
            $controller = $this->container->resolve(DashboardController::class);
            
            if ($controller instanceof DashboardController) {
                $this->pass("DashboardController instantiated successfully");
            } else {
                $this->fail("DashboardController is not the correct type");
            }
        } catch (Exception $e) {
            $this->fail("Failed to instantiate DashboardController: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Test 5.3: ReportService can be instantiated
     */
    private function testReportServiceInstantiation(): void
    {
        echo "Test 5.3: ReportService Instantiation\n";
        
        try {
            $service = $this->container->resolve(ReportService::class);
            
            if ($service instanceof ReportService) {
                $this->pass("ReportService instantiated successfully");
            } else {
                $this->fail("ReportService is not the correct type");
            }
        } catch (Exception $e) {
            $this->fail("Failed to instantiate ReportService: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Test 5.3: ReportController can be instantiated
     */
    private function testReportControllerInstantiation(): void
    {
        echo "Test 5.3: ReportController Instantiation\n";
        
        try {
            $controller = $this->container->resolve(ReportController::class);
            
            if ($controller instanceof ReportController) {
                $this->pass("ReportController instantiated successfully");
            } else {
                $this->fail("ReportController is not the correct type");
            }
        } catch (Exception $e) {
            $this->fail("Failed to instantiate ReportController: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Test 5.3: ReportService methods exist and have correct signatures
     */
    private function testReportServiceMethods(): void
    {
        echo "Test 5.3: ReportService Methods\n";
        
        try {
            $service = $this->container->resolve(ReportService::class);
            
            // Check if methods exist
            $methods = [
                'generateAttendanceReport',
                'generateLeaveReport',
                'generateHeadcountReport'
            ];
            
            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->pass("Method {$method} exists");
                } else {
                    $this->fail("Method {$method} does not exist");
                }
            }
            
        } catch (Exception $e) {
            $this->fail("Failed to test ReportService methods: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Test 5.5: Dashboard metrics structure
     */
    private function testDashboardMetricsStructure(): void
    {
        echo "Test 5.5: Dashboard Metrics Structure\n";
        
        try {
            // Test that the controller has the metrics method
            $controller = $this->container->resolve(DashboardController::class);
            
            if (method_exists($controller, 'metrics')) {
                $this->pass("DashboardController has metrics method");
            } else {
                $this->fail("DashboardController missing metrics method");
            }
            
            if (method_exists($controller, 'admin')) {
                $this->pass("DashboardController has admin method");
            } else {
                $this->fail("DashboardController missing admin method");
            }
            
            if (method_exists($controller, 'employee')) {
                $this->pass("DashboardController has employee method");
            } else {
                $this->fail("DashboardController missing employee method");
            }
            
            // Test ReportController methods
            $reportController = $this->container->resolve(ReportController::class);
            
            $reportMethods = ['attendance', 'leave', 'headcount'];
            foreach ($reportMethods as $method) {
                if (method_exists($reportController, $method)) {
                    $this->pass("ReportController has {$method} method");
                } else {
                    $this->fail("ReportController missing {$method} method");
                }
            }
            
        } catch (Exception $e) {
            $this->fail("Failed to test dashboard metrics: " . $e->getMessage());
        }
        
        echo "\n";
    }
    
    /**
     * Mark test as passed
     */
    private function pass(string $message): void
    {
        echo "  ✓ PASS: {$message}\n";
        $this->results[] = ['status' => 'pass', 'message' => $message];
    }
    
    /**
     * Mark test as failed
     */
    private function fail(string $message): void
    {
        echo "  ✗ FAIL: {$message}\n";
        $this->results[] = ['status' => 'fail', 'message' => $message];
    }
    
    /**
     * Print test results summary
     */
    private function printResults(): void
    {
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'pass'));
        $failed = count(array_filter($this->results, fn($r) => $r['status'] === 'fail'));
        $total = count($this->results);
        
        echo "=== Test Results ===\n";
        echo "Total: {$total}\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        
        if ($failed === 0) {
            echo "\n✓ All tests passed!\n";
        } else {
            echo "\n✗ Some tests failed.\n";
        }
    }
}

// Run tests
try {
    $test = new Task5_IntegrationTest();
    $test->runAll();
} catch (Exception $e) {
    echo "Fatal error running tests: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
