<?php

namespace Core;

use Throwable;
use Exception;

/**
 * Base HRIS Exception
 * 
 * All custom exceptions in the HRIS system extend from this base class.
 * This allows for centralized exception handling and consistent error responses.
 */
abstract class HRISException extends Exception
{
    protected array $context = [];
    protected int $httpStatusCode = 500;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context data for the exception
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the HTTP status code for this exception
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * Get user-friendly error message
     * Override this in child classes to provide custom user messages
     */
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}

/**
 * ValidationException
 * 
 * Thrown when input validation fails.
 * HTTP Status: 422 Unprocessable Entity
 */
class ValidationException extends HRISException
{
    protected int $httpStatusCode = 422;
    private array $errors = [];

    public function __construct(string|array $message = "Validation failed", array $errors = [], int $code = 0, ?Throwable $previous = null)
    {
        if (is_array($message)) {
            $errors = $message;
            $message = "Validation failed";
        }

        $this->errors = $errors;
        parent::__construct($message, $code, $previous, ['errors' => $errors]);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getUserMessage(): string
    {
        return "The provided data is invalid. Please check your input and try again.";
    }
}

/**
 * AuthenticationException
 * 
 * Thrown when authentication fails or is required.
 * HTTP Status: 401 Unauthorized
 */
class AuthenticationException extends HRISException
{
    protected int $httpStatusCode = 401;

    public function getUserMessage(): string
    {
        return "Authentication is required. Please log in to continue.";
    }
}

/**
 * AuthorizationException
 * 
 * Thrown when a user lacks permission to perform an action.
 * HTTP Status: 403 Forbidden
 */
class AuthorizationException extends HRISException
{
    protected int $httpStatusCode = 403;

    public function getUserMessage(): string
    {
        return "You do not have permission to perform this action.";
    }
}

/**
 * DatabaseException
 * 
 * Thrown when database operations fail.
 * HTTP Status: 500 Internal Server Error
 */
class DatabaseException extends HRISException
{
    protected int $httpStatusCode = 500;

    public function getUserMessage(): string
    {
        return "A database error occurred. Please try again later.";
    }
}

/**
 * BusinessLogicException
 * 
 * Thrown when business rules are violated.
 * HTTP Status: 400 Bad Request
 */
class BusinessLogicException extends HRISException
{
    protected int $httpStatusCode = 400;

    public function getUserMessage(): string
    {
        // Business logic exceptions often have specific messages that are safe to show users
        return $this->getMessage();
    }
}

/**
 * NotFoundException
 * 
 * Thrown when a requested resource is not found.
 * HTTP Status: 404 Not Found
 */
class NotFoundException extends HRISException
{
    protected int $httpStatusCode = 404;

    public function getUserMessage(): string
    {
        return "The requested resource was not found.";
    }
}

/**
 * ErrorHandler
 * 
 * Centralized error handling system for the HRIS application.
 * Provides consistent error logging, user message formatting, and HTTP status code mapping.
 */
class ErrorHandler
{
    private string $logFile;
    private bool $debugMode;
    private array $errorCounts = [];

    public function __construct(string $logFile = null, bool $debugMode = false)
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/app.log';
        $this->debugMode = $debugMode;
    }

    /**
     * Handle any exception and return a formatted Response
     * 
     * @param Throwable $e The exception to handle
     * @return Response Formatted error response
     */
    public function handleException(Throwable $e): Response
    {
        // Log the error with full details
        $this->logError($e);

        // Determine HTTP status code
        $statusCode = $this->getHttpStatusCode($e);

        // Build error response
        $errorData = $this->buildErrorResponse($e, $statusCode);

        // Create and return response
        $response = new Response();
        return $response->json($errorData, $statusCode);
    }

    /**
     * Handle validation errors specifically
     * 
     * @param ValidationException $e The validation exception
     * @return Response Formatted validation error response
     */
    public function handleValidationError(ValidationException $e): Response
    {
        $this->logError($e, ['type' => 'validation']);

        $response = new Response();
        return $response->json([
            'success' => false,
            'error' => 'Validation Error',
            'message' => $e->getUserMessage(),
            'errors' => $e->getErrors(),
            'timestamp' => date('Y-m-d H:i:s')
        ], 422);
    }

    /**
     * Handle authentication/authorization errors
     * 
     * @param HRISException $e The auth exception (Authentication or Authorization)
     * @return Response Formatted auth error response
     */
    public function handleAuthError(HRISException $e): Response
    {
        $this->logError($e, ['type' => 'authentication']);

        $statusCode = $e->getHttpStatusCode();
        
        $response = new Response();
        return $response->json([
            'success' => false,
            'error' => $e instanceof AuthenticationException ? 'Authentication Required' : 'Access Denied',
            'message' => $e->getUserMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ], $statusCode);
    }

