<?php

/**
 * Bootstrap file for the MVC framework
 * 
 * This file initializes the framework components and sets up the dependency injection container.
 */

// Load autoloader
require_once __DIR__ . '/autoload.php';

// Load helper functions
require_once __DIR__ . '/Config/helpers.php';

// Initialize Sentry error tracking (ZERO COST - free tier!)
// This will gracefully degrade if Sentry SDK is not installed
\Core\SentryIntegration::init();

use Core\Container;
use Core\Router;
use Core\Request;

// Initialize container and register default bindings
$container = Container::getInstance();
$container->registerDefaultBindings();

// Register router as singleton
$container->singleton(Router::class, function () {
    return new Router();
});

// Register request as singleton
$container->singleton(Request::class, function () {
    return new Request();
});

// Register cache as singleton
$container->singleton(\Core\Cache::class, function () {
    return \Core\Cache::getInstance();
});

// Register View/ViewRenderer as singleton
$container->singleton('ViewRenderer', function () {
    return new \Core\View();
});

// Register database connection pool as singleton
$container->singleton(\Core\DatabaseConnectionPool::class, function () {
    return \Core\DatabaseConnectionPool::getInstance();
});

// Register query optimizer as singleton
$container->singleton(\Core\QueryOptimizer::class, function () {
    return \Core\QueryOptimizer::getInstance();
});

// Register route cache as singleton
$container->singleton(\Core\RouteCache::class, function () {
    return \Core\RouteCache::getInstance();
});

// Load configuration if available
if (file_exists(__DIR__ . '/../config/app.php')) {
    $appConfig = require __DIR__ . '/../config/app.php';
    $container->instance('app.config', $appConfig);
}

// Register all Models as singletons
$container->singleton(\Models\User::class);
$container->singleton(\Models\Employee::class);
$container->singleton(\Models\Attendance::class);
$container->singleton(\Models\LeaveRequest::class);
$container->singleton(\Models\PayrollPeriod::class);
$container->singleton(\Models\EmployeeCompensation::class);
$container->singleton(\Models\PayrollRun::class);
$container->singleton(\Models\PayrollLineItem::class);
$container->singleton(\Models\PayrollAdjustment::class);
$container->singleton(\Models\JobPosting::class);
$container->singleton(\Models\Applicant::class);
$container->singleton(\Models\ApplicantEvaluation::class);
$container->singleton(\Models\EmployeeDocument::class);

// Register all Services as singletons
$container->singleton(\Services\AuthService::class);
$container->singleton(\Services\AnnouncementService::class);
$container->singleton(\Services\EmployeeService::class);
$container->singleton(\Services\AttendanceService::class);
$container->singleton(\Services\LeaveService::class);
$container->singleton(\Services\ReportService::class);
$container->singleton(\Services\AuditLogService::class);
$container->singleton(\Services\PayrollService::class);
$container->singleton(\Services\IdempotencyService::class);
$container->singleton(\Services\RecruitmentService::class);
$container->singleton(\Services\DocumentService::class);

// Register all Middleware as singletons
$container->singleton(\Middleware\AuthMiddleware::class);
$container->singleton(\Middleware\RoleMiddleware::class);
$container->singleton(\Middleware\LoggingMiddleware::class);
$container->singleton(\Middleware\InputValidationMiddleware::class);
$container->singleton(\Middleware\CsrfMiddleware::class);
$container->singleton(\Middleware\RateLimitMiddleware::class);
$container->singleton(\Middleware\SecurityHeadersMiddleware::class);

// Register all Controllers (not as singletons - new instance per request)
$container->bind(\Controllers\AuthController::class);
$container->bind(\Controllers\EmployeeController::class);
$container->bind(\Controllers\AttendanceController::class);
$container->bind(\Controllers\LeaveController::class);
$container->bind(\Controllers\DashboardController::class);
$container->bind(\Controllers\ReportController::class);
$container->bind(\Controllers\AnnouncementController::class);
$container->bind(\Controllers\PayrollController::class);
$container->bind(\Controllers\RecruitmentController::class);
$container->bind(\Controllers\DocumentController::class);
$container->bind(\Controllers\HealthController::class);
$container->bind(\Controllers\SystemHealthController::class);

// Register utility classes as singletons
$container->singleton(\Core\StructuredLogger::class, function() {
    return new \Core\StructuredLogger();
});

return $container;
