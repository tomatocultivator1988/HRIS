# Security Enhancements Documentation

This document describes the security enhancements implemented in the MVC Architecture Conversion project (Task 7.4).

## Overview

The security enhancements provide comprehensive protection against common web vulnerabilities and ensure secure operation of the HRIS system. These enhancements are implemented as middleware components and services that can be easily integrated into the application.

## Components

### 1. Input Validation and Sanitization Middleware

**File:** `src/Middleware/InputValidationMiddleware.php`

**Purpose:** Validates and sanitizes all user input to prevent injection attacks and ensure data integrity.

**Features:**
- SQL injection pattern detection
- XSS pattern detection and sanitization
- Null byte injection prevention
- Dangerous protocol stripping (javascript:, data:, etc.)
- UTF-8 encoding validation
- Content length validation

**Usage:**
```php
// In routes configuration
$router->addRoute('POST', '/api/employees', 'EmployeeController@create', ['input_validation', 'auth']);
```

**Configuration:** `config/security.php` - `input` section

### 2. CSRF Protection Middleware

**File:** `src/Middleware/CsrfMiddleware.php`

**Purpose:** Protects against Cross-Site Request Forgery attacks by validating CSRF tokens on state-changing requests.

**Features:**
- Automatic token generation and validation
- Token expiry management
- Support for both header and form field tokens
- Automatic bypass for JWT-authenticated API requests

**Usage:**
```php
// Generate token for forms
$csrfToken = CsrfMiddleware::getToken();

// In routes configuration
$router->addRoute('POST', '/employees/create', 'EmployeeController@create', ['csrf', 'auth']);

// In HTML forms
<input type="hidden" name="_token" value="<?php echo $csrfToken; ?>">

// In AJAX requests
headers: {
    'X-CSRF-TOKEN': csrfToken
}
```

**Configuration:** `config/security.php` - `csrf` section

### 3. Security Headers Middleware

**File:** `src/Middleware/SecurityHeadersMiddleware.php`

**Purpose:** Adds security-related HTTP headers to all responses to protect against common web vulnerabilities.

**Headers Applied:**
- `X-Frame-Options`: Prevents clickjacking attacks
- `X-Content-Type-Options`: Prevents MIME sniffing
- `X-XSS-Protection`: Enables browser XSS protection
- `Strict-Transport-Security`: Forces HTTPS connections
- `Referrer-Policy`: Controls referrer information
- `Permissions-Policy`: Controls browser features
- `Content-Security-Policy`: Prevents XSS and injection attacks

**Usage:**
Security headers are automatically applied to all responses by the Router. No explicit configuration needed in routes.

**Configuration:** `config/security.php` - `headers` and `xss` sections

### 4. Rate Limiting Middleware

**File:** `src/Middleware/RateLimitMiddleware.php`

**Purpose:** Prevents abuse by limiting the number of requests from a single IP address within a time window.

**Features:**
- Per-IP rate limiting
- Burst limit protection
- Automatic blocking for excessive requests
- IP whitelisting support
- Rate limit headers in responses

**Usage:**
```php
// In routes configuration
$router->addRoute('POST', '/api/auth/login', 'AuthController@login', ['rate_limit']);
```

**Configuration:** `config/security.php` - `rate_limit` section

**Response Headers:**
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Time when the limit resets
- `Retry-After`: Seconds to wait before retrying (when blocked)

### 5. Audit Logging Service

**File:** `src/Services/AuditLogService.php`

**Purpose:** Provides centralized audit logging for security-sensitive operations across the application.

**Features:**
- Comprehensive logging of security events
- Database and file-based logging (redundancy)
- Automatic context capture (IP, user agent, etc.)
- Configurable retention policies
- Specialized methods for common events

**Usage:**
```php
// In controllers (automatic via logActivity method)
$this->logActivity('CREATE_EMPLOYEE', ['employee_id' => $employeeId]);

// Direct usage
$auditLogService = $container->resolve(AuditLogService::class);

// Log login
$auditLogService->logLogin($userId, $userRole, ['ip' => $ip]);

// Log failed login
$auditLogService->logFailedLogin($email, ['reason' => 'invalid_password']);

// Log data change
$auditLogService->logDataChange('employee', 'update', $employeeId, $changes, $userId, $userRole);

// Log admin action
$auditLogService->logAdminAction('DELETE_USER', ['user_id' => $targetUserId], $adminUserId);

// Log security event
$auditLogService->logSecurityEvent('UNAUTHORIZED_ACCESS', ['resource' => $uri], $userId);
```

**Configuration:** `config/security.php` - `audit` section

## Integration with Controllers

All controllers automatically benefit from security enhancements through:

1. **Base Controller Methods:**
   - `sanitizeInput($input)`: Sanitize a single string
   - `sanitizeArray($data)`: Sanitize an array of data
   - `logActivity($action, $context)`: Log actions with audit service

2. **Automatic Middleware Application:**
   Security headers and rate limiting are automatically applied by the Router.

3. **Example Controller Usage:**
```php
public function create(Request $request): Response
{
    $this->requireRole('admin');
    
    $data = $this->getJsonData();
    
    // Sanitize input (additional layer beyond middleware)
    $sanitizedData = $this->sanitizeArray($data);
    
    // Create employee
    $employee = $this->employeeService->createEmployee($sanitizedData);
    
    // Log activity (uses AuditLogService)
    $this->logActivity('CREATE_EMPLOYEE', [
        'employee_id' => $employee['employee_id']
    ]);
    
    return $this->success(['employee' => $employee], 'Employee created successfully');
}
```

## Configuration

All security settings are centralized in `config/security.php`:

```php
return [
    // Input validation
    'input' => [
        'max_input_length' => 10000,
        'validate_utf8' => true,
        'strip_dangerous_protocols' => true,
    ],
    
    // CSRF protection
    'csrf' => [
        'enabled' => true,
        'token_expiry' => 1800, // 30 minutes
        'header_name' => 'X-CSRF-TOKEN',
        'form_field' => '_token',
    ],
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'requests_per_minute' => 100,
        'burst_limit' => 200,
        'whitelist' => ['127.0.0.1', '::1'],
    ],
    
    // Security headers
    'headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        // ... more headers
    ],
    
    // Audit logging
    'audit' => [
        'enabled' => true,
        'log_successful_logins' => true,
        'log_failed_logins' => true,
        'log_data_changes' => true,
        'log_admin_actions' => true,
        'retention_days' => 365,
    ],
];
```

## Testing

Run the security enhancements test suite:

```bash
php tests/SecurityEnhancementsTest.php
```

The test suite validates:
- Input validation and SQL injection prevention
- CSRF token generation and validation
- Security headers application
- Rate limiting functionality
- Audit logging service

## Security Best Practices

1. **Always use middleware:** Apply appropriate middleware to all routes
2. **Sanitize input:** Use controller sanitization methods for additional protection
3. **Log security events:** Use audit logging for all security-sensitive operations
4. **Review logs regularly:** Monitor audit logs for suspicious activity
5. **Keep configuration updated:** Review and update security settings periodically
6. **Test thoroughly:** Run security tests after any changes

## Requirements Validation

This implementation validates the following requirements:

- **Requirement 12.3:** Input validation and sanitization across all controllers
- **Requirement 12.4:** Protection against SQL injection, XSS, and CSRF
- **Requirement 12.6:** Audit logging for security-sensitive operations

## Future Enhancements

Potential future improvements:
- Two-factor authentication (2FA)
- Advanced threat detection
- IP geolocation and blocking
- Automated security scanning
- Security event alerting
- Enhanced rate limiting with user-based limits
