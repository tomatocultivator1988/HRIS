<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/bootstrap.php';

use Core\Container;
use Models\LeaveRequest;
use Core\SupabaseConnection;

echo "=== LEAVE REQUEST TEST ===\n\n";

try {
    $container = Container::getInstance();
    $leaveRequestModel = $container->resolve(LeaveRequest::class);
    $db = $container->resolve(SupabaseConnection::class);
    
    // Test 1: Direct database insert
    echo "Test 1: Direct Supabase Insert\n";
    echo "--------------------------------\n";
    
    $testData = [
        'employee_id' => 'bdaa7c81-f553-491a-b0af-aeaff82987c7', // Kian's ID
        'leave_type_id' => '2',
        'start_date' => '2026-04-21',
        'end_date' => '2026-04-25',
        'total_days' => 5,
        'reason' => 'Test leave request from script',
        'status' => 'Pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    echo "Data to insert:\n";
    print_r($testData);
    echo "\n";
    
    $result = $db->insert('leave_requests', $testData);
    
    echo "Insert result:\n";
    print_r($result);
    echo "\n";
    
    // Test 2: Check if record was created
    echo "\nTest 2: Query the database to verify\n";
    echo "-------------------------------------\n";
    
    $query = $db->select('leave_requests', [
        'employee_id' => 'bdaa7c81-f553-491a-b0af-aeaff82987c7',
        'start_date' => '2026-04-21'
    ]);
    
    echo "Query result:\n";
    print_r($query);
    echo "\n";
    
    // Test 3: Use Model's create method
    echo "\nTest 3: Using LeaveRequest Model create()\n";
    echo "------------------------------------------\n";
    
    $testData2 = [
        'employee_id' => 'bdaa7c81-f553-491a-b0af-aeaff82987c7',
        'leave_type_id' => '1',
        'start_date' => '2026-04-28',
        'end_date' => '2026-05-02',
        'total_days' => 5,
        'reason' => 'Test leave request using Model',
        'status' => 'Pending'
    ];
    
    echo "Data to create:\n";
    print_r($testData2);
    echo "\n";
    
    try {
        $created = $leaveRequestModel->create($testData2);
        echo "Model create result:\n";
        print_r($created);
        echo "\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Test 4: Check all leave requests for Kian
    echo "\nTest 4: All leave requests for Kian\n";
    echo "------------------------------------\n";
    
    $allRequests = $leaveRequestModel->getByEmployee('bdaa7c81-f553-491a-b0af-aeaff82987c7');
    echo "Total requests found: " . count($allRequests) . "\n";
    
    if (!empty($allRequests)) {
        echo "Requests:\n";
        foreach ($allRequests as $req) {
            echo "  - ID: {$req['id']}, Start: {$req['start_date']}, End: {$req['end_date']}, Status: {$req['status']}\n";
        }
    } else {
        echo "No requests found!\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
