<?php

/**
 * Phase 3 Employee Management Integration Test
 * 
 * Tests the employee management functionality including:
 * - EmployeeController API methods
 * - EmployeeService business logic
 * - Employee model enhancements
 * - View rendering
 */

require_once __DIR__ . '/../src/autoload.php';

use Core\Container;
use Core\Request;
use Core\Response;
use Controllers\EmployeeController;
use Services\EmployeeService;
use Models\Employee;
use Core\View;

class Phase3EmployeeManagementTest
{
    private Container $container;
    private EmployeeController $controller;
    private EmployeeService $service;
    private Employee $model;
    private View $view;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->setupDependencies();
    }
    
    private function setupDependencies()
    {
        // Get database connection from container
        $db = $this->container->resolve(\Core\SupabaseConnection::class);
        
        // Register dependencies in container
        $this->container->bind(Employee::class, function() use ($db) {
            return new Employee($db);
        });
        
        $this->container->bind(\Services\AuthService::class, function() use ($db) {
            $userModel = new \Models\User($db);
            return new \Services\AuthService($userModel);
        });
        
        $this->container->bind(EmployeeService::class, function($container) use ($db) {
            $employeeModel = $container->resolve(Employee::class);
            $authService = $container->resolve(\Services\AuthService::class);
            return new EmployeeService($employeeModel, $authService);
        });
        
        $this->controller = new EmployeeController($this->container);
        $this->service = $this->container->resolve(EmployeeService::class);
        $this->model = $this->container->resolve(Employee::class);
        $this->view = new View();
    }
    
    public function runTests()
    {
        echo "Running Phase 3 Employee Management Tests...\n\n";
        
        $tests = [
            'testEmployeeModelEnhancements',
            'testEmployeeServiceMethods',
            'testViewRendering',
            'testControllerMethods'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $test) {
            try {
                echo "Running {$test}... ";
                $this->$test();
                echo "PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nTest Results: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "✅ All Phase 3 tests passed!\n";
        } else {
            echo "❌ Some tests failed. Check implementation.\n";
        }
    }
    
    private function testEmployeeModelEnhancements()
    {
        // Test search functionality
        $searchParams = [
            'query' => 'test',
            'department' => 'IT',
            'status' => 'active'
        ];
        
        $results = $this->model->searchEmployees($searchParams);
        
        if (!is_array($results)) {
            throw new Exception('searchEmployees should return an array');
        }
        
        // Test statistics method
        $stats = $this->model->getStatistics();
        
        $requiredKeys = ['total', 'active', 'inactive', 'by_status', 'by_department', 'recent_hires'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $stats)) {
                throw new Exception("Statistics missing key: {$key}");
            }
        }
        
        // Test unique departments
        $departments = $this->model->getUniqueDepartments();
        if (!is_array($departments)) {
            throw new Exception('getUniqueDepartments should return an array');
        }
    }
    
    private function testEmployeeServiceMethods()
    {
        // Test getEmployees method
        $filters = [
            'search' => '',
            'department' => '',
            'status' => '',
            'employment_status' => '',
            'position' => '',
            'limit' => 50,
            'offset' => 0,
            'order_by' => 'created_at',
            'order_dir' => 'DESC'
        ];
        
        $result = $this->service->getEmployees($filters);
        
        $requiredKeys = ['employees', 'pagination', 'filters', 'summary'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $result)) {
                throw new Exception("getEmployees result missing key: {$key}");
            }
        }
        
        // Test search employees method
        $searchParams = [
            'query' => 'test',
            'department' => '',
            'status' => '',
            'employment_status' => '',
            'position' => '',
            'limit' => 20,
            'offset' => 0,
            'sort_by' => 'created_at',
            'sort_order' => 'DESC'
        ];
        
        $searchResult = $this->service->searchEmployees($searchParams);
        
        $requiredKeys = ['employees', 'pagination', 'search'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $searchResult)) {
                throw new Exception("searchEmployees result missing key: {$key}");
            }
        }
    }
    
    private function testViewRendering()
    {
        // Test that view templates exist
        $templates = [
            'employees/list',
            'employees/profile',
            'errors/404',
            'errors/403',
            'errors/500'
        ];
        
        foreach ($templates as $template) {
            if (!$this->view->exists($template)) {
                throw new Exception("Template not found: {$template}");
            }
        }
        
        // Test rendering a simple template
        try {
            $html = $this->view->render('employees/list', [
                'employees' => [],
                'pagination' => ['total' => 0, 'current_page' => 1, 'total_pages' => 1],
                'filters' => [],
                'departments' => [],
                'user' => ['name' => 'Test User', 'role' => 'admin']
            ]);
            
            if (empty($html)) {
                throw new Exception('View rendering returned empty content');
            }
            
            // Check for basic HTML structure
            if (strpos($html, '<html') === false || strpos($html, '</html>') === false) {
                throw new Exception('Rendered HTML missing basic structure');
            }
            
        } catch (Exception $e) {
            throw new Exception('View rendering failed: ' . $e->getMessage());
        }
    }
    
    private function testControllerMethods()
    {
        // Test that controller has required methods
        $requiredMethods = [
            'index', 'show', 'create', 'update', 'delete', 'search', 'profile',
            'apiIndex', 'apiShow', 'apiCreate', 'apiUpdate', 'apiDelete', 'apiSearch',
            'indexView', 'showView', 'createForm', 'editForm'
        ];
        
        $reflection = new ReflectionClass($this->controller);
        
        foreach ($requiredMethods as $method) {
            if (!$reflection->hasMethod($method)) {
                throw new Exception("Controller missing method: {$method}");
            }
        }
        
        // Test that methods are public
        foreach ($requiredMethods as $method) {
            $methodReflection = $reflection->getMethod($method);
            if (!$methodReflection->isPublic()) {
                throw new Exception("Controller method {$method} should be public");
            }
        }
    }
}

// Run the tests
$test = new Phase3EmployeeManagementTest();
$test->runTests();