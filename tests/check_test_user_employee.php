<?php
/**
 * Check test user's employee record
 */

require_once __DIR__ . '/../src/autoload.php';

use Core\SupabaseConnection;

$config = require __DIR__ . '/../config/supabase.php';
$db = new SupabaseConnection($config);

$testEmail = 'last@gmail.com';

echo "=== Checking Test User Employee Record ===\n\n";

// Get user from auth.users
echo "Step 1: Getting user from auth.users...\n";
$users = $db->select('users', ['email' => $testEmail]);

if (empty($users)) {
    echo "❌ User not found!\n";
    exit(1);
}

$user = $users[0];
echo "✅ User found:\n";
echo "   ID: {$user['id']}\n";
echo "   Email: {$user['email']}\n";
echo "   Employee ID: " . ($user['employee_id'] ?? 'NULL') . "\n";
echo "\n";

// Check if employee record exists
if (!empty($user['employee_id'])) {
    echo "Step 2: Checking employee record...\n";
    $employee = $db->find('employees', $user['employee_id']);
    
    if ($employee) {
        echo "✅ Employee record found:\n";
        echo "   ID: {$employee['id']}\n";
        echo "   Name: {$employee['first_name']} {$employee['last_name']}\n";
        echo "   Employee Number: {$employee['employee_id']}\n";
        echo "   Is Active: " . ($employee['is_active'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Employee record NOT found for ID: {$user['employee_id']}\n";
        echo "   This is the problem! The user has an employee_id but no matching employee record.\n";
    }
} else {
    echo "❌ User has no employee_id set!\n";
}

echo "\n=== Check Complete ===\n";
