# MVC Core Bugfixes Design

## Overview

This design addresses 16 critical bugs in the HRIS MVC system that prevent core functionality from working correctly. The bugs span across Router, Controllers, Middleware, and View layers. The fix strategy involves:

1. **Request Injection Fix**: Ensure Router calls setRequest() before dispatching to controllers
2. **Class Redeclaration Fix**: Remove duplicate Request/Response stub classes from Router.php
3. **Method Signature Fix**: Add Request parameter to all controller methods
4. **Legacy Code Removal**: Replace non-existent legacy API dependencies with proper MVC patterns
5. **Route Parameter Fix**: Use route parameters instead of JSON body for RESTful endpoints
6. **View Data Access Fix**: Update templates to use extracted variables correctly
7. **Asset Path Fix**: Use dynamic base path for XAMPP subdirectory deployments
8. **Consistency Fix**: Standardize error handling and redirect patterns

The fixes will restore proper request handling, eliminate fatal errors, fix method signatures, remove legacy dependencies, correct view rendering, and resolve deployment path issues.

## Glossary

- **Bug_Condition (C)**: The condition that triggers each specific bug - varies by bug category
- **Property (P)**: The desired behavior when the bug condition is fixed
- **Preservation**: Existing functionality that must remain unchanged by the fixes
- **Router::dispatch()**: The method in `src/Core/Router.php` that calls controller methods
- **Controller::setRequest()**: The method in `src/Core/Controller.php` that sets the request object
- **Request/Response Classes**: Core HTTP classes defined in `src/Core/Router.php` (lines 321-500+)
- **Route Parameters**: URL path parameters extracted from patterns like `/api/leave/{id}/approve`
- **DatabaseHelper**: Non-existent legacy class that was used before MVC conversion
- **View::render()**: The method that processes templates and extracts data variables
- **Base Path**: The subdirectory path where the application is deployed (e.g., `/HRIS/`)

## Bug Details

### Bug Condition

The bugs manifest across multiple categories. Each category has its own bug condition:

**Category 1: Request Injection Bugs (FATAL)**
The bug manifests when Router::dispatch() calls a controller method without first calling setRequest() on the controller instance, causing $this->request to remain null.

**Category 2: Class Redeclaration Bugs (FATAL)**
The bug manifests when both Router.php (containing stub classes at lines 321-500+) and standalone Request.php/Response.php files are loaded, causing PHP to throw "Cannot redeclare class" fatal errors.

**Category 3: Legacy Dependency Bugs (FATAL)**
The bug manifests when DashboardController methods try to require non-existent legacy API files (/api/config/database.php) or call non-existent classes (DatabaseHelper, AnnouncementManager).

**Category 4: Method Signature Bugs (HIGH)**
The bug manifests when Router calls $controller->$method($request) but the controller method declares (): Response with no parameter, causing PHP 8 strict mode to silently drop the $request argument.

**Category 5: Route Parameter Bugs (HIGH)**
The bug manifests when LeaveController::approve() and deny() ignore the {id} route parameter and read request_id from JSON body instead, requiring the ID to be sent twice.

**Category 6: View Data Access Bugs (HIGH)**
The bug manifests when View::render() calls extract($data, EXTR_SKIP) but templates still try to access $data['key'] instead of the extracted $key variable.

**Category 7: Asset Path Bugs (MEDIUM)**
The bug manifests when the application runs on XAMPP at /HRIS/ subdirectory and asset paths use root-relative paths without the base path prefix.

