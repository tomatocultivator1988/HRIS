<?php
/**
 * Script to list all employees
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

echo "=== List All Employees ===\n\n";

try {
    $supabase = new SupabaseConnection();
    
    $employees = $supabase->select('employees', []);
    
    if (empty($employees)) {
        echo "No employees found.\n";
        exit(0);
    }
    
    echo "Found " . count($employees) . " employee(s):\n\n";
    
    foreach ($employees as $emp) {
        echo "ID: {$emp['id']}\n";
        echo "Name: {$emp['first_name']} {$emp['last_name']}\n";
        echo "Email: {$emp['email']}\n";
        echo "Force Password Change: " . ($emp['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
