<?php
/**
 * Direct Login Test
 */

$url = 'http://localhost/HRIS/api/auth/login';
$data = [
    'email' => 'admin@company.com',
    'password' => 'admin123'
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
$error = curl_error($ch);

curl_close($ch);

echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";
echo "Error: " . ($error ?: 'None') . "\n";
echo "Response:\n";
echo $response . "\n";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "\nDecoded:\n";
    print_r($decoded);
}
