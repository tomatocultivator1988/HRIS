<?php
/**
 * Test script for profile API endpoint
 * 
 * INSTRUCTIONS:
 * 1. Open browser console on any HRIS page where you're logged in
 * 2. Type: localStorage.getItem('access_token')
 * 3. Copy the token (without quotes)
 * 4. Paste it in the form below
 */

$token = $_POST['token'] ?? $_GET['token'] ?? null;

if (!$token) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Profile API Test</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; }
            button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
            button:hover { background: #45a049; }
            .instructions { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <h1>Profile API Test</h1>
        
        <div class="instructions">
            <h3>Instructions:</h3>
            <ol>
                <li>Open browser console (F12) on any HRIS page where you're logged in</li>
                <li>Type: <code>localStorage.getItem('access_token')</code></li>
                <li>Copy the token (without quotes)</li>
                <li>Paste it in the form below</li>
            </ol>
        </div>
        
        <form method="POST">
            <label><strong>Access Token:</strong></label>
            <input type="text" name="token" placeholder="Paste your access token here" required>
            <button type="submit">Test Profile API</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Test the profile API endpoint
$url = 'http://localhost/HRIS/api/employees/profile';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
]);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile API Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Profile API Test Results</h1>
    
    <div class="info">
        <strong>Testing:</strong> <?= htmlspecialchars($url) ?><br>
        <strong>Token:</strong> <?= htmlspecialchars(substr($token, 0, 30)) ?>...
    </div>
    
    <?php
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "<p class='error'><strong>cURL Error:</strong> " . htmlspecialchars($curlError) . "</p>";
    }
    
    $statusClass = ($httpCode >= 200 && $httpCode < 300) ? 'success' : 'error';
    echo "<p class='$statusClass'><strong>HTTP Status:</strong> $httpCode</p>";
    
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode JSON
    $data = json_decode($response, true);
    if ($data) {
        echo "<h3>Decoded JSON:</h3>";
        echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
    } else {
        echo "<p><em>Response is not valid JSON</em></p>";
    }
    ?>
    
    <p><a href="?">← Test Again</a></p>
</body>
</html>
