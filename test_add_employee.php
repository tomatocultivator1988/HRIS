<?php

// Test adding employee via API

// Step 1: Login
$loginData = [
    'email' => 'admin@company.com',
    'password' => 'Admin123!'
];

$ch = curl_init('http://localhost/HRIS/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$loginResponse = curl_exec($ch);
$loginResult = json_decode($loginResponse, true);

echo "Login Response:\n";
print_r($loginResult);
echo "\n";

if (!$loginResult['success']) {
    die("Login failed: " . $loginResult['message'] . "\n");
}

$token = $loginResult['data']['access_token'] ?? null;
if (!$token) {
    die("No token in response!\n");
}
echo "Login successful! Token: " . substr($token, 0, 50) . "...\n\n";

// Step 2: Add employee
$employeeData = [
    'first_name' => 'Juan',
    'last_name' => 'Dela Cruz',
    'work_email' => 'juan.delacruz' . time() . '@company.com',  // Unique email
    'phone' => '09171234567',
    'department' => 'IT',
    'position' => 'Software Developer',
    'employment_status' => 'Regular',
    'date_hired' => '2024-01-15',
    'employee_id' => 'EMP-' . rand(1000, 9999)  // Added employee_id
];

echo "Adding employee...\n";
echo "Data: " . json_encode($employeeData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init('http://localhost/HRIS/api/employees');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($employeeData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$addResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Status: $httpCode\n";
echo "Response: " . $addResponse . "\n";

$addResult = json_decode($addResponse, true);

if ($addResult && $addResult['success']) {
    echo "\n✅ Employee added successfully!\n";
    echo "Employee ID: " . $addResult['data']['employee']['employee_id'] . "\n";
} else {
    echo "\n❌ Failed to add employee\n";
    if (isset($addResult['message'])) {
        echo "Error: " . $addResult['message'] . "\n";
    }
    if (isset($addResult['errors'])) {
        echo "Validation errors:\n";
        print_r($addResult['errors']);
    }
}

curl_close($ch);
