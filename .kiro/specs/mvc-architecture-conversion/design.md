# MVC Architecture Conversion Design Document

## Overview

This design document outlines the conversion of the existing HRIS system from its current mixed architecture to a clean MVC (Model-View-Controller) pattern with a dedicated services layer. The current system suffers from scattered business logic, direct database calls in API endpoints, broken routing, and mixed concerns across files.

### Current Architecture Issues

**Identified Problems:**
- **Broken Navigation**: Relative path issues causing 404 errors in admin dashboard and modules
- **Mixed Concerns**: Business logic scattered across API endpoints (e.g., `api/auth/login.php` contains authentication, user lookup, role determination, and logging)
- **Direct Database Access**: API endpoints directly calling `DatabaseHelper` without abstraction
- **No Centralized Routing**: Each API endpoint is a separate PHP file with duplicated setup code
- **Inconsistent Error Handling**: Different error handling patterns across files
- **Tight Coupling**: Controllers, business logic, and data access are intermingled

### Target Architecture

The new MVC architecture will provide:
- **Centralized Routing**: Single entry point with URL-to-controller mapping
- **Clean Separation**: Models handle data, Views handle presentation, Controllers coordinate
- **Service Layer**: Business logic encapsulated in reusable services
- **Dependency Injection**: Loose coupling between components
- **Consistent Error Handling**: Unified error handling and logging
- **Testable Components**: Each layer can be independently tested

## Architecture

### High-Level Structure

```
src/
├── Controllers/           # HTTP request handlers
│   ├── AuthController.php
│   ├── EmployeeController.php
│   ├── AttendanceController.php
│   ├── LeaveController.php
│   ├── DashboardController.php
│   └── ReportController.php
├── Services/             # Business logic layer
│   ├── AuthService.php
│   ├── EmployeeService.php
│   ├── AttendanceService.php
│   ├── LeaveService.php
│   └── ReportService.php
├── Models/               # Data access layer
│   ├── User.php
│   ├── Employee.php
│   ├── Attendance.php
│   ├── LeaveRequest.php
│   └── Announcement.php
├── Views/                # Template system
│   ├── layouts/
│   ├── dashboard/
│   ├── employees/
│   ├── attendance/
│   └── leave/
├── Core/                 # Framework components
│   ├── Router.php
│   ├── Controller.php
│   ├── Model.php
│   ├── View.php
│   ├── Container.php
│   └── Middleware/
├── Config/               # Configuration management
│   ├── app.php
│   ├── database.php
│   └── routes.php
└── public/               # Web-accessible files
    ├── index.php         # Single entry point
    ├── assets/
    └── api/              # API entry point
```

### Request Flow

1. **Request Entry**: All requests go through `public/index.php`
2. **Routing**: Router matches URL to controller/action
3. **Middleware**: Authentication, logging, validation
4. **Controller**: Validates input, calls services
5. **Service**: Executes business logic, coordinates models
6. **Model**: Handles database operations
7. **Response**: Controller formats response (JSON for API, HTML for web)

## Components and Interfaces

### Core Framework Components

#### Router Class
```php
class Router {
    public function addRoute(string $method, string $pattern, string $handler): void
    public function match(string $method, string $uri): ?Route
    public function dispatch(Route $route, Request $request): Response
}
```

**Responsibilities:**
- URL pattern matching
- Route parameter extraction
- Controller instantiation and method calling
- Middleware execution

#### Controller Base Class
```php
abstract class Controller {
    protected Container $container;
    protected Request $request;
    protected Response $response;
    
    public function __construct(Container $container)
    protected function json(array $data, int $status = 200): Response
    protected function view(string $template, array $data = []): Response
    protected function redirect(string $url): Response
}
```

**Responsibilities:**
- Request/response handling
- Input validation
- Service coordination
- Response formatting

