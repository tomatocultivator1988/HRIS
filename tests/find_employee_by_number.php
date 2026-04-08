<?php
/**
 * Find employee by employee number
 */

require_once __DIR__ . '/../src/autoload.php';

$config = require __DIR__ . '/../config/supabase.php';
$db = new \Core\SupabaseConnection($config);

$employeeNumber = 'EMP-1317';

echo "=== Finding Employee by Number ===\n\n";

echo "Searching for employee_id = '$employeeNumber'...\n";
$employees = $db->select('employees', ['employee_id' => $employeeNumber]);

if (!empty($employees)) {
    echo "✅ Employee found:\n";
    $employee = $employees[0];
    print_r($employee);
    echo "\n";
    echo "Employee UUID: {$employee['id']}\n";
    echo "Employee Number: {$employee['employee_id']}\n";
} else {
    echo "❌ Employee not found!\n";
    
    echo "\nListing all employees...\n";
    $allEmployees = $db->select('employees', [], ['limit' => 5]);
    foreach ($allEmployees as $emp) {
        echo "- ID: {$emp['id']}, Number: {$emp['employee_id']}, Name: {$emp['first_name']} {$emp['last_name']}\n";
    }
}

echo "\n=== Search Complete ===\n";
