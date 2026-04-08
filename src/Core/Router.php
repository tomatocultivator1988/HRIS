<?php

namespace Core;

/**
 * Router Class - Handles URL pattern matching and route dispatching
 * 
 * This class provides centralized routing functionality for the MVC framework,
 * supporting RESTful URL patterns and automatic controller/action mapping.
 */
class Router
{
    private array $routes = [];
    private array $middleware = [];
    private ?RouteCache $routeCache = null;
    
    public function __construct()
    {
        $this->routeCache = RouteCache::getInstance();
        
        // Load cached routes if available
        $cachedRoutes = $this->routeCache->load();
        if ($cachedRoutes !== null) {
            $this->routes = $cachedRoutes;
        }
    }
    
    /**
     * Add a route to the routing table
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $pattern URL pattern with optional parameters (e.g., '/users/{id}')
     * @param string $handler Controller@method format (e.g., 'UserController@show')
     * @param array $middleware Optional middleware to apply to this route
     */
    public function addRoute(string $method, string $pattern, string $handler, array $middleware = []): void
    {
        $route = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
            'regex' => $this->convertPatternToRegex($pattern)
        ];

        foreach ($this->routes as $index => $existingRoute) {
            if ($existingRoute['method'] === $route['method'] && $existingRoute['pattern'] === $route['pattern']) {
                $this->routes[$index] = $route;
                return;
            }
        }

        $this->routes[] = $route;
    }
    
    /**
     * Cache all routes for production
     *
     * @return bool Success status
     */
    public function cacheRoutes(): bool
    {
        return $this->routeCache->cache($this->routes);
    }
    
    /**
     * Match a request to a route
     *
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return Route|null Matched route or null if no match found
     */
    public function match(string $method, string $uri): ?Route
    {
        $method = strtoupper($method);
        $uri = $this->cleanUri($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['regex'], $uri, $matches)) {
                $parameters = $this->extractParameters($route['pattern'], $matches);
                
                return new Route(
                    $route['handler'],
                    $parameters,
                    $route['middleware']
                );
            }
        }
        
        return null;
    }
    
    /**
     * Dispatch a matched route
     *
     * @param Route $route The matched route
     * @param Request $request The HTTP request
     * @return Response The response from the controller
     * @throws \Exception If controller or method not found
     */
    public function dispatch(Route $route, Request $request): Response
    {
        // Execute middleware pipeline
        foreach ($route->getMiddleware() as $middlewareSpec) {
            $response = $this->executeMiddleware($middlewareSpec, $request);
            if ($response !== null) {
                // Apply security headers before returning error response
                $response = \Middleware\SecurityHeadersMiddleware::apply($response);
                return $response;
            }
        }
        
        // Parse controller and method
        [$controllerClass, $method] = explode('@', $route->getHandler());
        
        // Add Controllers namespace if not present
        if (strpos($controllerClass, '\\') === false) {
            $controllerClass = 'Controllers\\' . $controllerClass;
        }
        
        // Instantiate controller with dependency injection
        $container = Container::getInstance();
        $controller = $container->resolve($controllerClass);
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in controller {$controllerClass}");
        }
        
        // Inject request into controller
        $controller->setRequest($request);
        
        // Inject route parameters into request
        $request->setRouteParameters($route->getParameters());
        
        // Call controller method
        $response = $controller->$method($request);
        
        // Apply security headers to response
        $response = \Middleware\SecurityHeadersMiddleware::apply($response);
        
        // Apply rate limit headers if available
        $rateLimitInfo = $request->getRateLimitInfo();
        if ($rateLimitInfo) {
            $response = \Middleware\RateLimitMiddleware::applyHeaders($response, $rateLimitInfo);
        }
        
        // Log response if logging middleware was used
        $this->logResponseIfNeeded($request, $response);
        
        return $response;
    }
    
    /**
     * Execute middleware with support for parameterized middleware
     *
     * @param string $middlewareSpec Middleware specification (e.g., 'auth' or 'role:admin')
     * @param Request $request The HTTP request
     * @return Response|null Response to halt execution, null to continue
     */
    private function executeMiddleware(string $middlewareSpec, Request $request): ?Response
    {
        // Parse middleware specification
        if (strpos($middlewareSpec, ':') !== false) {
            [$middlewareName, $parameter] = explode(':', $middlewareSpec, 2);
        } else {
            $middlewareName = $middlewareSpec;
            $parameter = null;
        }
        
        // Map middleware names to classes
        $middlewareMap = [
            'auth' => 'Middleware\\AuthMiddleware',
            'role' => 'Middleware\\RoleMiddleware',
            'logging' => 'Middleware\\LoggingMiddleware',
            'input_validation' => 'Middleware\\InputValidationMiddleware',
            'csrf' => 'Middleware\\CsrfMiddleware',
            'rate_limit' => 'Middleware\\RateLimitMiddleware',
            'security_headers' => 'Middleware\\SecurityHeadersMiddleware'
        ];
        
        if (!isset($middlewareMap[$middlewareName])) {
            throw new \Exception("Unknown middleware: {$middlewareName}");
        }
        
        $middlewareClass = $middlewareMap[$middlewareName];
        
        // Instantiate middleware with parameter if needed
        if ($parameter !== null) {
            if ($middlewareClass === 'Middleware\\RoleMiddleware') {
                // For role middleware, pass the role as a parameter
                $middleware = \Middleware\RoleMiddleware::role($parameter);
            } else {
                // For other middleware that might accept parameters
                $middleware = new $middlewareClass($parameter);
            }
        } else {
            $middleware = new $middlewareClass();
        }
        
        return $middleware->handle($request);
    }
    
    /**
     * Log response if logging middleware was executed
     *
     * @param Request $request
     * @param Response $response
     */
    private function logResponseIfNeeded(Request $request, Response $response): void
    {
        if ($request->getStartTime() !== null) {
            try {
                $loggingMiddleware = new \Middleware\LoggingMiddleware();
                $loggingMiddleware->logResponse($request, $response);
            } catch (Exception $e) {
                // Fail silently
                error_log("Response logging error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Convert URL pattern to regex for matching
     *
     * @param string $pattern URL pattern with {param} placeholders
     * @return string Regex pattern
     */
    private function convertPatternToRegex(string $pattern): string
    {
        // Escape special regex characters except {}
        $pattern = preg_quote($pattern, '/');
        
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^\/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Extract parameters from matched route
     *
     * @param string $pattern Original URL pattern
     * @param array $matches Regex matches
     * @return array Parameter name => value pairs
     */
    private function extractParameters(string $pattern, array $matches): array
    {
        $parameters = [];
        
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }
        
        return $parameters;
    }
    
    /**
     * Clean URI by removing query string and trailing slashes
     *
     * @param string $uri Raw URI
     * @return string Cleaned URI
     */
    private function cleanUri(string $uri): string
    {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH) ?? $uri;
        
        // Strip base path if present (for XAMPP /HRIS/ deployment)
        $basePath = '/HRIS';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            if (empty($uri)) {
                $uri = '/';
            }
        }
        
        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        return $uri;
    }
    
    /**
     * Get all registered routes
     *
     * @return array All routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}


/**
 * Route Class - Represents a matched route
 */
class Route
{
    private string $handler;
    private array $parameters;
    private array $middleware;
    
    public function __construct(string $handler, array $parameters = [], array $middleware = [])
    {
        $this->handler = $handler;
        $this->parameters = $parameters;
        $this->middleware = $middleware;
    }
    
    public function getHandler(): string
    {
        return $this->handler;
    }
    
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