**Category 8: Consistency Bugs (LOW)**
The bug manifests when different controllers redirect to different login paths ('/' vs '/login') on AuthenticationException.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type SystemState
  OUTPUT: boolean
  
  RETURN (
    // Category 1: Request not injected
    (input.routerDispatchCalled AND NOT input.controllerSetRequestCalled)
    
    OR
    
    // Category 2: Duplicate classes
    (input.routerPhpLoaded AND input.standaloneRequestPhpLoaded)
    
    OR
    
    // Category 3: Legacy dependencies
    (input.controllerMethod IN ['DashboardController::admin', 'DashboardController::employee', 
                                  'DashboardController::metrics', 'AnnouncementController::*']
     AND input.methodCallsLegacyCode)
    
    OR
    
    // Category 4: Missing Request parameter
    (input.controllerMethod IN ['AttendanceController::*', 'DashboardController::*', 
                                  'EmployeeController::*', 'LeaveController::*', 'ReportController::*']
     AND input.methodSignature == '(): Response')
    
    OR
    
    // Category 5: Ignoring route parameters
    (input.endpoint IN ['/api/leave/{id}/approve', '/api/leave/{id}/deny']
     AND input.methodReadsFromJsonBodyInsteadOfRouteParam)
    
    OR
    
    // Category 6: Wrong variable access in templates
    (input.templateFile IN ['employees/list.php', 'employees/profile.php']
     AND input.templateAccessesDataArray)
    
    OR
    
    // Category 7: Missing base path in assets
    (input.deploymentPath == '/HRIS/'
     AND input.assetPath STARTS_WITH '/'
     AND NOT input.assetPath STARTS_WITH '/HRIS/')
    
    OR
    
    // Category 8: Inconsistent redirects
    (input.exceptionType == 'AuthenticationException'
     AND input.redirectTarget IN ['/', '/login']
     AND NOT input.redirectTargetConsistent)
  )
