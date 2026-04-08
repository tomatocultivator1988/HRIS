<?php
/**
 * Set force_password_change for existing employees with default passwords
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Container;

echo "=== Set Force Password Change for Existing Employees ===\n\n";

try {
    $container = Container::getInstance();
    $config = require __DIR__ . '/../config/supabase.php';
    
    // Employees to update (those using default passwords)
    $employeesToUpdate = [
        'last@gmail.com'  // Add more emails here if needed
    ];
    
    foreach ($employeesToUpdate as $email) {
        echo "Processing: $email\n";
        
        // Get employee by email
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $employees = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300 && !empty($employees)) {
            $employee = $employees[0];
            $employeeId = $employee['id'];
            
            echo "  - Found employee: {$employee['first_name']} {$employee['last_name']}\n";
            echo "  - Employee ID: {$employee['employee_id']}\n";
            echo "  - Current force_password_change: " . ($employee['force_password_change'] ? 'true' : 'false') . "\n";
            
            // Update force_password_change to true
            $updateUrl = $config['api_url'] . 'employees?id=eq.' . $employeeId;
            
            $updateData = [
                'force_password_change' => true
            ];
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $updateUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS => json_encode($updateData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'apikey: ' . $config['service_key'],
                    'Authorization: Bearer ' . $config['service_key'],
                    'Prefer: return=representation'
                ],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                echo "  ✅ Successfully set force_password_change = true\n";
            } else {
                echo "  ❌ Failed to update: HTTP $httpCode\n";
                echo "  Response: $response\n";
            }
        } else {
            echo "  ❌ Employee not found\n";
        }
        
        echo "\n";
    }
    
    echo "=== Done ===\n";
    echo "\nNow when these employees log in, they will be forced to change their password.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
