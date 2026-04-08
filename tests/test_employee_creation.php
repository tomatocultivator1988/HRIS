<?php
/**
 * Test Employee Creation Flow
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== TEST EMPLOYEE CREATION FLOW ===\n\n";

// Simulate creating employee with password
$testData1 = [
    'first_name' => 'TestUser',
    'last_name' => 'WithPassword',
    'work_email' => 'testwithpass@test.com',
    'mobile_number' => '09123456789',
    'department' => 'IT',
    'position' => 'Developer',
    'employment_status' => 'Regular',
    'password' => 'TestPass123!' // ← CUSTOM PASSWORD PROVIDED
];

echo "TEST 1: Creating employee WITH custom password\n";
echo "Data:\n";
print_r($testData1);
echo "\n";

// Check the logic
$usedDefaultPassword = empty($testData1['password']) ? true : false;
echo "Logic check:\n";
echo "  password provided: " . (!empty($testData1['password']) ? 'YES' : 'NO') . "\n";
echo "  usedDefaultPassword: " . ($usedDefaultPassword ? 'TRUE' : 'FALSE') . "\n";
echo "  force_password_change will be: " . ($usedDefaultPassword ? 'TRUE' : 'FALSE') . "\n";
echo "\n";

if (!$usedDefaultPassword) {
    echo "❌ PROBLEM: force_password_change will be FALSE!\n";
    echo "   But employee should still be forced to change password on first login!\n";
} else {
    echo "✅ OK: force_password_change will be TRUE\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// Simulate creating employee WITHOUT password
$testData2 = [
    'first_name' => 'TestUser',
    'last_name' => 'NoPassword',
    'work_email' => 'testnopass@test.com',
    'mobile_number' => '09123456789',
    'department' => 'IT',
    'position' => 'Developer',
    'employment_status' => 'Regular'
    // NO password field
];

echo "TEST 2: Creating employee WITHOUT custom password (default)\n";
echo "Data:\n";
print_r($testData2);
echo "\n";

$usedDefaultPassword2 = empty($testData2['password']) ? true : false;
echo "Logic check:\n";
echo "  password provided: " . (!empty($testData2['password']) ? 'YES' : 'NO') . "\n";
echo "  usedDefaultPassword: " . ($usedDefaultPassword2 ? 'TRUE' : 'FALSE') . "\n";
echo "  force_password_change will be: " . ($usedDefaultPassword2 ? 'TRUE' : 'FALSE') . "\n";
echo "\n";

if ($usedDefaultPassword2) {
    echo "✅ OK: force_password_change will be TRUE\n";
} else {
    echo "❌ PROBLEM: force_password_change will be FALSE!\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

echo "CONCLUSION:\n";
echo "The current logic sets force_password_change based on whether\n";
echo "a custom password was provided:\n\n";
echo "  - Custom password provided → force_password_change = FALSE ❌\n";
echo "  - No password (default) → force_password_change = TRUE ✅\n\n";
echo "RECOMMENDATION:\n";
echo "For NEW employees, force_password_change should ALWAYS be TRUE\n";
echo "regardless of whether admin provided a custom password or not.\n";
echo "The employee should change it on first login for security.\n\n";
echo "SUGGESTED FIX:\n";
echo "Always set force_password_change = TRUE for new employees.\n";
echo "It will be set to FALSE only after the employee changes their password.\n";
