<?php

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Container;
use Models\User;
use Services\AuditLogService;

/**
 * Role-based Authorization Middleware
 * 
 * Ensures that authenticated users have the required role to access resources.
 * Must be used after AuthMiddleware to ensure user is authenticated.
 */
class RoleMiddleware
{
    private Container $container;
    private User $userModel;
    private array $requiredRoles;
    private array $requiredPermissions;
    
    public function __construct($roleOrRoles = null, array $permissions = [])
    {
        $this->container = Container::getInstance();
        $this->userModel = $this->container->resolve(User::class);
        
        // Handle different parameter types for backward compatibility
        if (is_string($roleOrRoles)) {
            $this->requiredRoles = [$roleOrRoles];
        } elseif (is_array($roleOrRoles)) {
            $this->requiredRoles = $roleOrRoles;
        } else {
            $this->requiredRoles = [];
        }
        
        $this->requiredPermissions = $permissions;
    }
    
    /**
     * Create middleware instance for specific role
     *
     * @param string $role Required role
     * @return RoleMiddleware Middleware instance
     */
    public static function role(string $role): RoleMiddleware
    {
        return new self([$role]);
    }
    
    /**
     * Create middleware instance for multiple roles (OR logic)
     *
     * @param array $roles Required roles (user needs at least one)
     * @return RoleMiddleware Middleware instance
     */
    public static function roles(array $roles): RoleMiddleware
    {
        return new self($roles);
    }
    
    /**
     * Create middleware instance for specific permission
     *
     * @param string $permission Required permission
     * @return RoleMiddleware Middleware instance
     */
    public static function permission(string $permission): RoleMiddleware
    {
        return new self([], [$permission]);
    }
    
    /**
     * Create middleware instance for multiple permissions (AND logic)
     *
     * @param array $permissions Required permissions (user needs all)
     * @return RoleMiddleware Middleware instance
     */
    public static function permissions(array $permissions): RoleMiddleware
    {
        return new self([], $permissions);
    }
    
    /**
     * Handle the request
     *
     * @param Request $request
     * @return Response|null Return Response to halt execution, null to continue
     */
    public function handle(Request $request): ?Response
    {
        // Get authenticated user from request (set by AuthMiddleware)
        $userData = $request->getUser();
        
        if (!$userData) {
            return $this->forbiddenResponse('User not authenticated');
        }
        
        // Check role requirements
        if (!empty($this->requiredRoles)) {
            $userRole = $userData['role'] ?? '';
            
            if (!in_array($userRole, $this->requiredRoles)) {
                return $this->forbiddenResponse(
                    'Insufficient privileges. Required role: ' . implode(' or ', $this->requiredRoles)
                );
            }
        }
        
        // Check permission requirements
        if (!empty($this->requiredPermissions)) {
            foreach ($this->requiredPermissions as $permission) {
                if (!$this->userModel->hasPermission($userData, $permission)) {
                    return $this->forbiddenResponse(
                        "Insufficient privileges. Missing permission: {$permission}"
                    );
                }
            }
        }
        
        // Log authorization success
        $this->logAuthorization($userData, $request);
        
        return null; // Continue to next middleware/controller
    }
    
    /**
     * Create forbidden response
     *
     * @param string $message Error message
     * @return Response Forbidden response
     */
    private function forbiddenResponse(string $message): Response
    {
        $response = new Response();
        
        return $response->json([
            'success' => false,
            'message' => $message,
            'error' => 'FORBIDDEN'
        ], 403);
    }
    
    /**
     * Log successful authorization
     *
     * @param array $userData User data
     * @param Request $request HTTP request
     * @return void
     */
    private function logAuthorization(array $userData, Request $request): void
    {
        try {
            // Log authorization success for audit purposes
            $auditLogService = $this->container->resolve(AuditLogService::class);
            $auditLogService->log('AUTHORIZATION_SUCCESS', [
                'resource' => $request->getUri(),
                'method' => $request->getMethod(),
                'required_roles' => !empty($this->requiredRoles) ? implode(',', $this->requiredRoles) : null,
                'required_permissions' => !empty($this->requiredPermissions) ? implode(',', $this->requiredPermissions) : null
            ], (string) $userData['id'], $userData['role']);
            
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log('RoleMiddleware::logAuthorization Error: ' . $e->getMessage());
        }
    }
}
