<?php
/**
 * Test Force Password Change on Login
 */

$url = 'http://localhost/HRIS/api/auth/login';
$data = [
    'email' => 'test@gmail.com',
    'password' => 'Test123!' // Assuming this is the password
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== TEST FORCE PASSWORD CHANGE ON LOGIN ===\n\n";
echo "Email: test@gmail.com\n";
echo "HTTP Code: $httpCode\n\n";

$decoded = json_decode($response, true);

if ($decoded && $decoded['success']) {
    echo "✅ Login successful!\n\n";
    
    $user = $decoded['data']['user'] ?? [];
    
    echo "User Data:\n";
    echo "  Name: " . $user['name'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    echo "  Force Password Change: " . (isset($user['force_password_change']) ? ($user['force_password_change'] ? 'TRUE ✅' : 'FALSE') : 'NOT SET') . "\n";
    echo "  Password Changed At: " . ($user['password_changed_at'] ?? 'NULL') . "\n";
    
    if (isset($decoded['data']['force_password_change']) && $decoded['data']['force_password_change']) {
        echo "\n✅ FORCE PASSWORD CHANGE FLAG DETECTED IN RESPONSE!\n";
        echo "Redirect URL: " . ($decoded['data']['redirectUrl'] ?? 'N/A') . "\n";
    } else {
        echo "\n⚠️ No force password change flag in response\n";
    }
    
} else {
    echo "❌ Login failed\n";
    echo "Message: " . ($decoded['message'] ?? 'Unknown error') . "\n";
}

echo "\nRaw Response:\n";
echo $response . "\n";
