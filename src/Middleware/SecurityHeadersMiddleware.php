<?php

namespace Middleware;

use Core\Request;
use Core\Response;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to all responses to protect
 * against common web vulnerabilities (XSS, clickjacking, etc.).
 * 
 * Validates: Requirements 12.4
 */
class SecurityHeadersMiddleware
{
    private array $config;
    
    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/security.php';
    }
    
    /**
     * Handle the request
     * 
     * Note: This middleware doesn't block requests, it only adds headers
     * to responses. The actual header addition happens in applyHeaders().
     *
     * @param Request $request
     * @return Response|null Always returns null to continue
     */
    public function handle(Request $request): ?Response
    {
        // This middleware doesn't block requests
        // Headers are applied to the response after controller execution
        return null;
    }
    
    /**
     * Apply security headers to response
     * 
     * This method should be called after controller execution
     * to add security headers to the response.
     *
     * @param Response $response Response to add headers to
     * @return Response Response with security headers
     */
    public function applyHeaders(Response $response): Response
    {
        $headers = $this->config['headers'] ?? [];
        
        // X-Frame-Options: Prevent clickjacking
        if (!empty($headers['x_frame_options'])) {
            $response->setHeader('X-Frame-Options', $headers['x_frame_options']);
        }
        
        // X-Content-Type-Options: Prevent MIME sniffing
        if (!empty($headers['x_content_type_options'])) {
            $response->setHeader('X-Content-Type-Options', $headers['x_content_type_options']);
        }
        
        // X-XSS-Protection: Enable browser XSS protection
        if (!empty($headers['x_xss_protection'])) {
            $response->setHeader('X-XSS-Protection', $headers['x_xss_protection']);
        }
        
        // Strict-Transport-Security: Force HTTPS
        if (!empty($headers['strict_transport_security'])) {
            $response->setHeader('Strict-Transport-Security', $headers['strict_transport_security']);
        }
        
        // Referrer-Policy: Control referrer information
        if (!empty($headers['referrer_policy'])) {
            $response->setHeader('Referrer-Policy', $headers['referrer_policy']);
        }
        
        // Permissions-Policy: Control browser features
        if (!empty($headers['permissions_policy'])) {
            $response->setHeader('Permissions-Policy', $headers['permissions_policy']);
        }
        
        // Content-Security-Policy: Prevent XSS and injection attacks
        if ($this->config['xss']['content_security_policy'] ?? true) {
            $csp = $this->buildContentSecurityPolicy();
            if ($csp) {
                $response->setHeader('Content-Security-Policy', $csp);
            }
        }
        
        return $response;
    }
    
    /**
     * Build Content Security Policy header value
     *
     * @return string CSP header value
     */
    private function buildContentSecurityPolicy(): string
    {
        $directives = $this->config['xss']['csp_directives'] ?? [];
        
        if (empty($directives)) {
            return '';
        }
        
        $cspParts = [];
        foreach ($directives as $directive => $value) {
            $cspParts[] = "{$directive} {$value}";
        }
        
        return implode('; ', $cspParts);
    }
    
    /**
     * Static method to apply headers to any response
     *
     * @param Response $response Response to add headers to
     * @return Response Response with security headers
     */
    public static function apply(Response $response): Response
    {
        $middleware = new self();
        return $middleware->applyHeaders($response);
    }
}
