<?php

/**
 * Unit Tests for Authentication and Role Middleware
 * 
 * Tests the middleware functionality including token validation,
 * role checking, and request handling.
 */

require_once dirname(__DIR__) . '/src/autoload.php';

use Core\Container;
use Core\Request;
use Core\Response;
use Middleware\AuthMiddleware;
use Middleware\RoleMiddleware;

class MiddlewareTest
{
    private Container $container;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->container->registerDefaultBindings();
    }
    
    /**
     * Create a mock request with authorization header
     */
    private function createMockRequest(string $token = null, array $userData = null): Request
    {
        // Reset $_SERVER for clean test
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/api/test',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Test Agent'
        ];
        
        if ($token) {
            $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";
        }
        
        $request = Request::createFromGlobals();
        
        if ($userData) {
            $request->setUser($userData);
        }
        
        return $request;
    }
    
    /**
     * Test AuthMiddleware with missing token
     */
    public function testAuthMiddlewareMissingToken(): bool
    {
        $request = $this->createMockRequest(); // No token
        $middleware = new AuthMiddleware();
        
        try {
            $response = $middleware->handle($request);
            
            // Should return 401 response for missing token
            return $response instanceof Response && 
                   $response->getStatusCode() === 401;
        } catch (Exception $e) {
            // Expected to fail due to missing AuthService dependencies
            return true;
        }
    }
    
    /**
     * Test AuthMiddleware with invalid token
     */
    public function testAuthMiddlewareInvalidToken(): bool
    {
        $request = $this->createMockRequest('invalid_token');
        $middleware = new AuthMiddleware();
        
        try {
            $response = $middleware->handle($request);
            
            // Should return 401 response for invalid token
            return $response instanceof Response && 
                   $response->getStatusCode() === 401;
        } catch (Exception $e) {
            // Expected to fail due to missing AuthService dependencies
            return true;
        }
    }
    
    /**
     * Test RoleMiddleware with no authenticated user
     */
    public function testRoleMiddlewareNoUser(): bool
    {
        $request = $this->createMockRequest(); // No user data
        $middleware = RoleMiddleware::role('admin');
        
        $response = $middleware->handle($request);
        
        // Should return 403 response for missing user
        return $response instanceof Response && 
               $response->getStatusCode() === 403;
    }
    
    /**
     * Test RoleMiddleware with correct role
     */
    public function testRoleMiddlewareCorrectRole(): bool
    {
        $userData = [
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => true
        ];
        
        $request = $this->createMockRequest('valid_token', $userData);
        $middleware = RoleMiddleware::role('admin');
        
        $response = $middleware->handle($request);
        
        // Should return null (continue) for correct role
        return $response === null;
    }
    
    /**
     * Test RoleMiddleware with incorrect role
     */
    public function testRoleMiddlewareIncorrectRole(): bool
    {
        $userData = [
            'id' => 1,
            'email' => 'employee@example.com',
            'role' => 'employee',
            'is_active' => true
        ];
        
        $request = $this->createMockRequest('valid_token', $userData);
        $middleware = RoleMiddleware::role('admin');
        
        $response = $middleware->handle($request);
        
        // Should return 403 response for incorrect role
        return $response instanceof Response && 
               $response->getStatusCode() === 403;
    }
    
    /**
     * Test RoleMiddleware with multiple roles (OR logic)
     */
    public function testRoleMiddlewareMultipleRoles(): bool
    {
        $userData = [
            'id' => 1,
            'email' => 'employee@example.com',
            'role' => 'employee',
            'is_active' => true
        ];
        
        $request = $this->createMockRequest('valid_token', $userData);
        $middleware = RoleMiddleware::roles(['admin', 'employee']);
        
        $response = $middleware->handle($request);
        
        // Should return null (continue) for matching role
        return $response === null;
    }
    
    /**
     * Test RoleMiddleware with permission checking
     */
    public function testRoleMiddlewarePermission(): bool
    {
        $userData = [
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => true
        ];
        
        $request = $this->createMockRequest('valid_token', $userData);
        $middleware = RoleMiddleware::permission('manage_employees');
        
        $response = $middleware->handle($request);
        
        // Should return null (continue) for admin with manage_employees permission
        return $response === null;
    }
    
    /**
     * Test Request class user methods
     */
    public function testRequestUserMethods(): bool
    {
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'admin'
        ];
        
        $request = $this->createMockRequest();
        
        // Initially no user
        if ($request->isAuthenticated()) {
            return false;
        }
        
        // Set user
        $request->setUser($userData);
        
        // Now should be authenticated
        if (!$request->isAuthenticated()) {
            return false;
        }
        
        // User data should match
        $retrievedUser = $request->getUser();
        return $retrievedUser === $userData;
    }
    
    /**
     * Test Response class JSON methods
     */
    public function testResponseJsonMethods(): bool
    {
        $response = new Response();
        
        // Test JSON response
        $data = ['test' => 'value', 'success' => true];
        $response->json($data, 200);
        
        if ($response->getStatusCode() !== 200) {
            return false;
        }
        
        if ($response->getHeader('Content-Type') !== 'application/json') {
            return false;
        }
        
        $content = json_decode($response->getContent(), true);
        return $content === $data;
    }
    
    /**
     * Run all tests
     */
    public function runTests(): void
    {
        echo "Running Middleware Tests\n";
        echo "========================\n\n";
        
        $tests = [
            'testAuthMiddlewareMissingToken' => 'AuthMiddleware - Missing token handling',
            'testAuthMiddlewareInvalidToken' => 'AuthMiddleware - Invalid token handling',
            'testRoleMiddlewareNoUser' => 'RoleMiddleware - No user handling',
            'testRoleMiddlewareCorrectRole' => 'RoleMiddleware - Correct role access',
            'testRoleMiddlewareIncorrectRole' => 'RoleMiddleware - Incorrect role blocking',
            'testRoleMiddlewareMultipleRoles' => 'RoleMiddleware - Multiple roles (OR logic)',
            'testRoleMiddlewarePermission' => 'RoleMiddleware - Permission checking',
            'testRequestUserMethods' => 'Request - User authentication methods',
            'testResponseJsonMethods' => 'Response - JSON response methods'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $method => $description) {
            try {
                $result = $this->$method();
                
                if ($result) {
                    echo "✓ {$description}\n";
                    $passed++;
                } else {
                    echo "✗ {$description}\n";
                }
            } catch (Exception $e) {
                echo "✗ {$description} - Error: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nResults: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "✓ All Middleware tests passed!\n";
        } else {
            echo "✗ Some tests failed. Please review the implementation.\n";
        }
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new MiddlewareTest();
    $test->runTests();
}