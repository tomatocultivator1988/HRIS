<?php
/**
 * Test Login and Redirect Flow
 * Simulates browser login to check redirect behavior
 */

echo "=== Testing Login and Redirect Flow ===\n\n";

$email = 'last@gmail.com';
$password = 'first09123456789';

echo "Testing with: $email / $password\n\n";

// Step 1: Login
echo "Step 1: Login via API\n";
echo "---------------------\n";

$loginUrl = 'http://localhost/HRIS/api/auth/login';

$loginData = [
    'email' => $email,
    'password' => $password
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $loginUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($loginData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($httpCode != 200) {
    echo "❌ Login failed with HTTP $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

$loginResponse = json_decode($response, true);

if (!$loginResponse['success']) {
    echo "❌ Login failed: " . ($loginResponse['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

echo "✅ Login successful!\n\n";

// Step 2: Check response data
echo "Step 2: Check Response Data\n";
echo "----------------------------\n";

$data = $loginResponse['data'] ?? [];
$user = $data['user'] ?? [];
$accessToken = $data['access_token'] ?? '';

echo "Access Token: " . substr($accessToken, 0, 30) . "...\n";
echo "User ID: " . ($user['id'] ?? 'N/A') . "\n";
echo "Email: " . ($user['email'] ?? 'N/A') . "\n";
echo "Role: " . ($user['role'] ?? 'N/A') . "\n";
echo "Name: " . ($user['name'] ?? 'N/A') . "\n";
echo "force_password_change: " . (isset($user['force_password_change']) && $user['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
echo "password_changed_at: " . ($user['password_changed_at'] ?? 'NULL') . "\n";

echo "\n";

// Step 3: Determine expected redirect
echo "Step 3: Determine Expected Redirect\n";
echo "------------------------------------\n";

$shouldForcePasswordChange = isset($user['force_password_change']) && $user['force_password_change'] === true;

if ($shouldForcePasswordChange) {
    $expectedRedirect = '/HRIS/password/change';
    echo "✅ Should redirect to: $expectedRedirect\n";
    echo "   Reason: force_password_change = TRUE\n";
} else {
    $expectedRedirect = $user['role'] === 'admin' ? '/HRIS/dashboard/admin' : '/HRIS/dashboard/employee';
    echo "Should redirect to: $expectedRedirect\n";
    echo "   Reason: Normal login (force_password_change = FALSE)\n";
}

echo "\n";

// Step 4: Simulate frontend logic
echo "Step 4: Simulate Frontend Logic (auth.js)\n";
echo "------------------------------------------\n";

echo "Frontend code in auth.js login() method:\n";
echo "\n";
echo "if (this.user.role === 'employee' && this.user.force_password_change) {\n";
echo "    return {\n";
echo "        success: true,\n";
echo "        user: this.user,\n";
echo "        force_password_change: true,\n";
echo "        redirectUrl: '/HRIS/password/change'\n";
echo "    };\n";
echo "}\n";
echo "\n";

$role = $user['role'] ?? '';
$forceChange = $user['force_password_change'] ?? false;

echo "Checking condition:\n";
echo "  - this.user.role === 'employee': " . ($role === 'employee' ? 'TRUE' : 'FALSE') . "\n";
echo "  - this.user.force_password_change: " . ($forceChange ? 'TRUE' : 'FALSE') . "\n";
echo "\n";

if ($role === 'employee' && $forceChange) {
    echo "✅ Condition MATCHES! Should return force_password_change redirect\n";
    echo "   redirectUrl: /HRIS/password/change\n";
} else {
    echo "❌ Condition DOES NOT MATCH! Will use normal redirect\n";
    echo "   redirectUrl: " . ($role === 'admin' ? '/HRIS/dashboard/admin' : '/HRIS/dashboard/employee') . "\n";
}

echo "\n";

// Step 5: Test password change page accessibility
echo "Step 5: Test Password Change Page\n";
echo "----------------------------------\n";

$changePasswordUrl = 'http://localhost/HRIS/password/change';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $changePasswordUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Testing: $changePasswordUrl\n";
echo "HTTP Code: $httpCode\n";

if ($httpCode == 200) {
    echo "✅ Password change page is accessible\n";
    
    if (stripos($response, 'Change Password') !== false) {
        echo "✅ Page contains 'Change Password' text\n";
    } else {
        echo "⚠️  Page loaded but doesn't contain expected content\n";
    }
} else {
    echo "❌ Password change page returned HTTP $httpCode\n";
}

echo "\n";

// Summary
echo "=== SUMMARY ===\n";
echo "---------------\n";

if ($shouldForcePasswordChange) {
    echo "✅ Database has force_password_change = TRUE\n";
    echo "✅ API response includes force_password_change = TRUE\n";
    
    if ($role === 'employee' && $forceChange) {
        echo "✅ Frontend condition should match\n";
        echo "✅ Should redirect to: /HRIS/password/change\n";
    } else {
        echo "❌ Frontend condition will NOT match\n";
        echo "   Problem: Role or force_password_change value incorrect\n";
    }
} else {
    echo "❌ force_password_change is FALSE or not set\n";
    echo "   This user will NOT be forced to change password\n";
}

echo "\n";
echo "To test in browser:\n";
echo "1. Open browser console (F12)\n";
echo "2. Run: localStorage.clear()\n";
echo "3. Go to: http://localhost/HRIS/login\n";
echo "4. Login with: $email / $password\n";
echo "5. Check console for 'Login result' object\n";
echo "6. Check 'redirectUrl' value\n";
echo "7. Should redirect to: $expectedRedirect\n";

echo "\n=== End of Test ===\n";
