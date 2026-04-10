<?php
/**
 * Test script to verify recruitment module setup
 * Run this from command line: php test_recruitment_setup.php
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application root
define('APP_ROOT', __DIR__);

// Load framework bootstrap
require_once __DIR__ . '/src/bootstrap.php';

use Core\Container;

echo "=== Recruitment Module Setup Test ===\n\n";

try {
    $container = Container::getInstance();
    
    // Test 1: Check if Models are registered
    echo "Test 1: Model Registration\n";
    echo "  - JobPosting Model: ";
    try {
        $jobPostingModel = $container->resolve(\Models\JobPosting::class);
        echo "✓ Registered\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    echo "  - Applicant Model: ";
    try {
        $applicantModel = $container->resolve(\Models\Applicant::class);
        echo "✓ Registered\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    echo "  - ApplicantEvaluation Model: ";
    try {
        $evaluationModel = $container->resolve(\Models\ApplicantEvaluation::class);
        echo "✓ Registered\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    // Test 2: Check if Service is registered
    echo "\nTest 2: Service Registration\n";
    echo "  - RecruitmentService: ";
    try {
        $recruitmentService = $container->resolve(\Services\RecruitmentService::class);
        echo "✓ Registered\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Check if Controller is registered
    echo "\nTest 3: Controller Registration\n";
    echo "  - RecruitmentController: ";
    try {
        $recruitmentController = $container->resolve(\Controllers\RecruitmentController::class);
        echo "✓ Registered\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    // Test 4: Check database tables exist
    echo "\nTest 4: Database Tables\n";
    try {
        $db = $container->resolve('DatabaseConnection');
        
        echo "  - job_postings table: ";
        $result = $db->select('job_postings', [], ['limit' => 1]);
        echo "✓ Exists\n";
        
        echo "  - applicants table: ";
        $result = $db->select('applicants', [], ['limit' => 1]);
        echo "✓ Exists\n";
        
        echo "  - applicant_evaluations table: ";
        $result = $db->select('applicant_evaluations', [], ['limit' => 1]);
        echo "✓ Exists\n";
        
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    // Test 5: Check routes are registered
    echo "\nTest 5: Routes Registration\n";
    try {
        $router = $container->resolve(\Core\Router::class);
        
        // Load routes
        $routeLoader = require __DIR__ . '/config/routes.php';
        $routeLoader($router);
        
        $allRoutes = $router->getRoutes();
        $recruitmentRoutes = array_filter($allRoutes, function($route) {
            return strpos($route['pattern'], 'recruitment') !== false;
        });
        
        echo "  - Found " . count($recruitmentRoutes) . " recruitment routes\n";
        
        if (count($recruitmentRoutes) > 0) {
            echo "  - Sample routes:\n";
            $count = 0;
            foreach ($recruitmentRoutes as $route) {
                if ($count++ < 5) {
                    echo "    • {$route['method']} {$route['pattern']}\n";
                }
            }
            echo "  ✓ Routes registered\n";
        } else {
            echo "  ✗ No recruitment routes found\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Test Complete ===\n";
    echo "\nIf all tests passed, your recruitment module is ready!\n";
    echo "Access it at: http://your-domain/recruitment\n\n";
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
