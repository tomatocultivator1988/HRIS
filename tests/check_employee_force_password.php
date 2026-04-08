<?php
/**
 * Check Employee Force Password Change Status
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== CHECK EMPLOYEE FORCE PASSWORD STATUS ===\n\n";

try {
    $container = \Core\Container::getInstance();
    $db = $container->resolve(\Core\SupabaseConnection::class);
    
    // Get the test employee
    $email = 'test@gmail.com';
    $supabaseUserId = 'b3cf3158-be4b-4cba-8a36-865adaf2b9ce';
    
    echo "Checking employee: $email\n";
    echo "Supabase User ID: $supabaseUserId\n\n";
    
    // Check in employees table
    echo "=== EMPLOYEES TABLE ===\n";
    $result = $db->select('employees', ['supabase_user_id' => $supabaseUserId]);
    
    if (!empty($result)) {
        $employee = $result[0];
        echo "Found employee:\n";
        echo "  ID: " . $employee['id'] . "\n";
        echo "  Name: " . $employee['first_name'] . " " . $employee['last_name'] . "\n";
        echo "  Email: " . ($employee['email'] ?? 'N/A') . "\n";
        echo "  Force Password Change: " . (isset($employee['force_password_change']) ? ($employee['force_password_change'] ? 'TRUE' : 'FALSE') : 'NOT SET') . "\n";
        echo "  Password Changed At: " . ($employee['password_changed_at'] ?? 'NULL') . "\n";
        echo "  Created At: " . ($employee['created_at'] ?? 'N/A') . "\n";
        echo "  Is Active: " . ($employee['is_active'] ? 'TRUE' : 'FALSE') . "\n";
        
        // Check if force_password_change field exists
        if (!isset($employee['force_password_change'])) {
            echo "\n⚠️ WARNING: force_password_change field does NOT exist in database!\n";
            echo "This field needs to be added to the employees table.\n";
        } elseif ($employee['force_password_change'] === false) {
            echo "\n❌ PROBLEM: force_password_change is FALSE but should be TRUE for new employees!\n";
        } else {
            echo "\n✅ force_password_change is correctly set to TRUE\n";
        }
    } else {
        echo "❌ Employee not found in employees table\n";
    }
    
    echo "\n=== CHECKING ALL EMPLOYEES ===\n";
    $allResult = $db->select('employees', ['is_active' => true]);
    
    if (!empty($allResult)) {
        echo "Found " . count($allResult) . " active employees:\n\n";
        
        foreach ($allResult as $emp) {
            $forceChange = isset($emp['force_password_change']) ? ($emp['force_password_change'] ? 'TRUE' : 'FALSE') : 'NOT SET';
            $passwordChangedAt = $emp['password_changed_at'] ?? 'NULL';
            
            echo "- " . $emp['first_name'] . " " . $emp['last_name'] . "\n";
            echo "  ID: " . $emp['id'] . "\n";
            echo "  Supabase User ID: " . ($emp['supabase_user_id'] ?? 'N/A') . "\n";
            echo "  Force Password: $forceChange\n";
            echo "  Password Changed: $passwordChangedAt\n";
            echo "\n";
        }
    }
    
    echo "\n=== CHECKING TABLE SCHEMA ===\n";
    echo "Attempting to get table structure...\n";
    
    // Try to get one record to see all fields
    $schemaCheck = $db->select('employees', [], ['limit' => 1]);
    if (!empty($schemaCheck)) {
        $fields = array_keys($schemaCheck[0]);
        echo "Available fields in employees table:\n";
        foreach ($fields as $field) {
            echo "  - $field\n";
        }
        
        if (!in_array('force_password_change', $fields)) {
            echo "\n❌ MISSING FIELD: force_password_change\n";
            echo "You need to add this field to the employees table in Supabase!\n\n";
            echo "SQL to add the field:\n";
            echo "ALTER TABLE employees ADD COLUMN force_password_change BOOLEAN DEFAULT TRUE;\n";
            echo "ALTER TABLE employees ADD COLUMN password_changed_at TIMESTAMP;\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
