<?php
/**
 * Test Token Verification
 */

// First, login to get a token
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/HRIS/api/auth/login';
$_SERVER['SCRIPT_NAME'] = '/HRIS/public/index.php';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Set POST data
$_POST = [
    'email' => 'admin@company.com',
    'password' => 'Admin123!'
];

// Include bootstrap
require_once __DIR__ . '/src/bootstrap.php';

echo "=== Step 1: Login ===\n";

$request = new Core\Request();
$authController = new Controllers\AuthController($container);
$authController->setRequest($request);

$loginResponse = $authController->login($request);
$loginData = json_decode($loginResponse->getContent(), true);

if (!$loginData['success']) {
    echo "❌ Login failed: " . $loginData['message'] . "\n";
    exit(1);
}

$token = $loginData['data']['access_token'];
$user = $loginData['data']['user'];

echo "✅ Login successful\n";
echo "Token: " . substr($token, 0, 50) . "...\n";
echo "User: " . $user['email'] . " (Role: " . $user['role'] . ")\n\n";

// Now test token verification
echo "=== Step 2: Verify Token ===\n";

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/HRIS/api/auth/verify';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

// Create new request with auth header
$verifyRequest = new Core\Request();

$verifyResponse = $authController->verify($verifyRequest);
$verifyData = json_decode($verifyResponse->getContent(), true);

echo "Response Status: " . $verifyResponse->getStatusCode() . "\n";
echo "Response:\n" . json_encode($verifyData, JSON_PRETTY_PRINT) . "\n\n";

if ($verifyData['success']) {
    echo "✅ Token verification successful\n";
    if (isset($verifyData['data']['user'])) {
        echo "User from verify: " . $verifyData['data']['user']['email'] . "\n";
    }
} else {
    echo "❌ Token verification failed: " . $verifyData['message'] . "\n";
}

echo "\n=== Test Complete ===\n";
