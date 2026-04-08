<?php
/**
 * Verify Employee Creation Fix
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== VERIFY EMPLOYEE CREATION FIX ===\n\n";

try {
    $container = \Core\Container::getInstance();
    $employeeService = $container->resolve(\Services\EmployeeService::class);
    
    // Test creating employee with custom password
    $testData = [
        'first_name' => 'FixTest',
        'last_name' => 'Employee',
        'work_email' => 'fixtest' . time() . '@test.com', // Unique email
        'mobile_number' => '09123456789',
        'department' => 'IT',
        'position' => 'Tester',
        'employment_status' => 'Regular',
        'password' => 'CustomPass123!' // Custom password provided
    ];
    
    echo "Creating employee with CUSTOM password...\n";
    echo "Email: " . $testData['work_email'] . "\n";
    echo "Password: " . $testData['password'] . "\n\n";
    
    $result = $employeeService->createEmployee($testData);
    
    echo "Employee created!\n";
    echo "  ID: " . $result['id'] . "\n";
    echo "  Name: " . $result['first_name'] . " " . $result['last_name'] . "\n";
    echo "  Email: " . $result['work_email'] . "\n";
    echo "  Force Password Change: " . (isset($result['force_password_change']) ? ($result['force_password_change'] ? 'TRUE ✅' : 'FALSE ❌') : 'NOT SET') . "\n";
    
    // Verify in database
    echo "\nVerifying in database...\n";
    $db = $container->resolve(\Core\SupabaseConnection::class);
    $dbRecord = $db->select('employees', ['id' => $result['id']]);
    
    if (!empty($dbRecord)) {
        $emp = $dbRecord[0];
        echo "Database record:\n";
        echo "  force_password_change: " . ($emp['force_password_change'] ? 'TRUE ✅' : 'FALSE ❌') . "\n";
        echo "  password_changed_at: " . ($emp['password_changed_at'] ?? 'NULL') . "\n";
        
        if ($emp['force_password_change'] === true && $emp['password_changed_at'] === null) {
            echo "\n✅ SUCCESS! Employee created with correct settings:\n";
            echo "   - force_password_change = TRUE (will be forced to change on first login)\n";
            echo "   - password_changed_at = NULL (hasn't changed password yet)\n";
        } else {
            echo "\n❌ PROBLEM! Settings are incorrect.\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
