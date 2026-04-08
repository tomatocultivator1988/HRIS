<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;
use Services\AuthService;
use Models\User;

/**
 * Authentication Middleware
 * 
 * Ensures that requests are authenticated before proceeding to controllers.
 * Validates JWT tokens and injects authenticated user into request context.
 */
class AuthMiddleware
{
    private Container $container;
    private AuthService $authService;
    private User $userModel;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->authService = $this->container->resolve(AuthService::class);
        $this->userModel = $this->container->resolve(User::class);
    }
    
    /**
     * Handle the request
     *
     * @param Request $request
     * @return Response|null Return Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        // Extract JWT token from Authorization header
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->unauthorizedResponse('Missing authentication token');
        }
        
        // Validate token with AuthService
        $validationResult = $this->authService->validateToken($token);
        
        if (!$validationResult['success']) {
            return $this->unauthorizedResponse($validationResult['message'] ?? 'Invalid token');
        }
        
        // Get user data
        $userData = $validationResult['user'];
        
        // Check if user is active (default to true if field not present)
        $isActive = $userData['is_active'] ?? true;
        if (!$userData || !$isActive) {
            return $this->unauthorizedResponse('User account is inactive');
        }
        
        // Inject authenticated user into request context
        $request->setUser($userData);
        
        // Log successful authentication
        $this->logAuthentication($userData, $request);
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Extract JWT token from Authorization header
     *
     * @param Request $request HTTP request
     * @return string|null JWT token or null if not found
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->getHeader('Authorization');
        
        if (!$authHeader) {
            return null;
        }
        
        // Check for Bearer token format
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Create unauthorized response
     *
     * @param string $message Error message
     * @return Response Unauthorized response
     */
    private function unauthorizedResponse(string $message): Response
    {
        $response = new Response();
        
        return $response->json([
            'success' => false,
            'message' => $message,
            'error' => 'UNAUTHORIZED'
        ], 401);
    }
    
    /**
     * Log successful authentication
     *
     * @param array $userData User data
     * @param Request $request HTTP request
     * @return void
     */
    private function logAuthentication(array $userData, Request $request): void
    {
        try {
            $this->authService->logActivity(
                $userData['id'],
                $userData['role'],
                'API_ACCESS'
            );
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('AuthMiddleware::logAuthentication Error: ' . $e->getMessage());
        }
    }
}