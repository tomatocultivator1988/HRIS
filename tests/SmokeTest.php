<?php

/**
 * Smoke Test - Quick verification that the MVC system is operational
 * 
 * This test simulates HTTP requests to verify the routing and controller
 * dispatch system is working correctly.
 */

// Simulate a GET request to the login endpoint
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

// Capture output
ob_start();

try {
    // Load the application entry point
    require __DIR__ . '/../public/index.php';
    
    $output = ob_get_clean();
    
    // Check if we got a response
    if (!empty($output)) {
        echo "\033[32m✓ Smoke Test Passed: Application responded to request\033[0m\n";
        echo "Response length: " . strlen($output) . " bytes\n";
        
        // Check if it's HTML (login page)
        if (strpos($output, '<html') !== false || strpos($output, '<!DOCTYPE') !== false) {
            echo "\033[32m✓ HTML response detected (login page)\033[0m\n";
        }
    } else {
        echo "\033[31m✗ Smoke Test Failed: No response from application\033[0m\n";
        exit(1);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "\033[31m✗ Smoke Test Failed: {$e->getMessage()}\033[0m\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}

echo "\n\033[32m✓ MVC System is operational!\033[0m\n";
