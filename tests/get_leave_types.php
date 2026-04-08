<?php

// Get leave types from Supabase
require_once __DIR__ . '/../config/supabase.php';

echo "=== GET LEAVE TYPES ===\n\n";

$url = 'https://xtfekjcusnnadfgcrzht.supabase.co/rest/v1/leave_types';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inh0ZmVramN1c25uYWRmZ2Nyemh0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTM3NjIyMSwiZXhwIjoyMDkwOTUyMjIxfQ.EQnmstpF-wEKSMBEKBcwvCwtorbKNUQ6L86Alw_TP2I';

$headers = [
    'Content-Type: application/json',
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";
$decoded = json_decode($response, true);
print_r($decoded);

echo "\n=== LEAVE TYPE MAPPING ===\n";
if (is_array($decoded)) {
    foreach ($decoded as $type) {
        echo "ID: {$type['id']} => Name: {$type['name']}\n";
    }
}

echo "\n=== COMPLETE ===\n";
