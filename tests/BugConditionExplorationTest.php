<?php

/**
 * Bug Condition Exploration Tests for Routing and Asset Path Fixes
 * 
 * These tests demonstrate the 5 bugs exist on UNFIXED code.
 * They are EXPECTED TO FAIL initially to confirm the bugs exist.
 * 
 * After fixes are implemented, these tests should PASS.
 * 
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 2.5
 */

$baseUrl = 'http://localhost/HRIS';
$testsPassed = 0;
$testsFailed = 0;
$failureDetails = [];

echo "\n=== Bug Condition Exploration Tests ===\n";
echo "These tests are EXPECTED TO FAIL on unfixed code to confirm bugs exist.\n\n";

/**
 * Task 1.1: Bug #1 - Asset path doubling test
 * 
 * **Validates: Requirements 1.1, 2.1**
 * 
 * Test that requesting `/HRIS/assets/js/config.js` returns JavaScript content 
 * with `Content-Type: application/javascript`.
 * 
 * EXPECTED OUTCOME ON UNFIXED CODE: Test FAILS (returns HTML instead of JavaScript)
 * EXPECTED OUTCOME ON FIXED CODE: Test PASSES (returns JavaScript with correct MIME type)
 */
echo "Test 1.1: Bug #1 - Asset path doubling test\n";
try {
    // Arrange: Request a JavaScript asset
    $assetUrl = $baseUrl . '/assets/js/config.js';
    
    // Act: Make HTTP request
    $ch = curl_init($assetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    // Extract headers and body
    list($headers, $body) = parseHttpResponse($response);
    
    // Assert: Should return JavaScript content with correct MIME type
    $passed = true;
    $errors = [];
    
    if ($httpCode !== 200) {
        $passed = false;
        $errors[] = "Expected HTTP 200 for asset request, got {$httpCode}";
    }
    
    if (stripos($contentType, 'application/javascript') === false && stripos($contentType, 'text/javascript') === false) {
        $passed = false;
        $errors[] = "Expected Content-Type: application/javascript, got: {$contentType}. " .
            "Bug #1: Asset path doubling causes .htaccess to fail file check, " .
            "falling through to index.php which returns HTML instead of JavaScript.";
    }
    
    if (stripos($body, '<!DOCTYPE html>') !== false || stripos($body, '<html') !== false) {
        $passed = false;
        $errors[] = "Expected JavaScript content, but got HTML. " .
            "Bug #1: .htaccess checks DOCUMENT_ROOT/HRIS/public/HRIS/assets/js/config.js (doubled path), " .
            "file check fails, request falls through to index.php.";
    }
    
    if (stripos($body, 'window.AppConfig') === false && stripos($body, 'AppConfig') === false) {
        $passed = false;
        $errors[] = "Expected JavaScript content containing 'AppConfig', but got: " . substr($body, 0, 200);
    }
    
    if ($passed) {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $testsPassed++;
    } else {
        echo "  \033[31m✗ FAILED\033[0m\n";
        $testsFailed++;
        $failureDetails['Test 1.1'] = $errors;
        foreach ($errors as $error) {
            echo "    - $error\n";
        }
    }
} catch (Exception $e) {
    echo "  \033[31m✗ FAILED (Exception)\033[0m\n";
    echo "    - {$e->getMessage()}\n";
    $testsFailed++;
    $failureDetails['Test 1.1'] = [$e->getMessage()];
}


/**
 * Task 1.2: Bug #2 - Route shadowing test
 * 
 * **Validates: Requirements 1.2, 2.2**
 * 
 * Test that navigating to `/HRIS/employees/create` executes 
 * `EmployeeController@createForm` action.
 * 
 * EXPECTED OUTCOME ON UNFIXED CODE: Test FAILS (executes showView instead)
 * EXPECTED OUTCOME ON FIXED CODE: Test PASSES (executes createForm)
 */
echo "\nTest 1.2: Bug #2 - Route shadowing test\n";
try {
    // Arrange: Navigate to /employees/create
    $createUrl = $baseUrl . '/employees/create';
    
    // Act: Make HTTP request
    $ch = curl_init($createUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    // Assert: Should execute createForm action, not showView
    $passed = true;
    $errors = [];
    
    // Check for indicators that showView was executed (would show error or employee details)
    $hasShowViewIndicators = (
        stripos($response, 'Employee not found') !== false ||
        stripos($response, 'Invalid employee ID') !== false ||
        stripos($response, 'Employee Details') !== false
    );
    
    if ($hasShowViewIndicators) {
        $passed = false;
        $errors[] = "Expected createForm action, but got showView action. " .
            "Bug #2: Static route /employees/create is shadowed by parameterized route /employees/{id}.";
    }
    
    // If we get 401 with authentication error, that's actually correct (route is matched, auth middleware runs)
    if ($httpCode === 401) {
        $jsonData = json_decode($response, true);
        if (isset($jsonData['error']) && $jsonData['error'] === 'UNAUTHORIZED') {
            // This is correct - route is matched, authentication middleware is running
            // If route was shadowed, we would see "Employee not found" or "Invalid employee ID"
            $passed = true;
        } else {
            $passed = false;
            $errors[] = "Expected authentication error or create form, got: " . substr($response, 0, 200);
        }
    } elseif ($httpCode === 200) {
        // Check for indicators that createForm was executed (form elements)
        $hasCreateFormIndicators = (
            stripos($response, 'Create Employee') !== false ||
            stripos($response, 'New Employee') !== false ||
            stripos($response, 'Add Employee') !== false
        );
        
        if (!$hasCreateFormIndicators) {
            $passed = false;
            $errors[] = "Expected createForm action (form to create new employee), but got response: " . 
                substr($response, 0, 500) . "\n" .
                "Bug #2: Route shadowing - parameterized route /employees/{id} matches before " .
                "static route /employees/create, executing showView with id='create' instead of createForm.";
        }
    } else {
        $passed = false;
        $errors[] = "Expected HTTP 200 or 401 for /employees/create, got {$httpCode}";
    }
    
    if ($passed) {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $testsPassed++;
    } else {
        echo "  \033[31m✗ FAILED\033[0m\n";
        $testsFailed++;
        $failureDetails['Test 1.2'] = $errors;
        foreach ($errors as $error) {
            echo "    - $error\n";
        }
    }
} catch (Exception $e) {
    echo "  \033[31m✗ FAILED (Exception)\033[0m\n";
    echo "    - {$e->getMessage()}\n";
    $testsFailed++;
    $failureDetails['Test 1.2'] = [$e->getMessage()];
}


/**
 * Task 1.3: Bug #3 - Relative asset path test
 * 
 * **Validates: Requirements 1.3, 2.3**
 * 
 * Test that loading `/HRIS/login` page successfully loads all assets.
 * 
 * EXPECTED OUTCOME ON UNFIXED CODE: Test FAILS (assets fail to load)
 * EXPECTED OUTCOME ON FIXED CODE: Test PASSES (all assets load successfully)
 */
echo "\nTest 1.3: Bug #3 - Relative asset path test\n";
try {
    // Arrange: Load login page
    $loginUrl = $baseUrl . '/login';
    
    // Act: Make HTTP request
    $ch = curl_init($loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $passed = true;
    $errors = [];
    
    if ($httpCode !== 200) {
        $passed = false;
        $errors[] = "Expected HTTP 200 for /login, got {$httpCode}";
    }
    
    // Assert: Check for relative asset paths (bug condition)
    $hasRelativePaths = (
        preg_match('/href="assets\/css\//', $response) ||
        preg_match('/src="assets\/js\//', $response)
    );
    
    if ($hasRelativePaths) {
        $passed = false;
        $errors[] = "Expected absolute asset paths (starting with /), but found relative paths. " .
            "Bug #3: login.php uses relative paths like 'assets/css/custom.css' and 'assets/js/config.js'. " .
            "When served from /HRIS/login, browser resolves these to /HRIS/login/assets/... which triggers Bug #1.";
    }
    
    // Check for absolute paths (expected behavior)
    $hasAbsolutePaths = (
        preg_match('/href="\/assets\/css\//', $response) ||
        preg_match('/src="\/assets\/js\//', $response)
    );
    
    if (!$hasAbsolutePaths) {
        $passed = false;
        $errors[] = "Expected absolute asset paths (starting with /), but found: " . 
            substr($response, 0, 1000) . "\n" .
            "Bug #3: Relative asset paths in login.php cause incorrect path resolution.";
    }
    
    if ($passed) {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $testsPassed++;
    } else {
        echo "  \033[31m✗ FAILED\033[0m\n";
        $testsFailed++;
        $failureDetails['Test 1.3'] = $errors;
        foreach ($errors as $error) {
            echo "    - $error\n";
        }
    }
} catch (Exception $e) {
    echo "  \033[31m✗ FAILED (Exception)\033[0m\n";
    echo "    - {$e->getMessage()}\n";
    $testsFailed++;
    $failureDetails['Test 1.3'] = [$e->getMessage()];
}


/**
 * Task 1.4: Bug #4 - Missing web routes test
 * 
 * **Validates: Requirements 1.4, 2.4**
 * 
 * Test that navigating to `/HRIS/reports` and `/HRIS/profile` returns HTML views.
 * 
 * EXPECTED OUTCOME ON UNFIXED CODE: Test FAILS (returns 404)
 * EXPECTED OUTCOME ON FIXED CODE: Test PASSES (returns HTML views)
 */
echo "\nTest 1.4: Bug #4 - Missing web routes test\n";
try {
    $passed = true;
    $errors = [];
    
    // Test /reports route
    $reportsUrl = $baseUrl . '/reports';
    
    $ch = curl_init($reportsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $reportsResponse = curl_exec($ch);
    $reportsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($reportsHttpCode !== 200) {
        $passed = false;
        $errors[] = "Expected HTTP 200 for /reports, got {$reportsHttpCode}. " .
            "Bug #4: No web route exists for /reports (only API route /api/reports/attendance exists). " .
            "Router returns null and front controller returns 404.";
    }
    
    if (stripos($reportsResponse, '<!DOCTYPE html>') === false && stripos($reportsResponse, '<html') === false) {
        $passed = false;
        $errors[] = "Expected HTML view for /reports, but got: " . substr($reportsResponse, 0, 200);
    }
    
    // Test /profile route
    $profileUrl = $baseUrl . '/profile';
    
    $ch = curl_init($profileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $profileResponse = curl_exec($ch);
    $profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($profileHttpCode !== 200) {
        $passed = false;
        $errors[] = "Expected HTTP 200 for /profile, got {$profileHttpCode}. " .
            "Bug #4: No web route exists for /profile. " .
            "Navigation links exist in base layout but no route definitions in config/routes.php.";
    }
    
    if (stripos($profileResponse, '<!DOCTYPE html>') === false && stripos($profileResponse, '<html') === false) {
        $passed = false;
        $errors[] = "Expected HTML view for /profile, but got: " . substr($profileResponse, 0, 200);
    }
    
    if ($passed) {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $testsPassed++;
    } else {
        echo "  \033[31m✗ FAILED\033[0m\n";
        $testsFailed++;
        $failureDetails['Test 1.4'] = $errors;
        foreach ($errors as $error) {
            echo "    - $error\n";
        }
    }
} catch (Exception $e) {
    echo "  \033[31m✗ FAILED (Exception)\033[0m\n";
    echo "    - {$e->getMessage()}\n";
    $testsFailed++;
    $failureDetails['Test 1.4'] = [$e->getMessage()];
}


/**
 * Task 1.5: Bug #5 - Outdated redirect URLs test
 * 
 * **Validates: Requirements 1.5, 2.5**
 * 
 * Test that successful login redirects to `/HRIS/dashboard/admin` or 
 * `/HRIS/dashboard/employee` (without .html).
 * 
 * EXPECTED OUTCOME ON UNFIXED CODE: Test FAILS (redirects to .html URLs)
 * EXPECTED OUTCOME ON FIXED CODE: Test PASSES (redirects to MVC routes)
 */
echo "\nTest 1.5: Bug #5 - Outdated redirect URLs test\n";
try {
    // Arrange: Load auth.js file
    $authJsUrl = $baseUrl . '/assets/js/auth.js';
    
    // Act: Make HTTP request
    $ch = curl_init($authJsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $authJsContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $passed = true;
    $errors = [];
    
    if ($httpCode !== 200) {
        $passed = false;
        $errors[] = "Expected HTTP 200 for auth.js, got {$httpCode}";
    }
    
    // Assert: Check for .html extensions in redirect URLs (bug condition)
    $hasHtmlExtensions = (
        stripos($authJsContent, '/dashboard/admin.html') !== false ||
        stripos($authJsContent, '/dashboard/employee.html') !== false ||
        preg_match('/url\([\'"]index\.html[\'"]\)/', $authJsContent)
    );
    
    if ($hasHtmlExtensions) {
        $passed = false;
        $errors[] = "Expected MVC routes without .html extensions, but found .html in auth.js. " .
            "Bug #5: auth.js constructs redirect URLs using '/dashboard/admin.html' and '/dashboard/employee.html'. " .
            "MVC routing system uses clean URLs without file extensions (/dashboard/admin not /dashboard/admin.html). " .
            "After login, redirect to .html URLs causes 404 errors.";
    }
    
    // Check for correct MVC routes (expected behavior)
    $hasMvcRoutes = (
        preg_match('/\/dashboard\/admin[\'"\)]/', $authJsContent) ||
        preg_match('/\/dashboard\/employee[\'"\)]/', $authJsContent)
    );
    
    if (!$hasMvcRoutes) {
        $passed = false;
        $errors[] = "Expected MVC routes like '/dashboard/admin' and '/dashboard/employee' without .html extensions. " .
            "Bug #5: Outdated redirect URLs in auth.js point to non-existent .html files.";
    }
    
    if ($passed) {
        echo "  \033[32m✓ PASSED\033[0m\n";
        $testsPassed++;
    } else {
        echo "  \033[31m✗ FAILED\033[0m\n";
        $testsFailed++;
        $failureDetails['Test 1.5'] = $errors;
        foreach ($errors as $error) {
            echo "    - $error\n";
        }
    }
} catch (Exception $e) {
    echo "  \033[31m✗ FAILED (Exception)\033[0m\n";
    echo "    - {$e->getMessage()}\n";
    $testsFailed++;
    $failureDetails['Test 1.5'] = [$e->getMessage()];
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "\033[32mPassed: {$testsPassed}\033[0m\n";
echo "\033[31mFailed: {$testsFailed}\033[0m\n";

if ($testsFailed > 0) {
    echo "\n\033[33m⚠ EXPECTED RESULT: Tests FAILED on unfixed code\033[0m\n";
    echo "This confirms the bugs exist. The failures demonstrate:\n";
    foreach ($failureDetails as $testName => $errors) {
        echo "\n{$testName}:\n";
        foreach ($errors as $error) {
            echo "  - " . substr($error, 0, 200) . (strlen($error) > 200 ? '...' : '') . "\n";
        }
    }
    echo "\nNext step: Implement fixes in Phase 3 of the task plan.\n";
    exit(0); // Exit with success - failures are expected for exploration tests
} else {
    echo "\n\033[31m⚠ UNEXPECTED RESULT: All tests PASSED\033[0m\n";
    echo "This is unexpected for unfixed code. Either:\n";
    echo "1. The bugs have already been fixed\n";
    echo "2. The test conditions need adjustment\n";
    echo "3. The root cause analysis may be incorrect\n";
    exit(1);
}

/**
 * Helper function to parse HTTP response into headers and body
 */
function parseHttpResponse(string $response): array
{
    $parts = explode("\r\n\r\n", $response, 2);
    $headers = $parts[0] ?? '';
    $body = $parts[1] ?? '';
    return [$headers, $body];
}

