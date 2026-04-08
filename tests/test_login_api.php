<?php
/**
 * Test Login API Endpoint
 */

$baseUrl = 'http://localhost/HRIS';
$apiUrl = $baseUrl . '/api/auth/login';

$data = [
    'email' => 'last@gmail.com',
    'password' => 'first09123456789'
];

echo "Testing Login API Endpoint\n";
echo "==========================\n\n";
echo "URL: $apiUrl\n";
echo "Email: " . $data['email'] . "\n";
echo "Password: " . $data['password'] . "\n\n";

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "Response:\n";
echo "---------\n";
echo "HTTP Code: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
}

echo "\nResponse Body:\n";
echo $response . "\n\n";

$responseData = json_decode($response, true);

if ($responseData && isset($responseData['success'])) {
    if ($responseData['success']) {
        echo "✅ LOGIN SUCCESS!\n";
        echo "   - Access Token: " . substr($responseData['data']['access_token'] ?? '', 0, 30) . "...\n";
        echo "   - User ID: " . ($responseData['data']['user']['id'] ?? 'N/A') . "\n";
        echo "   - Role: " . ($responseData['data']['user']['role'] ?? 'N/A') . "\n";
        echo "   - Name: " . ($responseData['data']['user']['name'] ?? 'N/A') . "\n";
        echo "   - Force Password Change: " . (($responseData['data']['user']['force_password_change'] ?? false) ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ LOGIN FAILED!\n";
        echo "   - Message: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Invalid response format\n";
}
