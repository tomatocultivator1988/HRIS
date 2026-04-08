<?php
/**
 * Check what employee_id the login returns
 */

$baseUrl = 'http://localhost/HRIS';
$testEmail = 'last@gmail.com';
$testPassword = 'FinalPass789!';

echo "=== Checking Login Employee ID ===\n\n";

$loginUrl = "$baseUrl/api/auth/login";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode !== 200) {
    echo "❌ Login failed! HTTP Code: $loginHttpCode\n";
    exit(1);
}

$loginResult = json_decode($loginResponse, true);
$user = $loginResult['data']['user'] ?? null;

echo "Login Response:\n";
print_r($loginResult);
echo "\n";

if ($user) {
    echo "User Data:\n";
    echo "   ID: {$user['id']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Name: {$user['name']}\n";
    echo "   Role: {$user['role']}\n";
    echo "   Employee ID: " . ($user['employee_id'] ?? 'NULL') . "\n";
}

// Now check if this employee_id exists in employees table
if (!empty($user['employee_id'])) {
    require_once __DIR__ . '/../src/autoload.php';
    
    $config = require __DIR__ . '/../config/supabase.php';
    $db = new \Core\SupabaseConnection($config);
    
    echo "\nChecking employee record...\n";
    $employee = $db->find('employees', $user['employee_id']);
    
    if ($employee) {
        echo "✅ Employee record exists:\n";
        print_r($employee);
    } else {
        echo "❌ Employee record NOT found!\n";
    }
}

echo "\n=== Check Complete ===\n";
