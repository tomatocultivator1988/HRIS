# HRIS MVC Architecture - Clean Structure

## Directory Structure

```
HRIS/
├── .env                    # Environment configuration
├── .env.example            # Environment template
├── .htaccess               # Apache configuration (root)
├── README.md               # Project documentation
├── DEPLOYMENT_GUIDE.md     # Deployment instructions
│
├── bin/                    # CLI tools
│   ├── cache-routes.php    # Cache routes for production
│   └── clear-cache.php     # Clear application caches
│
├── config/                 # Configuration files
│   ├── app.php             # Application config
│   ├── database.php        # Database config
│   ├── routes.php          # Route definitions
│   └── security.php        # Security config
│
├── docs/                   # Documentation
│   ├── SECURITY_ENHANCEMENTS.md
│   ├── PERFORMANCE_OPTIMIZATION.md
│   └── examples/           # Code examples
│
├── logs/                   # Application logs
│   ├── app.log             # General application log
│   ├── audit.log           # Security audit log
│   └── rate_limit.json     # Rate limiting data
│
├── public/                 # Web root (document root)
│   ├── .htaccess           # Public directory config
│   ├── index.php           # MVC entry point
│   ├── login.html          # Login page
│   └── assets/             # Static files
│       ├── css/            # Stylesheets
│       └── js/             # JavaScript files
│
├── src/                    # MVC Framework
│   ├── autoload.php        # Class autoloader
│   ├── bootstrap.php       # Framework initialization
│   │
│   ├── Config/             # Configuration management
│   │   ├── ConfigManager.php
│   │   └── helpers.php
│   │
│   ├── Controllers/        # HTTP request handlers
│   │   ├── AnnouncementController.php
│   │   ├── AttendanceController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── EmployeeController.php
│   │   ├── LeaveController.php
│   │   └── ReportController.php
│   │
│   ├── Core/               # Framework components
│   │   ├── Cache.php       # Caching system
│   │   ├── Container.php   # Dependency injection
│   │   ├── Controller.php  # Base controller
│   │   ├── DatabaseConnectionPool.php
│   │   ├── ErrorHandler.php # Error handling
│   │   ├── Model.php       # Base model
│   │   ├── QueryOptimizer.php
│   │   ├── Request.php     # HTTP request
│   │   ├── Response.php    # HTTP response
│   │   ├── RouteCache.php  # Route caching
│   │   ├── Router.php      # URL routing
│   │   ├── SupabaseConnection.php
│   │   ├── ValidationResult.php
│   │   ├── View.php        # View rendering
│   │   └── Traits/
│   │       └── Cacheable.php
│   │
│   ├── Middleware/         # Request/response middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   ├── InputValidationMiddleware.php
│   │   ├── LoggingMiddleware.php
│   │   ├── PerformanceMiddleware.php
│   │   ├── RateLimitMiddleware.php
│   │   ├── RoleMiddleware.php
│   │   └── SecurityHeadersMiddleware.php
│   │
│   ├── Models/             # Data access layer
│   │   ├── Attendance.php
│   │   ├── Employee.php
│   │   ├── LeaveRequest.php
│   │   └── User.php
│   │
│   ├── Services/           # Business logic layer
│   │   ├── AttendanceService.php
│   │   ├── AuditLogService.php
│   │   ├── AuthService.php
│   │   ├── EmployeeService.php
│   │   ├── LeaveService.php
│   │   └── ReportService.php
│   │
│   └── Views/              # Template files
│       ├── employees/
│       ├── errors/
│       └── layouts/
│
├── storage/                # Runtime storage
│   └── cache/              # Cache files
│
└── tests/                  # Test files
    ├── Unit/               # Unit tests
    ├── Integration/        # Integration tests
    └── Feature/            # Feature tests
```

## Request Flow

1. **Entry Point**: All requests → `public/index.php`
2. **Routing**: Router matches URL to controller/action
3. **Middleware**: Authentication, validation, logging
4. **Controller**: Handles request, calls services
5. **Service**: Executes business logic
6. **Model**: Database operations
7. **Response**: JSON (API) or HTML (Views)

## Key Features

### ✅ MVC Architecture
- **Models**: Data access and business entities
- **Views**: HTML templates and presentation
- **Controllers**: HTTP request handlers

### ✅ Services Layer
- Business logic separation
- Reusable across controllers
- Easy to test

### ✅ Dependency Injection
- Loose coupling
- Easy testing with mocks
- Centralized configuration

### ✅ Security
- CSRF protection
- Input validation and sanitization
- Rate limiting
- Security headers
- Audit logging

### ✅ Performance
- Route caching
- Query optimization
- Database connection pooling
- HTTP caching headers
- Lazy loading

### ✅ Error Handling
- Centralized exception handling
- Consistent error responses
- Detailed logging
- User-friendly messages

## Running the Application

### Development

1. **Configure Environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

2. **Start Server**:
   ```bash
   # XAMPP: Place in htdocs/HRIS
   # Access: http://localhost/HRIS
   ```

3. **Clear Caches** (if needed):
   ```bash
   php bin/clear-cache.php all
   ```

### Production

1. **Set Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Cache Routes**:
   ```bash
   php bin/cache-routes.php
   ```

3. **Configure Apache**:
   - Document root: `/path/to/HRIS/public`
   - Enable mod_rewrite

## API Endpoints

All API endpoints follow RESTful conventions:

- `POST /api/auth/login` - User login
- `GET /api/employees` - List employees
- `POST /api/employees` - Create employee
- `GET /api/employees/{id}` - Get employee
- `PUT /api/employees/{id}` - Update employee
- `DELETE /api/employees/{id}` - Delete employee
- `GET /api/dashboard/metrics` - Dashboard metrics
- `GET /api/attendance/daily` - Daily attendance
- `GET /api/leave/balance` - Leave balance
- `GET /api/reports/attendance` - Attendance report

## Configuration Files

- `.env` - Environment variables
- `config/app.php` - Application settings
- `config/database.php` - Database configuration
- `config/routes.php` - Route definitions
- `config/security.php` - Security settings

## Logs

- `logs/app.log` - Application logs
- `logs/audit.log` - Security audit logs
- `logs/slow_queries.log` - Slow database queries
- `logs/slow_requests.log` - Slow HTTP requests

## Testing

```bash
# Run all tests
php tests/run_all_tests.php

# Run specific test
php tests/Unit/CacheTest.php
```

## Maintenance

### Clear Caches
```bash
php bin/clear-cache.php all
```

### Cache Routes (Production)
```bash
php bin/cache-routes.php
```

### View Logs
```bash
tail -f logs/app.log
```

## Notes

- All old non-MVC files have been removed
- System follows strict MVC architecture
- Single entry point: `public/index.php`
- All routes go through Router
- Backward compatibility maintained through routing
