<?php

// Simple test to check Supabase insert directly
require_once __DIR__ . '/../config/supabase.php';

echo "=== SUPABASE INSERT TEST ===\n\n";

$testData = [
    'employee_id' => 'bdaa7c81-f553-491a-b0af-aeaff82987c7', // Kian's ID
    'leave_type_id' => '2',
    'start_date' => '2026-04-21',
    'end_date' => '2026-04-25',
    'total_days' => 5,
    'reason' => 'Test leave request from direct script',
    'status' => 'Pending',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

echo "Data to insert:\n";
print_r($testData);
echo "\n";

// Make direct request to Supabase
$url = 'https://xtfekjcusnnadfgcrzht.supabase.co/rest/v1/leave_requests';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTM3NjIyMSwiZXhwIjoyMDkwOTUyMjIxfQ.EQnmstpF-wEKSMBEKBcwvCwtorbKNUQ6L86Alw_TP2I';

$headers = [
    'Content-Type: application/json',
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Prefer: return=representation'
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_VERBOSE => true
]);

echo "Making request to: $url\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "cURL Error: " . ($error ?: 'None') . "\n";
echo "Response:\n";
echo $response . "\n\n";

$decoded = json_decode($response, true);
echo "Decoded Response:\n";
print_r($decoded);
echo "\n";

// Now try to query the record
echo "\n=== QUERY TEST ===\n";
$queryUrl = 'https://xtfekjcusnnadfgcrzht.supabase.co/rest/v1/leave_requests?employee_id=eq.bdaa7c81-f553-491a-b0af-aeaff82987c7&start_date=eq.2026-04-21';

$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => $queryUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true
]);

$queryResponse = curl_exec($ch2);
$queryHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "Query HTTP Status Code: $queryHttpCode\n";
echo "Query Response:\n";
echo $queryResponse . "\n";

echo "\n=== TEST COMPLETE ===\n";
