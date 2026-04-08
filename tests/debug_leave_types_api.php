<?php
/**
 * Debug the /api/leave/types endpoint
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== Debugging /api/leave/types API Endpoint ===\n\n";

try {
    $container = \Core\Container::getInstance();
    
    echo "1. Testing LeaveController instantiation...\n";
    $leaveController = $container->resolve(\Controllers\LeaveController::class);
    echo "   ✓ LeaveController created successfully\n\n";
    
    echo "2. Testing LeaveService instantiation...\n";
    $leaveService = $container->resolve(\Services\LeaveService::class);
    echo "   ✓ LeaveService created successfully\n\n";
    
    echo "3. Testing direct database access to leave_types...\n";
    $db = $container->resolve(\Core\SupabaseConnection::class);
    $leaveTypes = $db->select('leave_types', [], ['limit' => 5]);
    if (!empty($leaveTypes)) {
        echo "   ✓ Database access works - found " . count($leaveTypes) . " leave types\n";
        foreach ($leaveTypes as $type) {
            echo "     - {$type['name']} (ID: {$type['id']})\n";
        }
    } else {
        echo "   ✗ Database access failed\n";
        exit(1);
    }
    
    echo "\n4. Testing LeaveService getLeaveTypes method...\n";
    
    // Check if getLeaveTypes method exists
    if (method_exists($leaveService, 'getLeaveTypes')) {
        echo "   ✓ getLeaveTypes method exists\n";
        try {
            $serviceTypes = $leaveService->getLeaveTypes();
            echo "   ✓ getLeaveTypes method works - returned " . count($serviceTypes) . " types\n";
        } catch (Exception $e) {
            echo "   ✗ getLeaveTypes method failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ✗ getLeaveTypes method does NOT exist in LeaveService\n";
        echo "   This is the problem! The method is missing.\n";
    }
    
    echo "\n5. Testing LeaveController types method...\n";
    
    // Create a mock request with authentication
    $request = new \Core\Request();
    
    // Mock user data
    $userData = [
        'id' => '27c542c2-a9df-4c48-b53c-7e3acdc8c82f',
        'role' => 'employee',
        'email' => 'test@example.com'
    ];
    $request->setUser($userData);
    
    // Set the request in the controller
    $reflection = new ReflectionClass($leaveController);
    $requestProperty = $reflection->getProperty('request');
    $requestProperty->setAccessible(true);
    $requestProperty->setValue($leaveController, $request);
    
    try {
        $response = $leaveController->types($request);
        echo "   ✓ Controller types method executed\n";
        echo "   Response status: " . $response->getStatusCode() . "\n";
        echo "   Response body: " . substr($response->getBody(), 0, 200) . "...\n";
    } catch (Exception $e) {
        echo "   ✗ Controller types method failed: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nDebug completed.\n";