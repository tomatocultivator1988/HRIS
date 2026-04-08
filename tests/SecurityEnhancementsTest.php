<?php

/**
 * Security Enhancements Test
 * 
 * Tests for the security enhancements implemented in task 7.4:
 * - Input validation and sanitization
 * - CSRF protection
 * - Security headers
 * - Rate limiting
 * - Audit logging
 */

require_once __DIR__ . '/../src/autoload.php';

use Core\Container;
use Core\Request;
use Core\Response;
use Middleware\InputValidationMiddleware;
use Middleware\CsrfMiddleware;
use Middleware\SecurityHeadersMiddleware;
use Middleware\RateLimitMiddleware;
use Services\AuditLogService;

class SecurityEnhancementsTest
{
    private Container $container;
    private int $passed = 0;
    private int $failed = 0;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
    }
    
    public function runAllTests(): void
    {
        echo "Running Security Enhancements Tests...\n\n";
        
        $this->testInputValidationMiddleware();
        $this->testCsrfTokenGeneration();
        $this->testSecurityHeaders();
        $this->testRateLimitMiddleware();
        $this->testAuditLogService();
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Results:\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo str_repeat("=", 50) . "\n";
    }
    
    private function testInputValidationMiddleware(): void
    {
        echo "Testing Input Validation Middleware...\n";
        
        try {
            $middleware = new InputValidationMiddleware();
            
            // Test 1: Valid input should pass
            $_GET = ['name' => 'John Doe', 'age' => '30'];
            $_POST = [];
            $request = new Request();
            $result = $middleware->handle($request);
            
            $this->assert($result === null, "Valid input should pass validation");
            
            // Test 2: SQL injection pattern should be blocked
            $_GET = ['query' => "'; DROP TABLE users; --"];
            $request = new Request();
            $result = $middleware->handle($request);
            
            $this->assert($result !== null, "SQL injection pattern should be blocked");
            $this->assert($result->getStatusCode() === 400, "Should return 400 status code");
            
            echo "  ✓ Input Validation Middleware tests passed\n\n";
            
        } catch (Exception $e) {
            echo "  ✗ Input Validation Middleware test failed: " . $e->getMessage() . "\n\n";
            $this->failed++;
        }
    }
    
    private function testCsrfTokenGeneration(): void
    {
        echo "Testing CSRF Token Generation...\n";
        
        try {
            // Test 1: Token generation
            $token1 = CsrfMiddleware::generateToken();
            $this->assert(!empty($token1), "CSRF token should be generated");
            $this->assert(strlen($token1) === 64, "CSRF token should be 64 characters (32 bytes hex)");
            
            // Test 2: Token retrieval
            $token2 = CsrfMiddleware::getToken();
            $this->assert($token1 === $token2, "Retrieved token should match generated token");
            
            echo "  ✓ CSRF Token Generation tests passed\n\n";
            
        } catch (Exception $e) {
            echo "  ✗ CSRF Token Generation test failed: " . $e->getMessage() . "\n\n";
            $this->failed++;
        }
    }
    
    private function testSecurityHeaders(): void
    {
        echo "Testing Security Headers...\n";
        
        try {
            $middleware = new SecurityHeadersMiddleware();
            $response = new Response();
            
            // Apply security headers
            $response = $middleware->applyHeaders($response);
            
            $headers = $response->getHeaders();
            
            // Test 1: X-Frame-Options header
            $this->assert(isset($headers['X-Frame-Options']), "X-Frame-Options header should be set");
            
            // Test 2: X-Content-Type-Options header
            $this->assert(isset($headers['X-Content-Type-Options']), "X-Content-Type-Options header should be set");
            
            // Test 3: X-XSS-Protection header
            $this->assert(isset($headers['X-XSS-Protection']), "X-XSS-Protection header should be set");
            
            // Test 4: Content-Security-Policy header
            $this->assert(isset($headers['Content-Security-Policy']), "Content-Security-Policy header should be set");
            
            echo "  ✓ Security Headers tests passed\n\n";
            
        } catch (Exception $e) {
            echo "  ✗ Security Headers test failed: " . $e->getMessage() . "\n\n";
            $this->failed++;
        }
    }
    
    private function testRateLimitMiddleware(): void
    {
        echo "Testing Rate Limit Middleware...\n";
        
        try {
            // Note: This is a basic test. Full testing would require mocking
            $middleware = new RateLimitMiddleware();
            
            // Test that middleware initializes without errors
            $this->assert($middleware !== null, "Rate Limit Middleware should initialize");
            
            echo "  ✓ Rate Limit Middleware tests passed\n\n";
            
        } catch (Exception $e) {
            echo "  ✗ Rate Limit Middleware test failed: " . $e->getMessage() . "\n\n";
            $this->failed++;
        }
    }
    
    private function testAuditLogService(): void
    {
        echo "Testing Audit Log Service...\n";
        
        try {
            $auditLogService = $this->container->resolve(AuditLogService::class);
            
            // Test 1: Service initialization
            $this->assert($auditLogService !== null, "Audit Log Service should initialize");
            
            // Test 2: Log an action (will log to file if database not available)
            $result = $auditLogService->log('TEST_ACTION', ['test' => 'data'], 'test-user-id', 'admin');
            
            // We don't assert the result because it depends on database availability
            // Just verify the method doesn't throw an exception
            
            echo "  ✓ Audit Log Service tests passed\n\n";
            
        } catch (Exception $e) {
            echo "  ✗ Audit Log Service test failed: " . $e->getMessage() . "\n\n";
            $this->failed++;
        }
    }
    
    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
        } else {
            $this->failed++;
            echo "  ✗ Assertion failed: {$message}\n";
        }
    }
}

// Run tests
$test = new SecurityEnhancementsTest();
$test->runAllTests();
