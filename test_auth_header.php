<?php
/**
 * Test script to check if Authorization header is being received
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$debug = [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'headers' => [],
    'server_vars' => []
];

// Check all possible Authorization header locations
$authSources = [
    'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? null,
    'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null,
];

// Try apache_request_headers if available
if (function_exists('apache_request_headers')) {
    $apacheHeaders = apache_request_headers();
    $authSources['apache_request_headers'] = $apacheHeaders['Authorization'] ?? $apacheHeaders['authorization'] ?? null;
}

$debug['auth_sources'] = $authSources;

// Get all HTTP headers from $_SERVER
foreach ($_SERVER as $key => $value) {
    if (str_starts_with($key, 'HTTP_')) {
        $headerName = str_replace('_', '-', substr($key, 5));
        $debug['headers'][$headerName] = $value;
    }
}

// Add some relevant $_SERVER vars
$relevantVars = ['REQUEST_METHOD', 'REQUEST_URI', 'SERVER_PROTOCOL', 'REMOTE_ADDR', 'HTTP_HOST'];
foreach ($relevantVars as $var) {
    if (isset($_SERVER[$var])) {
        $debug['server_vars'][$var] = $_SERVER[$var];
    }
}

echo json_encode($debug, JSON_PRETTY_PRINT);
