<?php
/**
 * Set force_password_change for pass@gmail.com
 */

$config = require __DIR__ . '/../config/supabase.php';
$email = 'pass@gmail.com';

echo "Setting force_password_change for: $email\n\n";

// Get employee
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
    CURLOPT_HTTPHEADER => $headers
]);

$response = curl_exec($ch);
curl_close($ch);

$employees = json_decode($response, true);

if (!empty($employees)) {
    $employee = $employees[0];
    echo "Employee: {$employee['first_name']} {$employee['last_name']}\n";
    echo "Current force_password_change: " . ($employee['force_password_change'] ? 'TRUE' : 'FALSE') . "\n\n";
    
    // Update to TRUE
    $updateUrl = $config['api_url'] . 'employees?id=eq.' . $employee['id'];
    
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
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ Successfully set force_password_change = TRUE\n";
    } else {
        echo "❌ Failed to update: HTTP $httpCode\n";
        echo "Response: $response\n";
    }
} else {
    echo "❌ Employee not found\n";
}
