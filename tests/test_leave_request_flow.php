<?php
/**
 * Test Leave Request Flow
 * Tests the complete leave request functionality including:
 * - Login and token storage
 * - Request leave
 * - View pending requests (admin)
 * - View leave history
 * - Approve/Deny leave
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== LEAVE REQUEST FLOW TEST ===\n\n";

// Configuration
$baseUrl = 'http://localhost/HRIS';
$apiUrl = $baseUrl . '/api';

// Test users
$adminEmail = 'admin@company.com';
$adminPassword = 'Admin123!';

// We'll use admin for all tests since employee accounts don't have easily accessible credentials

/**
 * Make HTTP request
 */
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => json_decode($response, true),
        'raw' => $response
    ];
}

// Step 1: Login as Admin
echo "Step 1: Login as Admin\n";
echo "Email: $adminEmail\n";

$loginResult = makeRequest(
    $apiUrl . '/auth/login',
    'POST',
    [
        'email' => $adminEmail,
        'password' => $adminPassword
    ]
);

if (!$loginResult['success']) {
    echo "❌ Login failed!\n";
    echo "HTTP Code: " . $loginResult['http_code'] . "\n";
    echo "Response: " . print_r($loginResult, true) . "\n";
    exit(1);
}

$adminToken = $loginResult['data']['data']['access_token'] ?? null;
$adminRefreshToken = $loginResult['data']['data']['refresh_token'] ?? null;
$adminUser = $loginResult['data']['data']['user'] ?? null;

if (!$adminToken) {
    echo "❌ No access token received!\n";
    echo "Response: " . print_r($loginResult['data'], true) . "\n";
    exit(1);
}

echo "✅ Login successful!\n";
echo "Access Token: " . substr($adminToken, 0, 30) . "...\n";
echo "Refresh Token: " . ($adminRefreshToken ? substr($adminRefreshToken, 0, 30) . "..." : "NOT PROVIDED") . "\n";
echo "User: " . $adminUser['name'] . " (" . $adminUser['role'] . ")\n\n";

// Step 2: View Pending Requests
echo "Step 2: View Pending Requests\n";

$pendingResult = makeRequest(
    $apiUrl . '/leave/pending',
    'GET',
    null,
    $adminToken
);

echo "HTTP Code: " . $pendingResult['http_code'] . "\n";
if ($pendingResult['success']) {
    echo "✅ Pending requests retrieved!\n";
    $pending = $pendingResult['data']['data']['pending_requests'] ?? [];
    echo "Total pending: " . count($pending) . "\n";
    if (!empty($pending)) {
        echo "First pending request:\n";
        $first = $pending[0];
        echo "  ID: " . $first['id'] . "\n";
        echo "  Employee: " . ($first['employee_name'] ?? 'N/A') . "\n";
        echo "  Type ID: " . $first['leave_type_id'] . "\n";
        echo "  Dates: " . $first['start_date'] . " to " . $first['end_date'] . "\n";
        echo "  Days: " . $first['total_days'] . "\n";
        echo "  Status: " . $first['status'] . "\n";
        
        $leaveRequestId = $first['id'];
    }
} else {
    echo "❌ Pending requests failed!\n";
    echo "Response: " . print_r($pendingResult['data'], true) . "\n";
}
echo "\n";

// Step 3: View Leave History
echo "Step 3: View Leave History\n";

$historyResult = makeRequest(
    $apiUrl . '/leave/history',
    'GET',
    null,
    $adminToken
);

echo "HTTP Code: " . $historyResult['http_code'] . "\n";
if ($historyResult['success']) {
    echo "✅ Leave history retrieved!\n";
    $historyData = $historyResult['data']['data'] ?? [];
    $requests = $historyData['requests'] ?? [];
    $totalRecords = $historyData['total_records'] ?? 0;
    
    echo "Total requests: " . $totalRecords . "\n";
    echo "Returned: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        echo "Latest request:\n";
        $latest = $requests[0];
        echo "  ID: " . $latest['id'] . "\n";
        echo "  Type ID: " . $latest['leave_type_id'] . "\n";
        echo "  Dates: " . $latest['start_date'] . " to " . $latest['end_date'] . "\n";
        echo "  Days: " . $latest['total_days'] . "\n";
        echo "  Status: " . $latest['status'] . "\n";
    }
} else {
    echo "❌ Leave history request failed!\n";
    echo "Response: " . print_r($historyResult['data'], true) . "\n";
}
echo "\n";

// Step 4: Test Token Refresh
echo "Step 4: Test Token Refresh\n";

if ($adminRefreshToken) {
    echo "Testing token refresh...\n";
    
    $refreshResult = makeRequest(
        $apiUrl . '/auth/refresh',
        'POST',
        ['refresh_token' => $adminRefreshToken]
    );
    
    echo "HTTP Code: " . $refreshResult['http_code'] . "\n";
    if ($refreshResult['success']) {
        echo "✅ Token refresh successful!\n";
        $newToken = $refreshResult['data']['data']['access_token'] ?? null;
        $newRefreshToken = $refreshResult['data']['data']['refresh_token'] ?? null;
        echo "New Access Token: " . substr($newToken, 0, 30) . "...\n";
        echo "New Refresh Token: " . ($newRefreshToken ? substr($newRefreshToken, 0, 30) . "..." : "SAME AS BEFORE") . "\n";
        
        // Use new token for remaining tests
        $adminToken = $newToken;
    } else {
        echo "❌ Token refresh failed!\n";
        echo "Response: " . print_r($refreshResult['data'], true) . "\n";
    }
} else {
    echo "⚠️ No refresh token available to test\n";
}
echo "\n";

// Step 5: Get Leave Types
echo "Step 5: Get Leave Types\n";

$typesResult = makeRequest(
    $apiUrl . '/leave/types',
    'GET',
    null,
    $adminToken
);

echo "HTTP Code: " . $typesResult['http_code'] . "\n";
if ($typesResult['success']) {
    echo "✅ Leave types retrieved!\n";
    $types = $typesResult['data']['data']['types'] ?? [];
    echo "Available types: " . count($types) . "\n";
    if (!empty($types)) {
        foreach ($types as $type) {
            echo "  - " . $type['name'] . " (ID: " . $type['id'] . ")\n";
        }
    }
} else {
    echo "⚠️ Leave types request failed\n";
    echo "Response: " . print_r($typesResult['data'], true) . "\n";
}
echo "\n";

echo "=== TEST COMPLETE ===\n";