#### Model Base Class
```php
abstract class Model {
    protected DatabaseConnection $db;
    protected string $table;
    protected array $fillable = [];
    
    public function find(int $id): ?array
    public function create(array $data): array
    public function update(int $id, array $data): bool
    public function delete(int $id): bool
    public function where(array $conditions): QueryBuilder
}
```

**Responsibilities:**
- Database operations
- Data validation
- Query building
- Result formatting

### Controllers

#### AuthController
```php
class AuthController extends Controller {
    public function login(Request $request): Response
    public function logout(Request $request): Response
    public function refresh(Request $request): Response
    public function verify(Request $request): Response
}
```

**Current Issues Fixed:**
- Removes business logic from `api/auth/login.php`
- Centralizes authentication handling
- Consistent error responses

#### EmployeeController
```php
class EmployeeController extends Controller {
    public function index(Request $request): Response      // List employees
    public function show(int $id): Response               // Get employee
    public function create(Request $request): Response    // Create employee
    public function update(int $id, Request $request): Response
    public function delete(int $id): Response
    public function search(Request $request): Response
}
```

**Current Issues Fixed:**
- Removes complex filtering logic from `api/employees/list.php`
- Standardizes CRUD operations
- Consistent pagination and search

#### DashboardController
```php
class DashboardController extends Controller {
    public function admin(): Response                     // Admin dashboard
    public function employee(): Response                  // Employee dashboard
    public function metrics(): Response                   // Dashboard metrics API
}
```

**Current Issues Fixed:**
- Separates dashboard rendering from metrics API
- Fixes navigation issues in admin dashboard
- Centralizes dashboard logic

### Services Layer

#### AuthService
```php
class AuthService {
    public function authenticate(string $email, string $password): AuthResult
    public function getUserRole(string $userId): ?string
    public function generateToken(User $user): string
    public function validateToken(string $token): ?User
    public function logActivity(User $user, string $action): void
}
```

**Business Logic Encapsulated:**
- User authentication flow
- Role determination logic
- Token management
- Activity logging

#### EmployeeService
```php
class EmployeeService {
    public function createEmployee(array $data): Employee
    public function updateEmployee(int $id, array $data): Employee
    public function searchEmployees(array $criteria): array
    public function validateEmployeeData(array $data): ValidationResult
    public function getEmployeesByDepartment(string $department): array
}
```

**Business Logic Encapsulated:**
- Employee validation rules
- Search and filtering logic
- Department management
- Data transformation

#### AttendanceService
```php
class AttendanceService {
    public function recordTimeIn(int $employeeId): AttendanceRecord
    public function recordTimeOut(int $employeeId): AttendanceRecord
    public function calculateDailyHours(AttendanceRecord $record): float
    public function detectAbsences(string $date): array
    public function generateAttendanceReport(array $criteria): Report
}
```

**Business Logic Encapsulated:**
- Attendance calculation rules
- Absence detection algorithms
- Report generation logic
- Time validation

### Models

#### Employee Model
```php
class Employee extends Model {
    protected string $table = 'employees';
    protected array $fillable = [
        'employee_id', 'first_name', 'last_name', 'work_email',
        'department', 'position', 'employment_status'
    ];
    
    public function attendance(): HasMany
    public function leaveRequests(): HasMany
    public function isActive(): bool
    public function getFullName(): string
}
```

#### Attendance Model
```php
class Attendance extends Model {
    protected string $table = 'attendance';
    protected array $fillable = [
        'employee_id', 'date', 'time_in', 'time_out', 'status'
    ];
    
    public function employee(): BelongsTo
    public function calculateHours(): float
    public function isLate(): bool
}
```

### Routing System

