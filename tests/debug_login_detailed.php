<?php
/**
 * Detailed Login Debug Script
 * Check exact issue with login
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Container;

echo "=== Detailed Login Debug ===\n\n";

$email = 'last@gmail.com';
$password = 'first09123456789';

try {
    $container = Container::getInstance();
    $config = require __DIR__ . '/../config/supabase.php';
    
    echo "Step 1: Authenticate with Supabase Auth\n";
    echo "----------------------------------------\n";
    
    $url = $config['auth_url'] . 'token?grant_type=password';
    
    $data = [
        'email' => $email,
        'password' => $password
    ];
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $config['anon_key']
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $authData = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300 && isset($authData['access_token'])) {
        echo "✅ Supabase Auth SUCCESS\n";
        echo "   - HTTP Code: $httpCode\n";
        echo "   - User ID: " . $authData['user']['id'] . "\n";
        echo "   - Email: " . $authData['user']['email'] . "\n";
        echo "   - Access Token: " . substr($authData['access_token'], 0, 30) . "...\n\n";
        
        $supabaseUserId = $authData['user']['id'];
        
        echo "Step 2: Query employees table for user\n";
        echo "----------------------------------------\n";
        
        // Query employees table
        $employeeUrl = $config['api_url'] . 'employees?supabase_user_id=eq.' . $supabaseUserId . '&is_active=eq.true';
        
        echo "Query URL: $employeeUrl\n\n";
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $config['service_key'],
            'Authorization: Bearer ' . $config['service_key']
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $employeeUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "Response HTTP Code: $httpCode\n";
        if ($error) {
            echo "cURL Error: $error\n";
        }
        echo "Response Body: $response\n\n";
        
        $employeeData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300 && !empty($employeeData)) {
            echo "✅ Employee found in database!\n";
            echo "   - ID: " . $employeeData[0]['id'] . "\n";
            echo "   - Employee ID: " . $employeeData[0]['employee_id'] . "\n";
            echo "   - Name: " . $employeeData[0]['first_name'] . " " . $employeeData[0]['last_name'] . "\n";
            echo "   - Email: " . $employeeData[0]['work_email'] . "\n";
            echo "   - Supabase User ID: " . $employeeData[0]['supabase_user_id'] . "\n";
            echo "   - Active: " . ($employeeData[0]['is_active'] ? 'Yes' : 'No') . "\n\n";
            
            echo "=== DIAGNOSIS ===\n";
            echo "✅ Everything looks correct! Login should work.\n";
            echo "If login still fails, check:\n";
            echo "1. Browser console for JavaScript errors\n";
            echo "2. Network tab for API request/response\n";
            echo "3. PHP error logs\n";
            
        } else {
            echo "❌ Employee NOT found in database!\n\n";
            
            echo "=== DIAGNOSIS ===\n";
            echo "PROBLEM: Supabase User ID mismatch\n\n";
            echo "Supabase Auth User ID: $supabaseUserId\n";
            echo "This ID is not found in employees.supabase_user_id column\n\n";
            
            echo "SOLUTION: Update the employee record with correct Supabase User ID\n";
            echo "Run this SQL in Supabase:\n\n";
            echo "UPDATE employees\n";
            echo "SET supabase_user_id = '$supabaseUserId'\n";
            echo "WHERE work_email = '$email';\n\n";
        }
        
    } else {
        echo "❌ Supabase Auth FAILED\n";
        echo "   - HTTP Code: $httpCode\n";
        echo "   - Response: $response\n\n";
        
        echo "=== DIAGNOSIS ===\n";
        echo "PROBLEM: Authentication with Supabase failed\n";
        echo "Possible causes:\n";
        echo "1. Wrong email or password\n";
        echo "2. User doesn't exist in Supabase Auth\n";
        echo "3. User is not confirmed/active in Supabase\n";
        echo "4. Supabase configuration issue\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== End of Debug ===\n";
