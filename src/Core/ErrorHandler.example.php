<?php

/**
 * ErrorHandler Usage Examples
 * 
 * This file demonstrates how to use the centralized error handling system
 * in the HRIS application.
 */

require_once __DIR__ . '/../autoload.php';

use Core\ErrorHandler;
use Core\ValidationException;
use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\DatabaseException;
use Core\BusinessLogicException;
use Core\NotFoundException;

// ============================================================================
// Example 1: Basic Setup
// ============================================================================

// Create an error handler instance
$errorHandler = new ErrorHandler(
    __DIR__ . '/../../logs/app.log',  // Log file path
    false                               // Debug mode (false for production)
);

// ============================================================================
// Example 2: Handling Validation Errors
// ============================================================================

try {
    // Simulate validation failure
    $errors = [
        'email' => 'Invalid email format',
        'password' => 'Password must be at least 8 characters'
    ];
    
    throw new ValidationException('Validation failed', $errors);
    
} catch (ValidationException $e) {
    // Handle validation error specifically
    $response = $errorHandler->handleValidationError($e);
    
    // Send response to client
    $response->send();
    
    // Response will be:
    // {
    //   "success": false,
    //   "error": "Validation Error",
    //   "message": "The provided data is invalid. Please check your input and try again.",
    //   "errors": {
    //     "email": "Invalid email format",
    //     "password": "Password must be at least 8 characters"
    //   },
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 3: Handling Authentication Errors
// ============================================================================

try {
    // Simulate authentication failure
    throw new AuthenticationException('Invalid credentials provided');
    
} catch (AuthenticationException $e) {
    // Handle auth error
    $response = $errorHandler->handleAuthError($e);
    $response->send();
    
    // Response will have HTTP 401 status code
    // {
    //   "success": false,
    //   "error": "Authentication Required",
    //   "message": "Authentication is required. Please log in to continue.",
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 4: Handling Authorization Errors
// ============================================================================

try {
    // Simulate authorization failure
    throw new AuthorizationException('User does not have admin role');
    
} catch (AuthorizationException $e) {
    $response = $errorHandler->handleAuthError($e);
    $response->send();
    
    // Response will have HTTP 403 status code
    // {
    //   "success": false,
    //   "error": "Access Denied",
    //   "message": "You do not have permission to perform this action.",
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 5: Handling Database Errors
// ============================================================================

try {
    // Simulate database error
    throw new DatabaseException('Connection to database failed');
    
} catch (DatabaseException $e) {
    $response = $errorHandler->handleException($e);
    $response->send();
    
    // Response will have HTTP 500 status code
    // Technical details are hidden from user
    // {
    //   "success": false,
    //   "error": "Internal Server Error",
    //   "message": "A database error occurred. Please try again later.",
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 6: Handling Business Logic Errors
// ============================================================================

try {
    // Simulate business rule violation
    throw new BusinessLogicException('Cannot approve leave request: insufficient balance');
    
} catch (BusinessLogicException $e) {
    $response = $errorHandler->handleException($e);
    $response->send();
    
    // Response will have HTTP 400 status code
    // Business logic messages are safe to show users
    // {
    //   "success": false,
    //   "error": "Bad Request",
    //   "message": "Cannot approve leave request: insufficient balance",
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 7: Handling Not Found Errors
// ============================================================================

try {
    // Simulate resource not found
    throw new NotFoundException('Employee with ID 12345 not found');
    
} catch (NotFoundException $e) {
    $response = $errorHandler->handleException($e);
    $response->send();
    
    // Response will have HTTP 404 status code
    // {
    //   "success": false,
    //   "error": "Not Found",
    //   "message": "The requested resource was not found.",
    //   "timestamp": "2024-01-15 10:30:45"
    // }
}

// ============================================================================
// Example 8: Using Exception Context
// ============================================================================

try {
    // Add context data to exception
    $context = [
        'user_id' => 123,
        'action' => 'delete_employee',
        'employee_id' => 456
    ];
    
    throw new AuthorizationException(
        'User attempted to delete employee without permission',
        0,
        null,
        $context
    );
    
} catch (AuthorizationException $e) {
    // Context will be logged but not exposed to user
    $errorHandler->logError($e);
    
    $response = $errorHandler->handleAuthError($e);
    $response->send();
}

// ============================================================================
// Example 9: Debug Mode (Development Only)
// ============================================================================

// Enable debug mode for development
$errorHandler->setDebugMode(true);

try {
    throw new DatabaseException('Query failed: SELECT * FROM invalid_table');
    
} catch (DatabaseException $e) {
    $response = $errorHandler->handleException($e);
    $response->send();
    
    // In debug mode, response includes technical details:
    // {
    //   "success": false,
    //   "error": "Internal Server Error",
    //   "message": "A database error occurred. Please try again later.",
    //   "timestamp": "2024-01-15 10:30:45",
    //   "debug": {
    //     "exception": "Core\\DatabaseException",
    //     "message": "Query failed: SELECT * FROM invalid_table",
    //     "file": "/path/to/file.php",
    //     "line": 123,
    //     "trace": [...]
    //   }
    // }
}

// ============================================================================
// Example 10: Integration with Controllers
// ============================================================================

// In a controller:
class EmployeeController extends Controller
{
    private ErrorHandler $errorHandler;
    
    public function __construct()
    {
        $this->errorHandler = new ErrorHandler(
            __DIR__ . '/../../logs/app.log',
            config('app.debug', false)
        );
    }
    
    public function show(int $id): Response
    {
        try {
            // Attempt to find employee
            $employee = $this->employeeService->findById($id);
            
            if (!$employee) {
                throw new NotFoundException("Employee with ID {$id} not found");
            }
            
            return $this->json(['success' => true, 'data' => $employee]);
            
        } catch (NotFoundException $e) {
            return $this->errorHandler->handleException($e);
        } catch (\Exception $e) {
            return $this->errorHandler->handleException($e);
        }
    }
    
    public function create(Request $request): Response
    {
        try {
            // Validate input
            $validator = new EmployeeValidator();
            $result = $validator->validate($request->all());
            
            if (!$result->isValid()) {
                throw new ValidationException('Validation failed', $result->getErrors());
            }
            
            // Create employee
            $employee = $this->employeeService->create($result->getData());
            
            return $this->json(['success' => true, 'data' => $employee], 201);
            
        } catch (ValidationException $e) {
            return $this->errorHandler->handleValidationError($e);
        } catch (\Exception $e) {
            return $this->errorHandler->handleException($e);
        }
    }
}

// ============================================================================
// Example 11: Global Error Handler Setup
// ============================================================================

// In public/index.php or bootstrap.php:

// Set up global error handler
$errorHandler = new ErrorHandler(
    __DIR__ . '/../logs/app.log',
    config('app.debug', false)
);

// Register exception handler
set_exception_handler(function($exception) use ($errorHandler) {
    $response = $errorHandler->handleException($exception);
    $response->send();
});

// Register error handler
set_error_handler(function($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// ============================================================================
// HTTP Status Code Reference
// ============================================================================

/*
 * Exception Type              -> HTTP Status Code
 * -----------------------------------------------
 * ValidationException         -> 422 Unprocessable Entity
 * AuthenticationException     -> 401 Unauthorized
 * AuthorizationException      -> 403 Forbidden
 * NotFoundException           -> 404 Not Found
 * BusinessLogicException      -> 400 Bad Request
 * DatabaseException           -> 500 Internal Server Error
 * Standard \Exception         -> 500 Internal Server Error
 * \InvalidArgumentException   -> 400 Bad Request
 */
