<?php
/**
 * Debug Attendance Page Loading
 */

echo "=== Debugging Attendance Page ===\n\n";

// Step 1: Check if helpers.php exists and can be loaded
echo "Step 1: Checking helpers.php...\n";
$helpersPath = __DIR__ . '/../src/Config/helpers.php';
if (file_exists($helpersPath)) {
    echo "✅ helpers.php exists at: $helpersPath\n";
    require_once $helpersPath;
    
    if (function_exists('base_url')) {
        echo "✅ base_url() function is available\n";
        echo "   Test: base_url('/test') = " . base_url('/test') . "\n";
    } else {
        echo "❌ base_url() function NOT available after loading helpers.php\n";
    }
} else {
    echo "❌ helpers.php NOT found at: $helpersPath\n";
}
echo "\n";

// Step 2: Check if attendance view exists
echo "Step 2: Checking attendance view...\n";
$viewPath = __DIR__ . '/../src/Views/attendance/index.php';
if (file_exists($viewPath)) {
    echo "✅ Attendance view exists at: $viewPath\n";
} else {
    echo "❌ Attendance view NOT found at: $viewPath\n";
}
echo "\n";

// Step 3: Try to render the view
echo "Step 3: Attempting to render view...\n";
try {
    ob_start();
    include $viewPath;
    $html = ob_get_clean();
    
    echo "✅ View rendered successfully!\n";
    echo "   HTML length: " . strlen($html) . " bytes\n";
    
    // Check for common issues
    if (strpos($html, 'base_url') !== false) {
        echo "⚠️  WARNING: 'base_url' text found in output (might be unprocessed PHP)\n";
    }
    
    if (strpos($html, '<?php') !== false || strpos($html, '<?=') !== false) {
        echo "⚠️  WARNING: PHP tags found in output (not processed)\n";
    }
    
    if (strpos($html, 'Attendance') !== false) {
        echo "✅ Page contains 'Attendance' text\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error rendering view: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}
echo "\n";

// Step 4: Test the actual HTTP request
echo "Step 4: Testing HTTP request to /attendance...\n";
$baseUrl = 'http://localhost/HRIS';
$attendanceUrl = "$baseUrl/attendance";

$ch = curl_init($attendanceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    if ($httpCode === 200) {
        echo "✅ Page loaded successfully (HTTP 200)\n";
        
        // Parse response
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        
        // Check for errors in body
        if (stripos($body, 'error') !== false) {
            echo "⚠️  'error' text found in response\n";
        }
        
        if (stripos($body, 'failed') !== false) {
            echo "⚠️  'failed' text found in response\n";
        }
        
        if (stripos($body, 'Attendance') !== false) {
            echo "✅ Response contains 'Attendance' text\n";
        }
        
        // Check for JavaScript errors
        if (stripos($body, 'base_url is not defined') !== false) {
            echo "❌ JavaScript error: base_url is not defined\n";
        }
        
        // Save response to file for inspection
        $outputFile = __DIR__ . '/debug_attendance_output.html';
        file_put_contents($outputFile, $body);
        echo "   Response saved to: $outputFile\n";
        
    } elseif ($httpCode === 302 || $httpCode === 301) {
        echo "⚠️  Redirect detected (HTTP $httpCode)\n";
        // Get redirect location
        if (preg_match('/Location: (.+)/', $headers, $matches)) {
            echo "   Redirecting to: " . trim($matches[1]) . "\n";
        }
    } elseif ($httpCode === 500) {
        echo "❌ Internal Server Error (HTTP 500)\n";
        echo "   Check Apache error logs for details\n";
    } else {
        echo "⚠️  Unexpected HTTP code: $httpCode\n";
    }
}
echo "\n";

// Step 5: Check Apache/PHP error logs
echo "Step 5: Checking for recent errors in logs...\n";
$logFile = __DIR__ . '/../public/logs/app.log';
if (file_exists($logFile)) {
    $logLines = file($logFile);
    $recentErrors = array_slice($logLines, -10);
    
    echo "Last 10 log entries:\n";
    foreach ($recentErrors as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
            echo "⚠️  " . trim($line) . "\n";
        }
    }
} else {
    echo "   Log file not found at: $logFile\n";
}
echo "\n";

// Step 6: Check if route is registered
echo "Step 6: Checking route registration...\n";
$routesFile = __DIR__ . '/../config/routes.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    if (strpos($routesContent, '/attendance') !== false) {
        echo "✅ '/attendance' route found in routes.php\n";
        
        // Find the specific route
        if (preg_match("/->get\('\/attendance'/", $routesContent)) {
            echo "✅ GET /attendance route registered\n";
        } else {
            echo "⚠️  Route might be registered differently\n";
        }
    } else {
        echo "❌ '/attendance' route NOT found in routes.php\n";
    }
} else {
    echo "❌ routes.php not found\n";
}
echo "\n";

echo "=== Debug Complete ===\n";
echo "\nSummary:\n";
echo "- If helpers.php loads but base_url() doesn't work in browser, it's a scope issue\n";
echo "- If HTTP 500, check Apache error logs\n";
echo "- If HTTP 302, check authentication/authorization\n";
echo "- Check debug_attendance_output.html for the actual HTML response\n";
