<?php

/**
 * ErrorHandler Test
 * 
 * Tests the centralized error handling system including:
 * - Exception hierarchy
 * - Error logging
 * - User message formatting
 * - HTTP status code mapping
 */

require_once __DIR__ . '/../src/autoload.php';

use Core\ErrorHandler;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\DatabaseException;
use Core\BusinessLogicException;
use Core\NotFoundException;
use Core\HRISException;

// Test configuration
$testLogFile = __DIR__ . '/../logs/test_error_handler.log';
$passed = 0;
$failed = 0;

// Clean up test log file
if (file_exists($testLogFile)) {
    unlink($testLogFile);
}

echo "=== ErrorHandler Test Suite ===\n\n";

/**
 * Test 1: ValidationException with proper status code and errors
 */
echo "Test 1: ValidationException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, true);
    $errors = [
        'email' => 'Invalid email format',
        'password' => 'Password must be at least 8 characters'
    ];
    $exception = new ValidationException('Validation failed', $errors);
    
    // Check exception properties
    assert($exception->getHttpStatusCode() === 422, 'ValidationException should have status code 422');
    assert($exception->getErrors() === $errors, 'ValidationException should store errors');
    assert($exception->getUserMessage() === 'The provided data is invalid. Please check your input and try again.', 'ValidationException should have user-friendly message');
    
    // Test handler response
    $response = $handler->handleValidationError($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Validation Error', 'Response should have correct error title');
    assert(isset($data['errors']), 'Response should include validation errors');
    assert($data['errors'] === $errors, 'Response errors should match exception errors');
    
    echo "✓ ValidationException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ ValidationException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 2: AuthenticationException with 401 status
 */
echo "Test 2: AuthenticationException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new AuthenticationException('Invalid credentials');
    
    assert($exception->getHttpStatusCode() === 401, 'AuthenticationException should have status code 401');
    assert($exception->getUserMessage() === 'Authentication is required. Please log in to continue.', 'AuthenticationException should have user-friendly message');
    
    $response = $handler->handleAuthError($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Authentication Required', 'Response should have correct error title');
    
    echo "✓ AuthenticationException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ AuthenticationException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 3: AuthorizationException with 403 status
 */
echo "Test 3: AuthorizationException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new AuthorizationException('Insufficient permissions');
    
    assert($exception->getHttpStatusCode() === 403, 'AuthorizationException should have status code 403');
    assert($exception->getUserMessage() === 'You do not have permission to perform this action.', 'AuthorizationException should have user-friendly message');
    
    $response = $handler->handleAuthError($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Access Denied', 'Response should have correct error title');
    
    echo "✓ AuthorizationException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ AuthorizationException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 4: DatabaseException with 500 status
 */
echo "Test 4: DatabaseException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new DatabaseException('Connection failed');
    
    assert($exception->getHttpStatusCode() === 500, 'DatabaseException should have status code 500');
    assert($exception->getUserMessage() === 'A database error occurred. Please try again later.', 'DatabaseException should have user-friendly message');
    
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Internal Server Error', 'Response should have correct error title');
    // User should NOT see technical database error details
    assert($data['message'] !== 'Connection failed', 'Response should not expose technical details');
    
    echo "✓ DatabaseException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ DatabaseException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 5: BusinessLogicException with 400 status
 */
echo "Test 5: BusinessLogicException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new BusinessLogicException('Cannot approve leave request: insufficient balance');
    
    assert($exception->getHttpStatusCode() === 400, 'BusinessLogicException should have status code 400');
    // Business logic messages are safe to show users
    assert($exception->getUserMessage() === 'Cannot approve leave request: insufficient balance', 'BusinessLogicException should show actual message');
    
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Bad Request', 'Response should have correct error title');
    
    echo "✓ BusinessLogicException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ BusinessLogicException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 6: NotFoundException with 404 status
 */
echo "Test 6: NotFoundException handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new NotFoundException('Employee not found');
    
    assert($exception->getHttpStatusCode() === 404, 'NotFoundException should have status code 404');
    assert($exception->getUserMessage() === 'The requested resource was not found.', 'NotFoundException should have user-friendly message');
    
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    assert($data['success'] === false, 'Response should indicate failure');
    assert($data['error'] === 'Not Found', 'Response should have correct error title');
    
    echo "✓ NotFoundException test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ NotFoundException test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 7: Error logging functionality
 */
echo "Test 7: Error logging...\n";
try {
    $handler = new ErrorHandler($testLogFile, true);
    $exception = new ValidationException('Test validation error', ['field' => 'error']);
    
    $handler->logError($exception, ['user_id' => 123]);
    
    assert(file_exists($testLogFile), 'Log file should be created');
    $logContent = file_get_contents($testLogFile);
    assert(strpos($logContent, 'ValidationException') !== false, 'Log should contain exception type');
    assert(strpos($logContent, 'Test validation error') !== false, 'Log should contain error message');
    
    echo "✓ Error logging test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ Error logging test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 8: Debug mode functionality
 */
echo "Test 8: Debug mode...\n";
try {
    // Test with debug mode ON
    $handler = new ErrorHandler($testLogFile, true);
    $exception = new DatabaseException('Test error');
    
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    assert(isset($data['debug']), 'Debug mode should include debug information');
    assert(isset($data['debug']['exception']), 'Debug info should include exception class');
    assert(isset($data['debug']['file']), 'Debug info should include file');
    assert(isset($data['debug']['line']), 'Debug info should include line number');
    
    // Test with debug mode OFF
    $handler->setDebugMode(false);
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    assert(!isset($data['debug']), 'Debug mode off should not include debug information');
    
    echo "✓ Debug mode test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ Debug mode test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 9: Exception context handling
 */
echo "Test 9: Exception context...\n";
try {
    $handler = new ErrorHandler($testLogFile, true);
    $context = ['user_id' => 456, 'action' => 'delete_employee'];
    $exception = new AuthorizationException('Cannot delete employee', 0, null, $context);
    
    assert($exception->getContext() === $context, 'Exception should store context');
    
    $handler->logError($exception);
    $logContent = file_get_contents($testLogFile);
    assert(strpos($logContent, 'user_id') !== false, 'Log should contain context data');
    
    echo "✓ Exception context test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ Exception context test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 10: HTTP status code mapping for standard exceptions
 */
echo "Test 10: Standard exception handling...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    $exception = new \InvalidArgumentException('Invalid argument');
    
    $response = $handler->handleException($exception);
    $data = json_decode($response->getContent(), true);
    
    // InvalidArgumentException should map to 400
    assert($response->getStatusCode() === 400, 'InvalidArgumentException should map to status code 400');
    assert($data['success'] === false, 'Response should indicate failure');
    
    echo "✓ Standard exception handling test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ Standard exception handling test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

/**
 * Test 11: Consistent error response format
 */
echo "Test 11: Error response format consistency...\n";
try {
    $handler = new ErrorHandler($testLogFile, false);
    
    $exceptions = [
        new ValidationException('Validation error', ['field' => 'error']),
        new AuthenticationException('Auth error'),
        new DatabaseException('DB error'),
        new BusinessLogicException('Business error'),
        new NotFoundException('Not found')
    ];
    
    foreach ($exceptions as $exception) {
        $response = $handler->handleException($exception);
        $data = json_decode($response->getContent(), true);
        
        // All responses should have consistent structure
        assert(isset($data['success']), 'Response should have success field');
        assert(isset($data['error']), 'Response should have error field');
        assert(isset($data['message']), 'Response should have message field');
        assert(isset($data['timestamp']), 'Response should have timestamp field');
        assert($data['success'] === false, 'Success should be false for errors');
    }
    
    echo "✓ Error response format consistency test passed\n\n";
    $passed++;
} catch (AssertionError $e) {
    echo "✗ Error response format consistency test failed: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Clean up test log file
if (file_exists($testLogFile)) {
    unlink($testLogFile);
}

// Summary
echo "\n=== Test Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed.\n";
    exit(1);
}
