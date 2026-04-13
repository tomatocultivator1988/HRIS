<?php

namespace Core;

/**
 * SentryIntegration - Simple Sentry error tracking wrapper
 * 
 * This provides a simple interface to Sentry error tracking.
 * Uses Sentry's free tier (5,000 errors/month, 7-day retention).
 * 
 * ZERO COST - Uses Sentry free tier!
 * 
 * Setup Instructions:
 * 1. Sign up at https://sentry.io (free tier)
 * 2. Create a new PHP project
 * 3. Copy your DSN to .env file: SENTRY_DSN=your_dsn_here
 * 4. Download Sentry SDK: composer require sentry/sentry (or manual download)
 * 
 * If Sentry SDK is not installed, this class will gracefully degrade
 * and just log errors locally without breaking the application.
 */
class SentryIntegration
{
    private static bool $initialized = false;
    private static bool $available = false;
    
    /**
     * Initialize Sentry integration
     * 
     * @return bool True if Sentry is available and initialized
     */
    public static function init(): bool
    {
        if (self::$initialized) {
            return self::$available;
        }
        
        self::$initialized = true;
        
        // Check if Sentry SDK is available
        if (!function_exists('\\Sentry\\init')) {
            error_log('Sentry SDK not installed. Error tracking will use local logs only.');
            self::$available = false;
            return false;
        }
        
        // Get DSN from environment
        $dsn = env('SENTRY_DSN', '');
        if (empty($dsn)) {
            error_log('SENTRY_DSN not configured. Error tracking will use local logs only.');
            self::$available = false;
            return false;
        }
        
        try {
            \Sentry\init([
                'dsn' => $dsn,
                'environment' => env('APP_ENV', 'production'),
                'traces_sample_rate' => 0.2, // 20% of requests for performance monitoring
                'profiles_sample_rate' => 0.2, // 20% of requests for profiling
                'send_default_pii' => false, // Don't send personally identifiable information
                'max_breadcrumbs' => 50,
                'attach_stacktrace' => true,
                'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
                    // Filter out sensitive data
                    return self::filterSensitiveData($event);
                }
            ]);
            
            self::$available = true;
            error_log('Sentry error tracking initialized successfully');
            return true;
            
        } catch (\Exception $e) {
            error_log('Failed to initialize Sentry: ' . $e->getMessage());
            self::$available = false;
            return false;
        }
    }
    
    /**
     * Capture an exception
     *
     * @param \Throwable $exception Exception to capture
     * @param array $context Additional context
     * @return string|null Event ID if sent to Sentry
     */
    public static function captureException(\Throwable $exception, array $context = []): ?string
    {
        // Always log locally
        error_log('Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        
        if (!self::$available && !self::init()) {
            return null;
        }
        
        try {
            // Add context
            if (!empty($context)) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($context): void {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }
            
            // Capture exception
            $eventId = \Sentry\captureException($exception);
            return $eventId;
            
        } catch (\Exception $e) {
            error_log('Failed to send exception to Sentry: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Capture a message
     *
     * @param string $message Message to capture
     * @param string $level Severity level (debug, info, warning, error, fatal)
     * @param array $context Additional context
     * @return string|null Event ID if sent to Sentry
     */
    public static function captureMessage(string $message, string $level = 'error', array $context = []): ?string
    {
        // Always log locally
        error_log("[$level] $message");
        
        if (!self::$available && !self::init()) {
            return null;
        }
        
        try {
            // Map level to Sentry severity
            $severity = match($level) {
                'debug' => \Sentry\Severity::debug(),
                'info' => \Sentry\Severity::info(),
                'warning' => \Sentry\Severity::warning(),
                'error' => \Sentry\Severity::error(),
                'fatal' => \Sentry\Severity::fatal(),
                default => \Sentry\Severity::error()
            };
            
            // Add context
            if (!empty($context)) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($context): void {
                    foreach ($context as $key => $value) {
                        $scope->setContext($key, $value);
                    }
                });
            }
            
            // Capture message
            $eventId = \Sentry\captureMessage($message, $severity);
            return $eventId;
            
        } catch (\Exception $e) {
            error_log('Failed to send message to Sentry: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add breadcrumb for debugging context
     *
     * @param string $message Breadcrumb message
     * @param string $category Category (e.g., 'auth', 'query', 'http')
     * @param array $data Additional data
     */
    public static function addBreadcrumb(string $message, string $category = 'default', array $data = []): void
    {
        if (!self::$available && !self::init()) {
            return;
        }
        
        try {
            \Sentry\addBreadcrumb(
                category: $category,
                message: $message,
                metadata: $data,
                level: \Sentry\Breadcrumb::LEVEL_INFO
            );
        } catch (\Exception $e) {
            // Silently fail - breadcrumbs are not critical
        }
    }
    
    /**
     * Set user context
     *
     * @param string|null $userId User ID
     * @param string|null $email User email
     * @param array $extra Extra user data
     */
    public static function setUser(?string $userId, ?string $email = null, array $extra = []): void
    {
        if (!self::$available && !self::init()) {
            return;
        }
        
        try {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($userId, $email, $extra): void {
                $scope->setUser([
                    'id' => $userId,
                    'email' => $email,
                    ...$extra
                ]);
            });
        } catch (\Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Set custom tags
     *
     * @param array $tags Key-value pairs of tags
     */
    public static function setTags(array $tags): void
    {
        if (!self::$available && !self::init()) {
            return;
        }
        
        try {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($tags): void {
                foreach ($tags as $key => $value) {
                    $scope->setTag($key, (string) $value);
                }
            });
        } catch (\Exception $e) {
            // Silently fail
        }
    }
    
    /**
     * Filter sensitive data from events before sending to Sentry
     *
     * @param \Sentry\Event $event Event to filter
     * @return \Sentry\Event|null Filtered event or null to drop
     */
    private static function filterSensitiveData(\Sentry\Event $event): ?\Sentry\Event
    {
        // List of sensitive keys to redact
        $sensitiveKeys = [
            'password',
            'passwd',
            'secret',
            'api_key',
            'apikey',
            'token',
            'auth',
            'authorization',
            'cookie',
            'session',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
            'social_security'
        ];
        
        // Redact sensitive data from request data
        $request = $event->getRequest();
        if ($request) {
            $data = $request->getData();
            if (is_array($data)) {
                foreach ($sensitiveKeys as $key) {
                    if (isset($data[$key])) {
                        $data[$key] = '[REDACTED]';
                    }
                }
                $request->setData($data);
            }
        }
        
        return $event;
    }
    
    /**
     * Check if Sentry is available and initialized
     *
     * @return bool True if Sentry is available
     */
    public static function isAvailable(): bool
    {
        if (!self::$initialized) {
            self::init();
        }
        return self::$available;
    }
}
