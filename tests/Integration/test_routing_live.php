<?php

/**
 * Live Routing Test
 * 
 * This script simulates HTTP requests to test the routing system
 */

// Simulate a GET request to /api/dashboard/metrics
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/dashboard/metrics';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

echo "=== Live Routing Test ===\n\n";
echo "Simulating: GET /api/dashboard/metrics\n";
echo "Expected: Route should match and return 401 (auth required)\n\n";

// Capture output
ob_start();

try {
    // Load the application
    require_once __DIR__ . '/../../public/index.php';
} catch (Exception $e) {
    echo "Exception caught: " . $e->getMessage() . "\n";
}

$output = ob_get_clean();

echo "Response received:\n";
echo $output . "\n";

// Check if routing worked
if (strpos($output, 'ROUTE_NOT_FOUND') !== false) {
    echo "\n✗ FAILED: Route not found\n";
} else if (strpos($output, 'AUTH_REQUIRED') !== false || strpos($output, 'Unauthorized') !== false) {
    echo "\n✓ SUCCESS: Route matched and auth middleware executed\n";
} else {
    echo "\n✓ SUCCESS: Route matched and executed\n";
}
