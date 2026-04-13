<?php

/**
 * Single Entry Point for HRIS MVC Application
 * 
 * This file serves as the centralized entry point for all HTTP requests,
 * implementing the front controller pattern with routing and middleware support.
 */

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

// ========================================
// REQUEST ID TRACKING (ZERO COST!)
// ========================================
// Generate or use existing request ID for tracing requests through logs
$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid('req_', true);
$_SERVER['HTTP_X_REQUEST_ID'] = $requestId;

// Add to response headers so clients can reference it
header('X-Request-ID: ' . $requestId);

// Log request start
error_log("[{$requestId}] Request started: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']}");

// Define application root
define('APP_ROOT', dirname(__DIR__));

// Load framework bootstrap
require_once APP_ROOT . '/src/bootstrap.php';

use Core\Router;
use Core\Request;
use Core\Response;
use Core\Container;

try {
    // Get container instance
    $container = Container::getInstance();
    
    // Get router and request instances
    $router = $container->resolve(Router::class);
    $request = $container->resolve(Request::class);
    
    // Debug logging (remove in production)
    error_log("MVC Debug - Request URI: " . $request->getUri());
    error_log("MVC Debug - Request Method: " . $request->getMethod());
    
    // Load route definitions
    $routeLoader = require APP_ROOT . '/config/routes.php';
    $routeLoader($router);
    
    // Debug: Check if profile route exists
    if ($request->getUri() === '/api/employees/profile') {
        error_log("MVC Debug - Looking for profile route...");
        $allRoutes = $router->getRoutes();
        error_log("MVC Debug - Total routes: " . count($allRoutes));
        
        $foundRoutes = [];
        foreach ($allRoutes as $route) {
            if ($route['method'] === 'GET' && strpos($route['pattern'], 'profile') !== false) {
                $foundRoutes[] = $route['pattern'];
                error_log("MVC Debug - Found GET profile route: " . $route['pattern']);
            }
        }
        
        if (empty($foundRoutes)) {
            error_log("MVC Debug - NO PROFILE ROUTES FOUND!");
        }
    }
    
    // Match request to route
    $route = $router->match($request->getMethod(), $request->getUri());
    
    // Debug: If no match for profile, show why
    if ($route === null && $request->getUri() === '/api/employees/profile') {
        error_log("MVC Debug - ROUTE MATCH FAILED for /api/employees/profile");
        error_log("MVC Debug - Request Method: " . $request->getMethod());
        error_log("MVC Debug - Request URI: " . $request->getUri());
    }
    
    if ($route === null) {
        // Handle 404 - Route not found
        $response = new Response();
        
        // Check if this is an API request
        if (substr($request->getUri(), 0, 5) === '/api/') {
            $response->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error' => 'ROUTE_NOT_FOUND'
            ], 404);
        } else {
            // For web requests, show 404 page
            $response->setStatusCode(404)
                     ->setContent('Page not found');
        }
        
        $response->send();
        exit;
    }
    
    // Dispatch route through middleware pipeline
    $response = $router->dispatch($route, $request);
    
    // Send response
    $response->send();
    
} catch (Throwable $e) {
    // Handle uncaught exceptions
    error_log("Uncaught exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    
    $response = new Response();
    
    // Check if this is an API request
    $isApi = isset($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], 0, 5) === '/api/';
    
    if ($isApi) {
        $response->json([
            'success' => false,
            'message' => 'Internal server error',
            'error' => 'INTERNAL_ERROR'
        ], 500);
    } else {
        $response->setStatusCode(500)
                 ->setContent('Internal Server Error');
    }
    
    $response->send();
}