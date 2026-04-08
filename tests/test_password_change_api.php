<?php
/**
 * Test script to simulate password change API call
 * Usage: php tests/test_password_change_api.php
 */

echo "=== Test Password Change API ===\n\n";

$email = 'last@gmail.com';
$currentPassword = 'TestPass456!'; // Updated from last test
$newPassword = 'FinalPass789!';

// Step 1: Login to get access token
echo "Step 1: Logging in to get access token...\n";

$loginUrl = 'http://localhost/HRIS/api/auth/login';
$loginData = [
    'email' => $email,
    'password' => $currentPassword
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

echo "Login Response Code: $loginHttpCode\n";

if ($loginHttpCode !== 200) {
    echo "❌ Login failed!\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

$loginResult = json_decode($loginResponse, true);

echo "Login Result:\n";
print_r($loginResult);
echo "\n";

if (!$loginResult['success']) {
    echo "❌ Login failed: " . ($loginResult['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

$accessToken = $loginResult['access_token'] ?? $loginResult['data']['access_token'] ?? null;
$user = $loginResult['user'] ?? $loginResult['data']['user'] ?? null;

if (!$accessToken) {
    echo "❌ No access token received!\n";
    exit(1);
}

echo "✅ Login successful!\n";
echo "   User: {$user['name']}\n";
echo "   Role: {$user['role']}\n";
echo "   Force Password Change: " . (($user['force_password_change'] ?? false) ? 'TRUE' : 'FALSE') . "\n";
echo "   Access Token: " . substr($accessToken, 0, 30) . "...\n\n";

// Step 2: Change password
echo "Step 2: Changing password...\n";

$changePasswordUrl = 'http://localhost/HRIS/api/password/change';
$changePasswordData = [
    'current_password' => $currentPassword,
    'new_password' => $newPassword,
    'confirm_password' => $newPassword
];

$ch = curl_init($changePasswordUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($changePasswordData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$changeResponse = curl_exec($ch);
$changeHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "Change Password Response Code: $changeHttpCode\n";

if ($curlError) {
    echo "❌ cURL Error: $curlError\n";
    exit(1);
}

if ($changeHttpCode !== 200) {
    echo "❌ Password change failed!\n";
    echo "Response: $changeResponse\n";
    
    // Try to decode error response
    $errorResult = json_decode($changeResponse, true);
    if ($errorResult) {
        echo "Error Message: " . ($errorResult['message'] ?? 'Unknown error') . "\n";
    }
    exit(1);
}

$changeResult = json_decode($changeResponse, true);

if (!$changeResult) {
    echo "❌ Failed to parse response!\n";
    echo "Raw Response: $changeResponse\n";
    exit(1);
}

if (!$changeResult['success']) {
    echo "❌ Password change failed: " . ($changeResult['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "✅ Password changed successfully!\n";
echo "   Message: " . ($changeResult['message'] ?? 'Success') . "\n\n";

// Step 3: Verify new password by logging in again
echo "Step 3: Verifying new password...\n";

$verifyLoginData = [
    'email' => $email,
    'password' => $newPassword
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verifyLoginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$verifyResponse = curl_exec($ch);
$verifyHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Verify Login Response Code: $verifyHttpCode\n";

if ($verifyHttpCode !== 200) {
    echo "❌ Verification login failed!\n";
    echo "Response: $verifyResponse\n";
    exit(1);
}

$verifyResult = json_decode($verifyResponse, true);

if (!$verifyResult['success']) {
    echo "❌ Verification login failed: " . ($verifyResult['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "✅ New password verified successfully!\n";
echo "   User can now login with new password: $newPassword\n\n";

// Step 4: Check if force_password_change flag was cleared
$verifyUser = $verifyResult['user'] ?? null;
if ($verifyUser) {
    $forceChange = $verifyUser['force_password_change'] ?? false;
    echo "Force Password Change after update: " . ($forceChange ? 'TRUE' : 'FALSE') . "\n";
    
    if ($forceChange) {
        echo "⚠️  Warning: force_password_change flag is still TRUE\n";
    } else {
        echo "✅ force_password_change flag cleared successfully\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "✅ All tests passed!\n";
echo "\nYou can now login with:\n";
echo "  Email: $email\n";
echo "  Password: $newPassword\n";