#### Route Configuration
```php
// config/routes.php
return [
    // Web routes
    ['GET', '/', 'AuthController@loginForm'],
    ['GET', '/dashboard/admin', 'DashboardController@admin'],
    ['GET', '/dashboard/employee', 'DashboardController@employee'],
    
    // API routes
    ['POST', '/api/auth/login', 'AuthController@login'],
    ['POST', '/api/auth/logout', 'AuthController@logout'],
    ['GET', '/api/employees', 'EmployeeController@index'],
    ['POST', '/api/employees', 'EmployeeController@create'],
    ['GET', '/api/employees/{id}', 'EmployeeController@show'],
    ['PUT', '/api/employees/{id}', 'EmployeeController@update'],
    ['DELETE', '/api/employees/{id}', 'EmployeeController@delete'],
    
    // Dashboard API
    ['GET', '/api/dashboard/metrics', 'DashboardController@metrics'],
];
```

#### URL Patterns
- **Web Pages**: `/dashboard/admin`, `/modules/employees/list`
- **API Endpoints**: `/api/employees`, `/api/attendance/daily`
- **RESTful**: `/api/employees/{id}`, `/api/leave-requests/{id}/approve`

## Data Models

### Database Schema Enhancements

#### Core Tables (Existing)
- `employees` - Employee master data
- `attendance` - Daily attendance records
- `leave_requests` - Leave applications
- `announcements` - System announcements
- `admins` - Administrator accounts

#### New Tables for MVC
```sql
-- Route caching for performance
CREATE TABLE route_cache (
    pattern VARCHAR(255) PRIMARY KEY,
    handler VARCHAR(255) NOT NULL,
    middleware JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System logs for debugging
CREATE TABLE system_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Configuration storage
CREATE TABLE app_config (
    key VARCHAR(100) PRIMARY KEY,
    value TEXT NOT NULL,
    type VARCHAR(20) DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Data Transfer Objects

#### AuthResult
```php
class AuthResult {
    public bool $success;
    public ?User $user;
    public ?string $token;
    public ?string $error;
    public array $metadata;
}
```

#### ValidationResult
```php
class ValidationResult {
    public bool $isValid;
    public array $errors;
    public array $sanitizedData;
}
```

#### PaginationResult
```php
class PaginationResult {
    public array $data;
    public int $total;
    public int $page;
    public int $perPage;
    public bool $hasMore;
}
```

## Error Handling

### Exception Hierarchy
```php
abstract class HRISException extends Exception {}

class ValidationException extends HRISException {}
class AuthenticationException extends HRISException {}
class AuthorizationException extends HRISException {}
class DatabaseException extends HRISException {}
class BusinessLogicException extends HRISException {}
```

### Error Handler
```php
class ErrorHandler {
    public function handleException(Throwable $e): Response
    public function handleValidationError(ValidationException $e): Response
    public function handleAuthError(AuthenticationException $e): Response
    public function logError(Throwable $e, array $context = []): void
}
```

### HTTP Status Code Mapping
- `400` - Validation errors, bad request
- `401` - Authentication required
- `403` - Authorization denied
- `404` - Resource not found
- `422` - Validation failed
- `500` - Server error

## Testing Strategy

### Unit Testing Structure
```
tests/
├── Unit/
│   ├── Services/
│   │   ├── AuthServiceTest.php
│   │   ├── EmployeeServiceTest.php
│   │   └── AttendanceServiceTest.php
│   ├── Models/
│   │   ├── EmployeeTest.php
│   │   └── AttendanceTest.php
│   └── Core/
│       ├── RouterTest.php
│       └── ContainerTest.php
├── Integration/
│   ├── Controllers/
│   │   ├── AuthControllerTest.php
│   │   └── EmployeeControllerTest.php
│   └── Database/
│       └── EmployeeRepositoryTest.php
└── Feature/
    ├── AuthenticationTest.php
    ├── EmployeeManagementTest.php
    └── DashboardTest.php
```

### Testing Approach

**Unit Tests:**
- Test individual methods in isolation
- Mock external dependencies
- Focus on business logic validation
- Test error conditions and edge cases

**Integration Tests:**
- Test controller-service-model interactions
- Use test database with fixtures
- Verify API endpoint responses
- Test middleware functionality

**Feature Tests:**
- End-to-end user workflows
- Authentication and authorization flows
- Complete CRUD operations
- Dashboard functionality

### Test Configuration
```php
// tests/TestCase.php
abstract class TestCase extends PHPUnit\Framework\TestCase {
    protected Container $container;
    protected DatabaseConnection $testDb;
    
