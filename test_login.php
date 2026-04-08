<?php
/**
 * Login Test Script
 * Tests the login functionality with admin credentials
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Test Script</h1>";
echo "<pre>";

// Define application root
define('APP_ROOT', __DIR__);

// Load framework bootstrap
require_once APP_ROOT . '/src/bootstrap.php';

use Core\Request;
use Core\Response;
use Controllers\AuthController;

try {
    echo "=== Testing Login Functionality ===\n\n";
    
    // Create mock request with login credentials
    $loginData = [
        'email' => 'admin@company.com',
        'password' => 'Admin123!'
    ];
    
    echo "Test Credentials:\n";
    echo "Email: " . $loginData['email'] . "\n";
    echo "Password: " . $loginData['password'] . "\n\n";
    
    // Set POST data BEFORE creating Request object
    $_POST = $loginData;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    
    echo "=== Creating Request Object ===\n";
    // Create request object (will read $_POST in constructor)
    $request = new Request();
    
    echo "Request Method: " . $request->getMethod() . "\n";
    echo "POST Data: " . json_encode($request->getPostData()) . "\n\n";
    
    echo "=== Calling AuthController::login() ===\n\n";
    
    // Get container and resolve AuthController
    $container = \Core\Container::getInstance();
    $authController = $container->resolve(AuthController::class);
    
    // Inject request
    $authController->setRequest($request);
    
    // Call login method
    $response = $authController->login($request);
    
    echo "=== Response Details ===\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content Type: " . $response->getHeader('Content-Type') . "\n\n";
    
    echo "Response Body:\n";
    $responseContent = $response->getContent();
    echo $responseContent . "\n\n";
    
    // Try to decode JSON response
    $responseData = json_decode($responseContent, true);
    
    if ($responseData) {
        echo "=== Parsed Response ===\n";
        echo "Success: " . ($responseData['success'] ? 'YES' : 'NO') . "\n";
        
        if (isset($responseData['message'])) {
            echo "Message: " . $responseData['message'] . "\n";
        }
        
        if (isset($responseData['data'])) {
            echo "\nData Keys: " . implode(', ', array_keys($responseData['data'])) . "\n";
            
            if (isset($responseData['data']['access_token'])) {
                echo "Access Token: " . substr($responseData['data']['access_token'], 0, 50) . "...\n";
            }
            
            if (isset($responseData['data']['user'])) {
                echo "\nUser Info:\n";
                echo "  ID: " . ($responseData['data']['user']['id'] ?? 'N/A') . "\n";
                echo "  Email: " . ($responseData['data']['user']['email'] ?? 'N/A') . "\n";
                echo "  Role: " . ($responseData['data']['user']['role'] ?? 'N/A') . "\n";
                echo "  First Name: " . ($responseData['data']['user']['first_name'] ?? 'N/A') . "\n";
                echo "  Last Name: " . ($responseData['data']['user']['last_name'] ?? 'N/A') . "\n";
            }
        }
        
        if (isset($responseData['error'])) {
            echo "\nError: " . $responseData['error'] . "\n";
        }
        
        echo "\n=== Test Result ===\n";
        if ($responseData['success']) {
            echo "✅ LOGIN SUCCESSFUL!\n";
            echo "The login functionality is working correctly.\n";
            echo "Token and user data are being returned.\n";
        } else {
            echo "❌ LOGIN FAILED!\n";
            echo "Reason: " . ($responseData['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ Could not parse JSON response\n";
        echo "Raw response: " . $responseContent . "\n";
    }
    
} catch (Throwable $e) {
    echo "\n❌ EXCEPTION CAUGHT:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "</pre>";
