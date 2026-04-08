<?php

/**
 * Security Middleware Usage Examples
 * 
 * This file demonstrates how to use the security middleware
 * in route definitions for the MVC framework.
 */

// Example 1: Basic API endpoint with authentication and rate limiting
$router->addRoute('GET', '/api/employees', 'EmployeeController@index', [
    'rate_limit',           // Prevent abuse
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
]);

// Example 2: Public endpoint with rate limiting only
$router->addRoute('POST', '/api/auth/login', 'AuthController@login', [
    'rate_limit',           // Prevent brute force attacks
    'input_validation'      // Validate and sanitize input
]);

// Example 3: Form submission with CSRF protection
$router->addRoute('POST', '/employees/create', 'EmployeeController@create', [
    'csrf',                 // CSRF protection for web forms
    'input_validation',     // Validate and sanitize input
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
]);

// Example 4: API endpoint with full security stack
$router->addRoute('POST', '/api/employees', 'EmployeeController@create', [
    'rate_limit',           // Prevent abuse
    'input_validation',     // Validate and sanitize input
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
]);

// Example 5: Update endpoint with comprehensive security
$router->addRoute('PUT', '/api/employees/{id}', 'EmployeeController@update', [
    'rate_limit',           // Prevent abuse
    'input_validation',     // Validate and sanitize input
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
]);

// Example 6: Delete endpoint with audit logging
$router->addRoute('DELETE', '/api/employees/{id}', 'EmployeeController@delete', [
    'rate_limit',           // Prevent abuse
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
    // Note: Audit logging is automatic via Controller::logActivity()
]);

// Example 7: Public read endpoint with rate limiting
$router->addRoute('GET', '/api/announcements', 'AnnouncementController@index', [
    'rate_limit'            // Prevent abuse even for public endpoints
]);

// Example 8: Sensitive admin action with full protection
$router->addRoute('POST', '/api/admin/users/delete', 'AdminController@deleteUser', [
    'rate_limit',           // Prevent abuse
    'input_validation',     // Validate and sanitize input
    'auth',                 // Require authentication
    'role:admin'            // Require admin role
    // Audit logging will be automatic
]);

/**
 * Controller Implementation Example
 */
class EmployeeController extends Controller
{
    public function create(Request $request): Response
    {
        try {
            // Authentication and authorization already handled by middleware
            $this->requireRole('admin');
            
            // Get and sanitize input (additional layer beyond middleware)
            $data = $this->getJsonData();
            $sanitizedData = $this->sanitizeArray($data);
            
            // Validate business rules
            $validationResult = $this->validate([
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'work_email' => 'required|email|unique:employees',
                'department' => 'required|string',
                'position' => 'required|string',
            ], $sanitizedData);
            
            if (!$validationResult->isValid()) {
                return $this->validationError($validationResult->getErrors());
            }
            
            // Create employee through service
            $employee = $this->employeeService->createEmployee($sanitizedData);
            
            // Log activity (uses AuditLogService automatically)
            $this->logActivity('CREATE_EMPLOYEE', [
                'employee_id' => $employee['employee_id'],
                'department' => $employee['department']
            ]);
            
            return $this->success(['employee' => $employee], 'Employee created successfully');
            
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    
    public function update(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            
            $employeeId = $this->getRouteParam('id');
            $data = $this->getJsonData();
            
            // Sanitize input
            $sanitizedData = $this->sanitizeArray($data);
            
            // Get original data for audit trail
            $originalEmployee = $this->employeeService->getEmployeeById($employeeId);
            
            // Update employee
            $employee = $this->employeeService->updateEmployee($employeeId, $sanitizedData);
            
            // Log data change with details
            $auditLogService = $this->container->resolve(AuditLogService::class);
            $user = $this->getAuthenticatedUser();
            
            $auditLogService->logDataChange(
                'employee',
                'update',
                $employeeId,
                [
                    'original' => $originalEmployee,
                    'updated' => $employee,
                    'fields_changed' => array_keys($sanitizedData)
                ],
                $user['id'],
                $user['role']
            );
            
            return $this->success(['employee' => $employee], 'Employee updated successfully');
            
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}

/**
 * HTML Form Example with CSRF Protection
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Employee</title>
</head>
<body>
    <form method="POST" action="/employees/create">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="<?php echo \Middleware\CsrfMiddleware::getToken(); ?>">
        
        <label>First Name:</label>
        <input type="text" name="first_name" required>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" required>
        
        <label>Email:</label>
        <input type="email" name="work_email" required>
        
        <button type="submit">Create Employee</button>
    </form>
</body>
</html>

<?php
/**
 * AJAX Request Example with CSRF Protection
 */
?>
<script>
// Get CSRF token from meta tag or cookie
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Make AJAX request with CSRF token
fetch('/api/employees', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Authorization': 'Bearer ' + accessToken
    },
    body: JSON.stringify({
        first_name: 'John',
        last_name: 'Doe',
        work_email: 'john.doe@example.com',
        department: 'IT',
        position: 'Developer'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Employee created:', data.data.employee);
    } else {
        console.error('Error:', data.message);
    }
})
.catch(error => {
    console.error('Request failed:', error);
});
</script>

<?php
/**
 * Security Configuration Example
 */

// config/security.php
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