END FUNCTION
```

### Examples

**Category 1: Request Injection**
- Client calls GET /api/employees/123
- Router::dispatch() creates EmployeeController instance
- Router calls $controller->show($request) WITHOUT calling setRequest() first
- Inside show(), $this->request is null
- Call to $this->getRouteParam('id') throws "Call to member function on null"

**Category 2: Class Redeclaration**
- public/index.php loads Router.php (contains Request/Response stubs at lines 321-500+)
- public/index.php also loads standalone Request.php and Response.php
- PHP throws "Cannot redeclare class Core\Request" fatal error

**Category 3: Legacy Dependencies**
- Client calls GET /dashboard/admin
- DashboardController::admin() tries to require '/api/config/database.php' (doesn't exist)
- PHP throws "Failed opening required file" fatal error
- DashboardController::metrics() calls \DatabaseHelper::count() (class doesn't exist)
- PHP throws "Class not found" fatal error

**Category 4: Method Signature**
- Router calls $controller->show($request) on EmployeeController
- EmployeeController::show() declares (): Response with no parameter
- PHP 8 strict mode silently drops the $request argument
- Method executes but $request parameter is never received

**Category 5: Route Parameters**
- Client calls POST /api/leave/123/approve with empty JSON body
- LeaveController::approve() ignores route parameter 'id' = '123'
- Method reads $data['request_id'] from JSON body (empty)
- Returns error "Leave request ID is required"

**Category 6: View Data Access**
- EmployeeController passes ['employees' => $list] to view
- View::render() calls extract($data, EXTR_SKIP), creating $employees variable
- Template tries to access $data['employees'] (undefined)
- PHP throws "Undefined variable: data" notice

**Category 7: Asset Paths**
- Application deployed at http://localhost/HRIS/
- Template includes <link href="/assets/css/custom.css">
- Browser requests http://localhost/assets/css/custom.css (404)
- Should request http://localhost/HRIS/assets/css/custom.css

**Category 8: Inconsistent Redirects**
- DashboardController catches AuthenticationException, redirects to '/'
- EmployeeController catches AuthenticationException, redirects to '/login'
- User experience is inconsistent

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Router middleware pipeline execution must continue to work exactly as before
- Router route parameter extraction and injection must continue to work
- Controller helper methods (json(), success(), error(), view(), redirect()) must continue to work
- AuthMiddleware user data injection via setUser() must continue to work
- View::render() layout support and data extraction must continue to work
- EmployeeController support for both legacy .php endpoints and clean REST endpoints must continue
- LeaveController permission validation and activity logging must continue to work
- DashboardController role-based access control must continue to work
- ReportController date format validation and filtering must continue to work
- AnnouncementController admin and employee access levels must continue to work
- LoggingMiddleware request logging (method, URI, IP, user agent, timestamp) must continue to work
- Asset file serving (CSS and JavaScript) must continue to work
- Application support for both subdirectory and domain-root deployments must continue
- Request class URI parsing (query string removal, URL decoding) must continue to work
- Response class HTTP status codes and headers must continue to work

**Scope:**
All inputs that do NOT involve the specific bug conditions should be completely unaffected by these fixes. This includes:
- Requests to routes that don't have the bugs (e.g., AuthController, AttendanceController without signature bugs)
- Middleware execution for non-buggy routes
- View rendering for templates that don't have data access bugs
- Asset requests that already work correctly
- Deployments on domain root (no subdirectory) that don't have path issues

## Hypothesized Root Cause

Based on the bug description, the most likely issues are:

1. **Missing setRequest() Call**: Router::dispatch() was refactored to instantiate controllers via Container but the setRequest() call was forgotten before invoking the controller method

2. **Incomplete Class Extraction**: When Request and Response classes were extracted from Router.php to standalone files, the stub classes in Router.php (lines 321-500+) were not removed

3. **Incomplete MVC Migration**: DashboardController and AnnouncementController still contain legacy code that tries to require old API files and call old helper classes that no longer exist after MVC conversion

4. **Method Signature Oversight**: When controllers were created, some methods were declared with (): Response signature without the Request $request parameter, likely copy-paste errors

5. **RESTful Pattern Misunderstanding**: LeaveController::approve() and deny() were written to read ID from JSON body instead of route parameter, not following RESTful conventions

6. **Template Migration Incomplete**: When templates were migrated to use View::render() with extract(), the template files weren't updated to use extracted variables instead of $data array access

7. **Hardcoded Base Path**: Asset paths and JavaScript redirect logic use hardcoded '/HRIS/' path instead of dynamic base path configuration

8. **Inconsistent Error Handling**: Different controllers were written by different developers or at different times, leading to inconsistent redirect patterns

## Correctness Properties

Property 1: Bug Condition - Request Injection

_For any_ controller method invocation where Router::dispatch() is called, the fixed Router SHALL call $controller->setRequest($request) before invoking $controller->$method($request), ensuring $this->request is properly set.

**Validates: Requirements 5.1, 5.2**

Property 2: Bug Condition - Class Redeclaration

_For any_ application bootstrap where both Router.php and standalone Request.php/Response.php are loaded, the fixed code SHALL load only the standalone classes without redeclaration errors because stub classes are removed from Router.php.

**Validates: Requirements 5.3, 5.4, 8.1**

Property 3: Bug Condition - Legacy Dependencies

_For any_ DashboardController or AnnouncementController method invocation, the fixed methods SHALL use proper MVC Service layer and Model classes instead of trying to require non-existent legacy API files or call non-existent helper classes.

**Validates: Requirements 5.5, 5.6, 5.7, 6.5**

Property 4: Bug Condition - Method Signatures

_For any_ controller method that Router calls with $controller->$method($request), the fixed method SHALL declare (Request $request): Response signature to properly receive the request parameter.

**Validates: Requirements 6.1**

Property 5: Bug Condition - Route Parameters

_For any_ RESTful endpoint with ID in URL path (e.g., /api/leave/{id}/approve), the fixed controller method SHALL read the ID from route parameter using getRouteParam('id') instead of requiring it in JSON body.

**Validates: Requirements 6.2, 6.3**

Property 6: Bug Condition - View Data Access

_For any_ template rendered by View::render() with data array, the fixed template SHALL access variables directly as $employees and $employee instead of $data['employees'] and $data['employee'].

**Validates: Requirements 6.6**

Property 7: Bug Condition - Asset Paths

_For any_ deployment on XAMPP at /HRIS/ subdirectory, the fixed templates and JavaScript SHALL use dynamic base path (AppConfig.basePath or base_url() helper) to generate correct asset paths.

**Validates: Requirements 7.1, 7.4**

Property 8: Bug Condition - Consistency

_For any_ controller that catches AuthenticationException, the fixed controller SHALL consistently redirect to '/login' for uniform user experience.

**Validates: Requirements 8.2**

Property 9: Preservation - Middleware Pipeline

_For any_ request that goes through the Router, the fixed code SHALL continue to execute the middleware pipeline exactly as before, preserving all middleware functionality.

**Validates: Requirements 9.1**

Property 10: Preservation - Controller Helpers

_For any_ controller method that uses helper methods (json(), success(), error(), view(), redirect()), the fixed code SHALL continue to support these methods with identical behavior.

**Validates: Requirements 9.3**

Property 11: Preservation - View Rendering

_For any_ view rendering that uses layouts or data extraction, the fixed View::render() SHALL continue to support these features exactly as before.

**Validates: Requirements 9.5**

Property 12: Preservation - Deployment Flexibility

_For any_ deployment on either subdirectory or domain root, the fixed code SHALL continue to support both deployment types correctly.

**Validates: Requirements 9.13**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `src/Core/Router.php`

**Function**: `dispatch()`

**Specific Changes**:
1. **Add setRequest() Call**: After instantiating controller via Container, add `$controller->setRequest($request);` before calling `$controller->$method($request)`
   - Insert after line: `$controller = $container->resolve($controllerClass);`
   - Insert before line: `$response = $controller->$method($request);`

2. **Remove Duplicate Classes**: Delete the Request and Response stub classes at lines 321-500+ (after the Route class definition)
   - These classes are now in standalone files: `src/Core/Request.php` and `src/Core/Response.php`
   - Keep only the Route class definition

**File**: `src/Controllers/DashboardController.php`

**Functions**: `admin()`, `employee()`, `metrics()`, `getDashboardMetrics()`, `getDepartmentHeadcount()`, `getAttendanceTrend()`

**Specific Changes**:
1. **Add Request Parameter**: Change method signatures from `(): Response` to `(Request $request): Response` for admin(), employee(), metrics()

2. **Create Dashboard Views**: Create new view files in src/Views/dashboard/
   - Create src/Views/dashboard/admin.php with admin dashboard layout
   - Create src/Views/dashboard/employee.php with employee dashboard layout
   - These views should display metrics and charts

3. **Remove Legacy HTML Loading**: Replace file_get_contents() of non-existent HTML files with proper View::render() calls
   - admin(): Change from file_get_contents('/dashboard/admin.html') to $this->view->render('dashboard/admin', ['user' => $user, 'metrics' => $metrics])
   - employee(): Change from file_get_contents('/dashboard/employee.html') to $this->view->render('dashboard/employee', ['user' => $user])

4. **Remove Legacy Database Calls**: Replace DatabaseHelper static calls with proper Model/Service layer calls
   - Remove all require_once dirname(__DIR__, 2) . '/api/config/database.php' lines
   - In getDashboardMetrics(): Use EmployeeModel::getActiveCount() instead of DatabaseHelper::count(TABLE_EMPLOYEES)
   - In getDashboardMetrics(): Use AttendanceModel methods instead of DatabaseHelper::select(TABLE_ATTENDANCE)
   - In getDashboardMetrics(): Use LeaveRequestModel methods instead of DatabaseHelper::select(TABLE_LEAVE_REQUESTS)
   - In getDepartmentHeadcount(): Use EmployeeModel::getAll() or similar instead of DatabaseHelper::select(TABLE_EMPLOYEES)
   - In getAttendanceTrend(): Use AttendanceModel methods instead of DatabaseHelper::select(TABLE_ATTENDANCE)
   - Inject EmployeeModel, AttendanceModel, LeaveRequestModel via constructor (or resolve from Container)

5. **Fix Redirect Consistency**: Change redirectToLogin() to redirect to '/login' instead of '/'

**File**: `src/Controllers/LeaveController.php`

**Functions**: `approve()`, `deny()`

**Specific Changes**:
1. **Add Request Parameter**: Change method signatures from `(): Response` to `(Request $request): Response`

2. **Use Route Parameters**: Change from reading $data['request_id'] to using $this->getRouteParam('id')
   - approve(): `$requestId = $this->getRouteParam('id');`
   - deny(): `$requestId = $this->getRouteParam('id');`
   - Keep denial_reason from JSON body for deny()

**File**: `src/Controllers/EmployeeController.php`

**Functions**: All methods that have (): Response signature

**Specific Changes**:
1. **Add Request Parameter**: Change method signatures from `(): Response` to `(Request $request): Response` for:
   - index(), show(), create(), update(), delete()
   - search(), profile(), updateProfile()
   - apiIndex(), apiSearch(), apiCreate(), apiShow(), apiUpdate(), apiDelete()
   - indexView(), showView(), createForm(), editForm(), profileView()

2. **Fix apiUpdate() Route Parameter Access**: Remove direct access to $this->request->getRouteParameters() and use getRouteParam() helper instead

3. **Fix Redirect Consistency**: Change redirectToLogin() to redirect to '/login' (already correct)

**File**: `src/Controllers/ReportController.php`

**Functions**: All methods

**Specific Changes**:
1. **Add Request Parameter**: Change method signatures from `(): Response` to `(Request $request): Response` for all methods

2. **Add Report Data to View**: In index() method, pass actual report data to the view instead of just title and user data

**File**: `src/Controllers/AttendanceController.php`

**Functions**: All methods

**Specific Changes**:
1. **Add Request Parameter**: Change method signatures from `(): Response` to `(Request $request): Response` for all methods

**File**: `src/Controllers/AnnouncementController.php`

**Functions**: All methods

**Specific Changes**:
1. **NOTE**: Method signatures are ALREADY CORRECT - all methods have (Request $request): Response signature. This is NOT a signature bug.

2. **Remove Legacy Dependencies**: Replace require of non-existent /api/announcements/AnnouncementManager.php with proper Service layer
   - Create AnnouncementService in src/Services/AnnouncementService.php
   - Implement these methods in AnnouncementService:
     - getAllAnnouncements(): array
     - getActiveAnnouncements(): array
     - getAnnouncement(string $id): array
     - createAnnouncement(string $title, string $content, string $authorId): array
     - updateAnnouncement(string $id, string $title, string $content, string $editorId): array
     - deactivateAnnouncement(string $id): array
   - Inject AnnouncementService via constructor
   - Remove all require_once dirname(__DIR__, 2) . '/api/announcements/AnnouncementManager.php' lines
   - Replace \AnnouncementManager::getAllAnnouncements() with $this->announcementService->getAllAnnouncements()
   - Replace \AnnouncementManager::getActiveAnnouncements() with $this->announcementService->getActiveAnnouncements()
   - Replace \AnnouncementManager::getAnnouncement($id) with $this->announcementService->getAnnouncement($id)
   - Replace \AnnouncementManager::createAnnouncement(...) with $this->announcementService->createAnnouncement(...)
   - Replace \AnnouncementManager::updateAnnouncement(...) with $this->announcementService->updateAnnouncement(...)
   - Replace \AnnouncementManager::deactivateAnnouncement($id) with $this->announcementService->deactivateAnnouncement($id)

**File**: `src/Views/employees/list.php`

**Specific Changes**:
1. **Fix Variable Access**: Change all occurrences of `$data['employees']` to `$employees`
2. **Fix Variable Access**: Change all occurrences of `$data['pagination']` to `$pagination`
3. **Fix Variable Access**: Change all occurrences of `$data['filters']` to `$filters`
4. **Fix Variable Access**: Change all occurrences of `$data['departments']` to `$departments`

**File**: `src/Views/employees/profile.php`

**Specific Changes**:
1. **Fix Variable Access**: Change all occurrences of `$data['employee']` to `$employee`
2. **Fix Variable Access**: Change all occurrences of `$data['canEdit']` to `$canEdit`
3. **Fix Variable Access**: Change all occurrences of `$data['isOwnProfile']` to `$isOwnProfile`

**File**: `src/Config/helpers.php`

**Specific Changes**:
1. **Create base_url() Helper**: Add base_url() function to helpers.php
   ```php
   function base_url(string $path = ''): string {
       $basePath = '/HRIS'; // Or get from config
       return $basePath . $path;
   }
   ```

**File**: `src/Views/layouts/base.php`

**Specific Changes**:
1. **Fix Asset Paths**: Change hardcoded asset paths to use base_url() helper
   - Change `/assets/css/custom.css` to `<?= base_url('/assets/css/custom.css') ?>`
   - Change `/assets/js/*.js` to `<?= base_url('/assets/js/*.js') ?>`

**File**: `src/Views/auth/login.php`

**Specific Changes**:
1. **Fix Asset Paths**: Change hardcoded asset paths to use base_url() helper
   - Change `/assets/css/custom.css` to `<?= base_url('/assets/css/custom.css') ?>`
   - Change `/assets/js/config.js` to `<?= base_url('/assets/js/config.js') ?>`
   - Change `/assets/js/auth.js` to `<?= base_url('/assets/js/auth.js') ?>`
   - Change `/assets/js/api.js` to `<?= base_url('/assets/js/api.js') ?>`
   - Change `/assets/js/utils.js` to `<?= base_url('/assets/js/utils.js') ?>`
   - Change `/assets/js/validation.js` to `<?= base_url('/assets/js/validation.js') ?>`

**File**: `public/assets/js/auth.js`

**Specific Changes**:
1. **Fix Redirect Logic**: Change hardcoded '/HRIS/' check to use AppConfig.basePath
   - Change `window.location.pathname === '/HRIS/'` to `window.location.pathname === AppConfig.basePath || window.location.pathname === AppConfig.basePath + '/'`

**File**: `src/Core/Request.php`

**Specific Changes**:
1. **Add Missing Methods**: Add setStartTime() and getStartTime() methods to standalone Request.php
   - Add private property: `private ?float $startTime = null;`
   - Add method: `public function setStartTime(float $startTime): void { $this->startTime = $startTime; }`
   - Add method: `public function getStartTime(): ?float { return $this->startTime; }`
   - These methods exist in Router.php stub but will be deleted in Bug #2 fix, so must be added to standalone file

**File**: `src/Controllers/LeaveController.php`

**Functions**: Add missing methods

**Specific Changes**:
1. **Implement Missing Methods**: Add balance(), types(), and credits() methods to LeaveController
   - DO NOT remove routes - they are referenced by frontend
   - Add method: `public function balance(Request $request): Response` - returns employee leave balance
   - Add method: `public function types(Request $request): Response` - returns available leave types
   - Add method: `public function credits(Request $request): Response` - returns leave credits summary
   - These methods should use LeaveService to fetch data
   - Return JSON responses with proper structure

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bugs on unfixed code, then verify the fixes work correctly and preserve existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bugs BEFORE implementing the fixes. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate requests to buggy endpoints and assert expected failures. Run these tests on the UNFIXED code to observe failures and understand the root causes.

**Test Cases**:
1. **Request Injection Test**: Call GET /api/employees/123, assert "Call to member function on null" error (will fail on unfixed code)
2. **Class Redeclaration Test**: Load Router.php and standalone Request.php, assert "Cannot redeclare class" error (will fail on unfixed code)
3. **Legacy Dependency Test**: Call GET /dashboard/admin, assert "Failed opening required file" or "Class not found" error (will fail on unfixed code)
4. **Method Signature Test**: Use reflection to check controller method signatures, assert missing Request parameter (will fail on unfixed code)
5. **Route Parameter Test**: Call POST /api/leave/123/approve with empty body, assert "Leave request ID is required" error (will fail on unfixed code)
6. **View Data Access Test**: Render employees/list view, assert "Undefined variable: data" notice (will fail on unfixed code)
7. **Asset Path Test**: Deploy on /HRIS/, request asset, assert 404 error (will fail on unfixed code)
8. **Consistency Test**: Trigger AuthenticationException in different controllers, assert inconsistent redirect targets (will fail on unfixed code)

**Expected Counterexamples**:
- Request object is null when controller methods try to use it
- PHP throws class redeclaration errors on bootstrap
- Controllers throw file-not-found or class-not-found errors
- Controller methods don't receive Request parameter
- Route parameters are ignored in favor of JSON body
- Templates throw undefined variable notices
- Assets return 404 on subdirectory deployments
- Different controllers redirect to different login paths

### Fix Checking

**Goal**: Verify that for all inputs where the bug conditions hold, the fixed functions produce the expected behavior.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := fixedSystem(input)
  ASSERT expectedBehavior(result)
END FOR
```

**Test Cases by Category**:

**Category 1: Request Injection**
```
FOR ALL controllerMethod IN affectedControllers DO
  request := createTestRequest()
  route := createTestRoute(controllerMethod)
  response := Router::dispatch(route, request)
  ASSERT response.statusCode IN [200, 400, 401, 403, 404] // No fatal error
  ASSERT NOT response.body.contains("Call to member function on null")
END FOR
```

**Category 2: Class Redeclaration**
```
// Bootstrap test
require 'src/Core/Router.php'
require 'src/Core/Request.php'
require 'src/Core/Response.php'
ASSERT NO_FATAL_ERROR
ASSERT class_exists('Core\Request')
ASSERT class_exists('Core\Response')
```

**Category 3: Legacy Dependencies**
```
FOR ALL method IN ['DashboardController::admin', 'DashboardController::metrics'] DO
  request := createAuthenticatedAdminRequest()
  response := method(request)
  ASSERT response.statusCode IN [200, 401, 403] // No fatal error
  ASSERT NOT response.body.contains("Failed opening required")
  ASSERT NOT response.body.contains("Class not found")
END FOR
```

**Category 4: Method Signatures**
```
FOR ALL controllerMethod IN affectedControllers DO
  reflection := new ReflectionMethod(controllerMethod)
  parameters := reflection.getParameters()
  ASSERT parameters.length >= 1
  ASSERT parameters[0].type == 'Core\Request'
END FOR
```

**Category 5: Route Parameters**
```
request := POST /api/leave/123/approve with body {}
response := LeaveController::approve(request)
ASSERT response.statusCode IN [200, 401, 403, 404] // Not 400 "ID required"
ASSERT NOT response.body.contains("Leave request ID is required")
```

**Category 6: View Data Access**
```
data := ['employees' => [...], 'pagination' => [...]]
html := View::render('employees/list', data)
ASSERT NOT html.contains("Undefined variable: data")
ASSERT html.contains(employees[0].name) // Extracted variable works
```

**Category 7: Asset Paths**
```
// Deploy on /HRIS/
html := renderTemplate()
ASSERT html.contains('/HRIS/assets/css/custom.css')
ASSERT NOT html.contains('"/assets/css/custom.css"') // Without base path
```

**Category 8: Consistency**
```
FOR ALL controller IN [DashboardController, EmployeeController, ReportController] DO
  TRY
    controller.requireAuth() // Will throw AuthenticationException
  CATCH AuthenticationException
    response := controller.redirectToLogin()
    ASSERT response.headers['Location'] == '/login'
    ASSERT NOT response.headers['Location'] == '/'
  END TRY
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug conditions do NOT hold, the fixed functions produce the same result as the original functions.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT fixedSystem(input) = originalSystem(input)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for non-buggy routes and features, then write property-based tests capturing that behavior.

**Test Cases**:
1. **Middleware Pipeline Preservation**: Verify middleware execution order and behavior remains unchanged for all routes
2. **Controller Helper Preservation**: Verify json(), success(), error(), view(), redirect() methods produce identical output
3. **View Layout Preservation**: Verify layout rendering and data extraction works identically for non-buggy templates
4. **Route Matching Preservation**: Verify route matching and parameter extraction works identically
5. **Authentication Preservation**: Verify AuthMiddleware user injection works identically
6. **Authorization Preservation**: Verify role-based access control works identically
7. **Logging Preservation**: Verify LoggingMiddleware captures same data
8. **Asset Serving Preservation**: Verify CSS and JavaScript files serve identically
9. **Domain Root Deployment Preservation**: Verify application works identically on domain root (no subdirectory)
10. **Request Parsing Preservation**: Verify Request class URI parsing works identically
11. **Response Sending Preservation**: Verify Response class status codes and headers work identically

### Unit Tests

- Test Router::dispatch() calls setRequest() before controller method invocation
- Test that duplicate Request/Response classes are removed from Router.php
- Test that all controller methods have correct (Request $request): Response signature
- Test that LeaveController::approve() and deny() read ID from route parameter
- Test that DashboardController uses Service layer instead of DatabaseHelper
- Test that templates access extracted variables instead of $data array
- Test that asset paths use base_url() helper
- Test that all controllers redirect to '/login' on AuthenticationException

### Property-Based Tests

- Generate random routes and verify Router::dispatch() always sets request before calling controller
- Generate random controller methods and verify all have Request parameter
- Generate random view data and verify templates can access all extracted variables
- Generate random deployment paths and verify asset paths are always correct
- Generate random authentication failures and verify consistent redirect behavior

### Integration Tests

- Test full request flow from Router to Controller to View with request injection
- Test application bootstrap with all classes loaded (no redeclaration errors)
- Test dashboard access with proper MVC Service layer usage
- Test leave approval/denial with route parameters
- Test employee list rendering with extracted variables
- Test asset loading on both subdirectory and domain root deployments
- Test authentication failure handling across all controllers