    protected function setUp(): void {
        $this->container = new Container();
        $this->testDb = new TestDatabase();
        $this->seedTestData();
    }
    
    protected function tearDown(): void {
        $this->testDb->cleanup();
    }
}
```

## Migration Strategy

### Phase 1: Core Framework Setup
1. **Create Core Classes**: Router, Controller, Model, Container
2. **Set up Entry Point**: Modify `public/index.php` for routing
3. **Database Abstraction**: Enhance existing `DatabaseHelper`
4. **Configuration System**: Centralize app configuration

### Phase 2: Authentication Migration
1. **AuthController**: Convert `api/auth/login.php` to controller
2. **AuthService**: Extract business logic from auth endpoints
3. **User Model**: Create user data access layer
4. **Middleware**: Implement authentication middleware

### Phase 3: Employee Management
1. **EmployeeController**: Convert employee API endpoints
2. **EmployeeService**: Extract employee business logic
3. **Employee Model**: Enhance existing database operations
4. **Views**: Create employee management templates

### Phase 4: Dashboard and Reporting
1. **DashboardController**: Fix navigation and metrics
2. **ReportService**: Centralize report generation
3. **View Templates**: Create reusable dashboard components
4. **API Standardization**: Consistent response formats

### Phase 5: Remaining Modules
1. **Attendance System**: Convert attendance endpoints
2. **Leave Management**: Convert leave request system
3. **Announcements**: Convert announcement system
4. **Testing**: Comprehensive test coverage

### Backward Compatibility
- **Gradual Migration**: Keep existing API endpoints during transition
- **Proxy Pattern**: Route old endpoints to new controllers
- **Feature Flags**: Enable/disable new architecture components
- **Rollback Plan**: Ability to revert to old system if needed

## Performance Considerations

### Caching Strategy
- **Route Caching**: Cache compiled routes for production
- **Query Caching**: Cache frequently accessed database queries
- **Template Caching**: Compile and cache view templates
- **Configuration Caching**: Cache merged configuration files

### Database Optimization
- **Connection Pooling**: Reuse database connections
- **Query Optimization**: Use prepared statements and indexes
- **Lazy Loading**: Load related data only when needed
- **Batch Operations**: Group multiple database operations

### Memory Management
- **Dependency Injection**: Singleton services where appropriate
- **Object Pooling**: Reuse expensive objects
- **Garbage Collection**: Proper cleanup of resources
- **Memory Profiling**: Monitor memory usage patterns

## Security Integration

### Authentication Enhancements
- **JWT Token Management**: Secure token generation and validation
- **Session Security**: Secure session handling and storage
- **Password Policies**: Enforce strong password requirements
- **Multi-factor Authentication**: Support for 2FA (future enhancement)

### Authorization Framework
- **Role-Based Access Control**: Granular permission system
- **Resource-Level Permissions**: Control access to specific resources
- **API Rate Limiting**: Prevent abuse of API endpoints
- **Audit Logging**: Track all security-sensitive operations

### Input Validation
- **Request Validation**: Validate all incoming requests
- **SQL Injection Prevention**: Use parameterized queries
- **XSS Protection**: Sanitize output data
- **CSRF Protection**: Implement CSRF tokens for forms

### Security Headers
- **Content Security Policy**: Prevent XSS attacks
- **HTTPS Enforcement**: Redirect HTTP to HTTPS
- **Security Headers**: Implement security-related HTTP headers
- **File Upload Security**: Validate and sanitize uploaded files

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Model Layer Data Validation Consistency

*For any* business entity and any input data, the Model Layer SHALL apply validation rules consistently, returning the same validation result for identical input data regardless of when or how the validation is performed.

**Validates: Requirements 1.3**

### Property 2: Database Error Handling Consistency

*For any* database operation failure across all Model Layer classes, the system SHALL return error responses in a consistent format with the same structure and error classification.

**Validates: Requirements 1.4**

### Property 3: Input Sanitization Completeness

*For any* user input containing potentially malicious content, the Model Layer SHALL sanitize the input before database operations, ensuring no malicious content reaches the database layer.

**Validates: Requirements 1.6**

### Property 4: HTTP Request Validation Consistency

*For any* HTTP request with invalid parameters or missing authentication, the Controller Layer SHALL validate and reject the request consistently, returning appropriate error responses with correct HTTP status codes.

**Validates: Requirements 3.2**

### Property 5: Controller Response Format Consistency

*For any* controller action across all controllers, the HTTP response SHALL follow a consistent format structure and use appropriate HTTP status codes for the response type (success, error, validation failure).

**Validates: Requirements 3.4**

### Property 6: Controller Error Handling Uniformity

*For any* error condition occurring in any controller, the error SHALL be handled consistently with proper logging and user-friendly error messages while maintaining the same response structure.

**Validates: Requirements 3.5**

### Property 7: Business Rule Enforcement Consistency

*For any* business scenario requiring rule validation, the Service Layer SHALL enforce business rules consistently across all domain areas, applying the same validation logic for equivalent business conditions.

**Validates: Requirements 4.3**

### Property 8: Service Layer Error Message Quality

*For any* business-specific error condition in any service, the system SHALL provide meaningful error messages that clearly describe the business rule violation or constraint failure.

**Validates: Requirements 4.6**

### Property 9: URL Routing Consistency

*For any* valid URL pattern matching the defined routes, the routing system SHALL consistently map the URL to the correct controller and action, extracting route parameters accurately.

**Validates: Requirements 6.3**

### Property 10: Route Parameter Handling Reliability

*For any* combination of route parameters and query string values, the routing system SHALL correctly parse and provide the parameters to the target controller without data loss or corruption.

**Validates: Requirements 6.4**

### Property 11: System-Wide Error Handling Uniformity

*For any* error occurring in any system layer (Model, View, Controller, Service), the centralized error handling system SHALL process the error consistently, applying the same logging and response formatting rules.

**Validates: Requirements 8.1**

### Property 12: Error Logging and User Message Separation

*For any* error condition, the system SHALL log detailed technical information for debugging while providing user-friendly messages to the client, ensuring sensitive information is never exposed to users.

**Validates: Requirements 8.2**

### Property 13: Error Classification Accuracy

*For any* error occurring in the system, the error handling system SHALL correctly classify the error type (validation, business logic, system error) and apply appropriate handling rules for each classification.

**Validates: Requirements 8.3**

### Property 14: API Error Response Format Consistency

*For any* error occurring in any API endpoint, the error response SHALL follow a consistent JSON format structure with standardized error codes and messages.

**Validates: Requirements 8.4**

### Property 15: HTTP Status Code Correctness

*For any* error condition type, the system SHALL return the appropriate HTTP status code that correctly represents the error category (400 for validation, 401 for authentication, 403 for authorization, 500 for system errors).

**Validates: Requirements 8.5**

### Property 16: Authorization Rule Consistency

*For any* user role and resource combination, the authorization system SHALL consistently apply role-based access control rules, granting or denying access based on the same permission logic across all service operations.

**Validates: Requirements 12.2**

### Property 17: Input Sanitization Completeness

*For any* user input across all system entry points, the system SHALL apply consistent sanitization and validation rules, ensuring malicious content is neutralized before processing.

**Validates: Requirements 12.3**

### Property 18: Security Vulnerability Protection

*For any* common attack pattern (SQL injection, XSS, CSRF), the system SHALL consistently detect and block the attack across all input vectors and system components.

**Validates: Requirements 12.4**