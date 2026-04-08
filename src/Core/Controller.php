<?php

namespace Core;

/**
 * Controller Base Class - Provides common functionality for all controllers
 * 
 * This abstract class serves as the foundation for all controllers in the MVC framework,
 * providing request/response handling, input validation, and service coordination.
 */
abstract class Controller
{
    protected Container $container;
    protected Request $request;
    protected Response $response;
    
    /**
     * Constructor - Initialize controller with dependency injection container
     *
     * @param Container $container Dependency injection container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->response = new Response();
    }
    
    /**
     * Set the current request object
     *
     * @param Request $request The HTTP request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
    
    /**
     * Create a JSON response
     *
     * @param array $data Response data
     * @param int $status HTTP status code
     * @param array $headers Additional headers
     * @return Response JSON response
     */
    protected function json(array $data, int $status = 200, array $headers = []): Response
    {
        $response = new Response();
        $response->json($data, $status);
        
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }
        
        return $response;
    }
    
    /**
     * Create a successful JSON response
     *
     * @param array $data Response data
     * @param string $message Success message
     * @return Response Success response
     */
    protected function success(array $data = [], string $message = 'Success'): Response
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Create an error JSON response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $errors Detailed error information
     * @return Response Error response
     */
    protected function error(string $message, int $status = 400, array $errors = []): Response
    {
        $data = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        
        return $this->json($data, $status);
    }
    
    /**
     * Create a validation error response
     *
     * @param array $errors Validation errors
     * @param string $message Error message
     * @return Response Validation error response
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): Response
    {
        return $this->error($message, 422, $errors);
    }
    
    /**
     * Create an HTML view response
     *
     * @param string $template Template name
     * @param array $data Template data
     * @param int $status HTTP status code
     * @return Response HTML response
     */
    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        $viewRenderer = $this->container->resolve('ViewRenderer');
        // Render without layout since login.php is a complete HTML page
        $html = $viewRenderer->render($template, $data, null);
        
        $response = new Response($html, $status);
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        
        return $response;
    }
    
    /**
     * Create a redirect response
     *
     * @param string $url Redirect URL
     * @param int $status HTTP status code (301, 302, etc.)
     * @return Response Redirect response
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        $response = new Response('', $status);
        $response->setHeader('Location', $url);
        
        return $response;
    }
    
    /**
     * Validate request input using validation rules
     *
     * @param array $rules Validation rules
     * @param array $data Data to validate (optional, uses request data if not provided)
     * @return ValidationResult Validation result
     */
    protected function validate(array $rules, array $data = null): ValidationResult
    {
        if ($data === null) {
            $data = array_merge(
                $this->request->getPostData(),
                $this->request->getJsonData()
            );
        }
        
        $validator = $this->container->resolve('Validator');
        return $validator->validate($data, $rules);
    }
    
    /**
     * Get authenticated user from request
     *
     * @return array|null Authenticated user data or null if not authenticated
     */
    protected function getAuthenticatedUser(): ?array
    {
        return $this->request->getUser();
    }
    
    /**
     * Check if user is authenticated
     *
     * @return bool True if authenticated, false otherwise
     */
    protected function isAuthenticated(): bool
    {
        return $this->getAuthenticatedUser() !== null;
    }
    
    /**
     * Require authentication - throw exception if not authenticated
     *
     * @throws AuthenticationException If user is not authenticated
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            throw new AuthenticationException('Authentication required');
        }
    }
    
    /**
     * Check if user has required role
     *
     * @param string $role Required role
     * @return bool True if user has role, false otherwise
     */
    protected function hasRole(string $role): bool
    {
        $userData = $this->getAuthenticatedUser();
        if (!$userData) {
            return false;
        }
        
        $userModel = $this->container->resolve(\Models\User::class);
        return $userModel->hasRole($userData, $role);
    }
    
    /**
     * Require specific role - throw exception if user doesn't have role
     *
     * @param string $role Required role
     * @throws AuthorizationException If user doesn't have required role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            throw new AuthorizationException("Role '{$role}' required");
        }
    }
    
    /**
     * Get route parameter value
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    protected function getRouteParam(string $name, $default = null)
    {
        return $this->request->getRouteParameter($name, $default);
    }
    
    /**
     * Get query parameter value
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value
     */
    protected function getQueryParam(string $name, $default = null)
    {
        return $this->request->getQueryParameter($name, $default);
    }
    
    /**
     * Get POST data value
     *
     * @param string $name Field name
     * @param mixed $default Default value if field not found
     * @return mixed Field value
     */
    protected function getPostData(string $name = null, $default = null)
    {
        return $this->request->getPostData($name, $default);
    }
    
    /**
     * Get JSON data from request body
     *
     * @return array JSON data as associative array
     */
    protected function getJsonData(): array
    {
        return $this->request->getJsonData();
    }
    
    /**
     * Log activity for audit trail
     *
     * @param string $action Action performed
     * @param array $context Additional context data
     */
    protected function logActivity(string $action, array $context = []): void
    {
        try {
            $auditLogService = $this->container->resolve(\Services\AuditLogService::class);
            $userData = $this->getAuthenticatedUser();
            
            $userId = $userData ? $userData['id'] : null;
            $userRole = $userData ? $userData['role'] : null;
            
            $auditLogService->log($action, $context, $userId, $userRole);
        } catch (\Exception $e) {
            // Fallback to basic logging if audit service fails
            $logger = $this->container->resolve('Logger');
            $userData = $this->getAuthenticatedUser();
            
            $logger->info("User action: {$action}", [
                'user_id' => $userData ? $userData['id'] : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'context' => $context
            ]);
        }
    }
    
    /**
     * Sanitize input string
     *
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    protected function sanitizeInput(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    
    /**
     * Sanitize array of inputs
     *
     * @param array $data Data to sanitize
     * @return array Sanitized data
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Handle exceptions and convert to appropriate response
     *
     * @param \Throwable $e Exception to handle
     * @return Response Error response
     */
    protected function handleException(\Throwable $e): Response
    {
        $logger = $this->container->resolve('Logger');
        
        // Log the exception
        $logger->error('Controller exception: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Return appropriate response based on exception type
        if ($e instanceof ValidationException) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        }
        
        if ($e instanceof AuthenticationException) {
            return $this->error($e->getMessage(), 401);
        }
        
        if ($e instanceof AuthorizationException) {
            return $this->error($e->getMessage(), 403);
        }
        
        if ($e instanceof NotFoundException) {
            return $this->error($e->getMessage(), 404);
        }
        
        // Generic server error for unexpected exceptions
        return $this->error('Internal server error', 500);
    }
}