    /**
     * Log error details for debugging
     * 
     * @param Throwable $e The exception to log
     * @param array $context Additional context data
     */
    public function logError(Throwable $e, array $context = []): void
    {
        $errorType = get_class($e);
        
        // Track error counts for monitoring
        if (!isset($this->errorCounts[$errorType])) {
            $this->errorCounts[$errorType] = 0;
        }
        $this->errorCounts[$errorType]++;

        // Build log entry
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $errorType,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $this->debugMode ? $e->getTraceAsString() : 'Stack trace hidden (debug mode off)',
            'context' => array_merge($context, $this->getExceptionContext($e))
        ];

        // Write to log file
        $this->writeLog($logEntry);
        
        // Send to Sentry if available (ZERO COST - free tier!)
        try {
            SentryIntegration::captureException($e, $context);
        } catch (\Exception $sentryError) {
            // Silently fail - don't let Sentry errors break the application
            error_log('Sentry error: ' . $sentryError->getMessage());
        }
    }

    /**
     * Get HTTP status code for an exception
     * 
     * @param Throwable $e The exception
     * @return int HTTP status code
     */
    private function getHttpStatusCode(Throwable $e): int
    {
        if ($e instanceof HRISException) {
            return $e->getHttpStatusCode();
        }

        // Default status codes for standard exceptions
        if ($e instanceof \InvalidArgumentException) {
            return 400;
        }

        // Default to 500 for unknown exceptions
        return 500;
    }

    /**
     * Build error response array
     * 
     * @param Throwable $e The exception
     * @param int $statusCode HTTP status code
     * @return array Error response data
     */
    private function buildErrorResponse(Throwable $e, int $statusCode): array
    {
        $response = [
            'success' => false,
            'error' => $this->getErrorTitle($statusCode),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Add user-friendly message
        if ($e instanceof HRISException) {
            $response['message'] = $e->getUserMessage();
        } else {
            $response['message'] = $this->getGenericErrorMessage($statusCode);
        }

        // Add validation errors if applicable
        if ($e instanceof ValidationException) {
            $response['errors'] = $e->getErrors();
        }

        // In debug mode, add technical details
        if ($this->debugMode) {
            $response['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];
        }

        return $response;
    }

    /**
     * Get error title based on HTTP status code
     * 
     * @param int $statusCode HTTP status code
     * @return string Error title
     */
    private function getErrorTitle(int $statusCode): string
    {
        $titles = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Validation Error',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];

        return $titles[$statusCode] ?? 'Error';
    }

    /**
     * Get generic error message based on HTTP status code
     * 
     * @param int $statusCode HTTP status code
     * @return string Generic error message
     */
    private function getGenericErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'The request could not be processed due to invalid data.',
            401 => 'Authentication is required to access this resource.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource could not be found.',
            422 => 'The provided data failed validation.',
            500 => 'An internal server error occurred. Please try again later.',
            503 => 'The service is temporarily unavailable. Please try again later.'
        ];

        return $messages[$statusCode] ?? 'An error occurred while processing your request.';
    }

    /**
     * Get exception context if available
     * 
     * @param Throwable $e The exception
     * @return array Context data
     */
    private function getExceptionContext(Throwable $e): array
    {
        if ($e instanceof HRISException) {
            return $e->getContext();
        }

        return [];
    }

    /**
     * Write log entry to file
     * 
     * @param array $logEntry Log entry data
     */
    private function writeLog(array $logEntry): void
    {
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Format log entry
        $logLine = sprintf(
            "[%s] %s: %s in %s:%d\n",
            $logEntry['timestamp'],
            $logEntry['type'],
            $logEntry['message'],
            $logEntry['file'],
            $logEntry['line']
        );

        // Add context if present
        if (!empty($logEntry['context'])) {
            $logLine .= "Context: " . json_encode($logEntry['context']) . "\n";
        }

        // Add trace in debug mode
        if ($this->debugMode && isset($logEntry['trace'])) {
            $logLine .= "Stack trace:\n" . $logEntry['trace'] . "\n";
        }

        $logLine .= str_repeat('-', 80) . "\n";

        // Write to file
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get error statistics
     * 
     * @return array Error counts by type
     */
    public function getErrorStats(): array
    {
        return $this->errorCounts;
    }

    /**
     * Set debug mode
     * 
     * @param bool $enabled Enable or disable debug mode
     */
    public function setDebugMode(bool $enabled): void
    {
        $this->debugMode = $enabled;
    }

    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug mode is enabled
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }
}
