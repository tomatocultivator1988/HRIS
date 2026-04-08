<?php
/**
 * Test Force Password Change Flow
 * Step by step testing
 */

echo "=== Testing Force Password Change Flow ===\n\n";

// Step 1: Check database
echo "Step 1: Check database for force_password_change flag\n";
echo "-----------------------------------------------------\n";

$config = require __DIR__ . '/../config/supabase.php';
$email = 'last@gmail.com';

$url = $config['api_url'] . 'employees?work_email=eq.' . urlencode($email);

$headers = [
    'Content-Type: application/json',
    'apikey: ' . $config['service_key'],
    'Authorization: Bearer ' . $config['service_key']
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
curl_close($ch);

$employees = json_decode($response, true);

if (!empty($employees)) {
    $employee = $employees[0];
    echo "✅ Employee found in database\n";
    echo "   - Name: {$employee['first_name']} {$employee['last_name']}\n";
    echo "   - Email: {$employee['work_email']}\n";
    echo "   - force_password_change: " . ($employee['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
    
    if (!$employee['force_password_change']) {
        echo "\n❌ PROBLEM: force_password_change is FALSE in database!\n";
        echo "   Run this to fix:\n";
        echo "   C:\\xampp\\php\\php.exe tests/set_force_password_change.php\n";
    }
} else {
    echo "❌ Employee not found in database\n";
}

echo "\n";

// Step 2: Test login API
echo "Step 2: Test login API response\n";
echo "--------------------------------\n";

$loginUrl = 'http://localhost/HRIS/api/auth/login';

$loginData = [
    'email' => $email,
    'password' => 'first09123456789'
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

if ($httpCode == 200) {
    $loginResponse = json_decode($response, true);
    
    if ($loginResponse['success']) {
        echo "✅ Login successful\n";
        
        $user = $loginResponse['data']['user'] ?? null;
        
        if ($user) {
            echo "   - User ID: {$user['id']}\n";
            echo "   - Role: {$user['role']}\n";
            echo "   - force_password_change: " . (isset($user['force_password_change']) && $user['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
            
            if (!isset($user['force_password_change']) || !$user['force_password_change']) {
                echo "\n❌ PROBLEM: force_password_change is not TRUE in API response!\n";
                echo "   Check AuthController line 85-95\n";
            } else {
                echo "\n✅ API response includes force_password_change = TRUE\n";
            }
        } else {
            echo "❌ No user data in response\n";
        }
    } else {
        echo "❌ Login failed: " . ($loginResponse['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ HTTP Error: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n";

// Step 3: Check route
echo "Step 3: Check if /password/change route exists\n";
echo "-----------------------------------------------\n";

$changePasswordUrl = 'http://localhost/HRIS/password/change';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $changePasswordUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($httpCode == 200) {
    echo "✅ Change password page is accessible\n";
    
    if (stripos($response, 'Change Password') !== false) {
        echo "✅ Page contains 'Change Password' text\n";
    } else {
        echo "⚠️  Page loaded but doesn't contain expected content\n";
    }
} else if ($httpCode == 302 || $httpCode == 301) {
    echo "⚠️  Page redirects (might need authentication)\n";
} else {
    echo "❌ Page not accessible\n";
}

echo "\n";

// Step 4: Summary
echo "=== SUMMARY ===\n";
echo "---------------\n";

if (!empty($employees) && $employees[0]['force_password_change']) {
    echo "✅ Database: force_password_change = TRUE\n";
} else {
    echo "❌ Database: force_password_change = FALSE or not set\n";
}

if ($httpCode == 200 && isset($loginResponse['success']) && $loginResponse['success']) {
    $user = $loginResponse['data']['user'] ?? null;
    if ($user && isset($user['force_password_change']) && $user['force_password_change']) {
        echo "✅ API Response: force_password_change = TRUE\n";
    } else {
        echo "❌ API Response: force_password_change = FALSE or not set\n";
    }
}

echo "\n";
echo "Next steps:\n";
echo "1. Clear browser localStorage: localStorage.clear()\n";
echo "2. Open browser console (F12)\n";
echo "3. Login with: last@gmail.com / first09123456789\n";
echo "4. Check console for 'Login result' and 'Redirect URL'\n";
echo "5. Should redirect to: /HRIS/password/change\n";

echo "\n=== End of Test ===\n";
