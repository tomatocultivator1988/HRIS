# Routing and Asset Path Fixes Bugfix Design

## Overview

This design addresses 5 critical bugs in the HRIS MVC system that prevent proper routing, asset loading, and navigation. The bugs stem from incorrect .htaccess path handling (doubled `/HRIS/` prefix), improper route ordering (parameterized routes shadowing static routes), inconsistent asset path conventions (relative vs absolute paths), missing web routes for navigation links, and outdated redirect URLs in JavaScript (pointing to non-existent .html files).

The fix approach involves: (1) correcting the .htaccess RewriteCond to check the correct file path without doubling the base path, (2) reordering routes in config/routes.php to place static routes before parameterized routes, (3) standardizing all asset paths in login.php to use absolute paths with leading slashes, (4) adding missing web routes for `/reports` and `/profile` with appropriate controller actions, and (5) updating auth.js redirect URLs to use MVC routes without .html extensions.

## Glossary

- **Bug_Condition (C)**: The condition that triggers each of the 5 bugs - asset requests with doubled paths, navigation to shadowed routes, relative asset paths in login.php, clicks on navigation links without routes, and successful login redirects
- **Property (P)**: The desired behavior - assets served with correct MIME types, correct route matching, assets loading from any path, navigation links working, and redirects to valid MVC routes
- **Preservation**: Existing functionality that must remain unchanged - other static asset serving, parameterized routes with numeric IDs, authentication flow, API routes, logout functionality, security headers, and middleware execution
- **.htaccess**: Apache configuration file in the project root that handles URL rewriting and asset serving for the XAMPP environment with `/HRIS/` base path
- **RewriteCond**: Apache directive that checks conditions before applying rewrite rules - currently incorrectly doubles the `/HRIS/` path when checking if asset files exist
- **Router**: The `Core\Router` class in `src/Core/Router.php` that matches incoming requests to route definitions using regex patterns and first-match-wins logic
- **Route Shadowing**: When a parameterized route like `/employees/{id}` matches before a static route like `/employees/create`, causing the static route to never be reached
- **Front Controller**: The `public/index.php` file that serves as the single entry point for all requests, loading routes and dispatching to controllers
- **Base Path**: The `/HRIS/` prefix used in the XAMPP environment to access the application (configured in .htaccess RewriteBase)
- **Absolute Path**: Asset paths starting with `/` that resolve relative to the domain root (e.g., `/assets/js/config.js` resolves to `/HRIS/assets/js/config.js` with RewriteBase)
- **Relative Path**: Asset paths without leading `/` that resolve relative to the current page URL (e.g., `assets/js/config.js` from `/HRIS/login` resolves to `/HRIS/login/assets/js/config.js`)

## Bug Details

### Bug Condition

The bugs manifest in 5 distinct scenarios:

**Bug #1 - Asset Path Doubling**: When the browser requests `/HRIS/assets/js/config.js`, the .htaccess RewriteCond checks `DOCUMENT_ROOT/HRIS/public/HRIS/assets/js/config.js` (doubled path), the file check fails, and the request falls through to index.php which returns HTML instead of JavaScript.

**Bug #2 - Route Shadowing**: When a user navigates to `/HRIS/employees/create`, the router matches the `GET /employees/{id}` route first with `{id}='create'` because it appears earlier in the route list, and the `GET /employees/create` route is never reached.

**Bug #3 - Relative Asset Paths**: When login.php is served from `/HRIS/login` and uses relative paths `assets/css/custom.css` and `assets/js/config.js`, the browser resolves these to `/HRIS/login/assets/...` which triggers Bug #1.

**Bug #4 - Missing Web Routes**: When a user clicks the "Reports" link (`/reports`) in the admin navigation or "My Profile" link (`/profile`) in the employee navigation, the router finds no matching route and returns a 404 error.

**Bug #5 - Outdated Redirect URLs**: When a user successfully logs in, auth.js redirects to `dashboard/admin.html` or `dashboard/employee.html` which no longer exist in the MVC system (routes are `/dashboard/admin` and `/dashboard/employee` without .html).

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type HTTPRequest
  OUTPUT: boolean
  
  // Bug #1: Asset path doubling
  IF input.uri MATCHES '/HRIS/assets/.*' THEN
    htaccessCheckPath := DOCUMENT_ROOT + '/HRIS/public' + input.uri
    IF htaccessCheckPath CONTAINS '/HRIS/HRIS/' THEN
      RETURN true  // Doubled path bug
    END IF
  END IF
  
  // Bug #2: Route shadowing
  IF input.uri == '/HRIS/employees/create' AND input.method == 'GET' THEN
    matchedRoute := router.match(input)
    IF matchedRoute.handler == 'EmployeeController@showView' THEN
      RETURN true  // Wrong route matched
    END IF
  END IF
  
  // Bug #3: Relative asset paths
  IF input.uri == '/HRIS/login' AND input.method == 'GET' THEN
    responseHTML := getResponse(input)
    IF responseHTML CONTAINS 'src="assets/js/config.js"' THEN
      RETURN true  // Relative path will fail
    END IF
  END IF
  
  // Bug #4: Missing web routes
  IF input.uri IN ['/HRIS/reports', '/HRIS/profile'] AND input.method == 'GET' THEN
    matchedRoute := router.match(input)
    IF matchedRoute == null THEN
      RETURN true  // No route exists
    END IF
  END IF
  
  // Bug #5: Outdated redirect URLs
  IF input.isLoginSuccess == true THEN
    redirectUrl := auth.getRedirectUrl()
    IF redirectUrl CONTAINS '.html' THEN
      RETURN true  // Outdated URL format
    END IF
  END IF
  
  RETURN false
END FUNCTION
```

### Examples

**Bug #1 Example**:
- Request: `GET /HRIS/assets/js/config.js`
- .htaccess checks: `/var/www/html/HRIS/public/HRIS/assets/js/config.js` (doubled)
- File check fails (actual file is at `/var/www/html/HRIS/public/assets/js/config.js`)
- Request falls through to index.php
- Response: HTML content with 200 status
- Browser error: "Strict MIME type checking enforced for module scripts"

**Bug #2 Example**:
- Request: `GET /HRIS/employees/create`
- Router checks routes in order:
  1. `/employees/{id}` matches with `{id}='create'` ✓ (first match wins)
  2. `/employees/create` never checked
- Controller action: `EmployeeController@showView` with `id='create'`
- Expected: `EmployeeController@createForm`

**Bug #3 Example**:
- Request: `GET /HRIS/login`
- Response: login.php with `<script src="assets/js/config.js"></script>`
- Browser resolves: `/HRIS/login/assets/js/config.js` (relative to current path)
- Actual file location: `/HRIS/assets/js/config.js`
- Result: 404 error or triggers Bug #1

**Bug #4 Example**:
- User clicks "Reports" link in admin navigation
- Request: `GET /HRIS/reports`
- Router finds no matching route (only `/api/reports/attendance` exists)
- Response: 404 "Page not found"

**Bug #5 Example**:
- User logs in successfully as admin
- auth.js executes: `window.location.href = basePath + '/dashboard/admin.html'`
- Request: `GET /HRIS/dashboard/admin.html`
- Router finds no matching route (route is `/dashboard/admin` without .html)
- Response: 404 "Page not found"

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Other static assets (CSS files, images, fonts) from `/HRIS/assets/...` must continue to be served directly from the public directory with correct MIME types
- Parameterized routes with numeric IDs (e.g., `/employees/123`) must continue to match the `GET /employees/{id}` route and execute the `showView` controller action
- Authentication flow (credential validation, token storage in localStorage) must continue to work correctly when login.php loads and the user submits credentials
- API routes (e.g., `/api/reports/attendance`, `/api/employees`, `/api/auth/login`) must continue to return JSON responses and function as expected
- Logout functionality must continue to clear session data, invalidate tokens, and redirect to the login page
- Security headers, cache control, and compression settings in .htaccess must continue to function as defined
- Middleware execution (authentication, authorization, logging) must continue to execute in the correct order for all routes

**Scope:**
All inputs that do NOT involve the 5 specific bug conditions should be completely unaffected by this fix. This includes:
- Asset requests for non-JavaScript files (CSS, images, fonts)
- Navigation to parameterized routes with numeric IDs
- Form submissions and API calls
- Logout and session management
- Security and performance configurations

## Hypothesized Root Cause

Based on the bug description and code analysis, the root causes are:

**Bug #1 - Asset Path Doubling**:
1. **Incorrect RewriteCond Path Construction**: The .htaccess file at line 12 uses `RewriteCond %{DOCUMENT_ROOT}/HRIS/public%{REQUEST_URI} -f` which concatenates `DOCUMENT_ROOT` + `/HRIS/public` + `REQUEST_URI`
2. **REQUEST_URI Already Contains Base Path**: When the browser requests `/HRIS/assets/js/config.js`, `REQUEST_URI` is `/HRIS/assets/js/config.js` (includes the `/HRIS/` base path)
3. **Path Doubling**: The concatenation results in `DOCUMENT_ROOT/HRIS/public/HRIS/assets/js/config.js` (doubled `/HRIS/`)
4. **File Check Fails**: The file doesn't exist at the doubled path, so the RewriteCond fails and the request falls through to the RewriteRule that sends it to index.php

**Bug #2 - Route Shadowing**:
1. **First-Match-Wins Logic**: The Router class in `src/Core/Router.php` uses a foreach loop that returns the first matching route
2. **Incorrect Route Order**: In `config/routes.php`, the parameterized route `GET /employees/{id}` (line 67) appears before the static route `GET /employees/create` (line 68)
3. **Greedy Pattern Matching**: The regex pattern for `/employees/{id}` is `/^\/employees\/(?P<id>[^\/]+)$/` which matches any non-slash characters, including the literal string "create"
4. **Static Route Never Reached**: When the router processes `/employees/create`, it matches the parameterized route first and never checks the static route

**Bug #3 - Relative Asset Paths**:
1. **Inconsistent Path Convention**: The login.php file uses relative paths `assets/css/custom.css` and `assets/js/config.js` (lines 8 and 73)
2. **Browser Path Resolution**: When login.php is served from `/HRIS/login`, the browser resolves relative paths relative to the current page URL
3. **Incorrect Resolution**: `assets/js/config.js` resolves to `/HRIS/login/assets/js/config.js` instead of `/HRIS/assets/js/config.js`
4. **Triggers Bug #1**: The incorrect path triggers the asset path doubling bug, causing the asset to fail to load

**Bug #4 - Missing Web Routes**:
1. **API Routes Only**: The `config/routes.php` file defines API routes for reports (`/api/reports/attendance`, line 52) but no web routes for `/reports`
2. **Navigation Links Exist**: The base layout (`src/Views/layouts/base.php`) includes navigation links to `/reports` (line 20) and `/profile` (line 23)
3. **No Route Definitions**: No corresponding route definitions exist in `config/routes.php` for these web routes
4. **Router Returns Null**: When the router tries to match these requests, it returns null and the front controller returns a 404 response

**Bug #5 - Outdated Redirect URLs**:
1. **Legacy HTML File References**: The auth.js file (line 91) constructs redirect URLs using `basePath + '/dashboard/admin.html'` and `basePath + '/dashboard/employee.html'`
2. **MVC Routes Don't Use Extensions**: The MVC routing system uses clean URLs without file extensions (e.g., `/dashboard/admin` not `/dashboard/admin.html`)
3. **Routes Defined Without Extensions**: The `config/routes.php` file defines routes as `/dashboard/admin` (line 60) and `/dashboard/employee` (line 61) without .html extensions
4. **404 After Login**: When auth.js redirects to the .html URLs, the router finds no matching route and returns a 404 error

## Correctness Properties

Property 1: Bug Condition - Asset Serving with Correct MIME Types

_For any_ HTTP request where the URI matches `/HRIS/assets/.*` and the corresponding file exists in `public/assets/`, the fixed .htaccess SHALL check the correct file path without doubling the base path, find the file, and serve it with the correct MIME type (e.g., `application/javascript` for .js files).

**Validates: Requirements 2.1**

Property 2: Bug Condition - Correct Route Matching for Static Routes

_For any_ HTTP GET request to `/HRIS/employees/create`, the fixed router SHALL match the static route `/employees/create` before the parameterized route `/employees/{id}` and execute the `EmployeeController@createForm` controller action.

**Validates: Requirements 2.2**

Property 3: Bug Condition - Asset Loading from Any Path

_For any_ HTTP GET request to `/HRIS/login` or any other path serving login.php, the fixed login.php SHALL use absolute paths with leading slashes for all asset references, ensuring assets load correctly regardless of the request path.

**Validates: Requirements 2.3**

Property 4: Bug Condition - Navigation Links Work

_For any_ HTTP GET request to `/HRIS/reports` (admin navigation) or `/HRIS/profile` (employee navigation), the fixed routing system SHALL match a web route handler that returns an HTML view with appropriate content.

**Validates: Requirements 2.4**

Property 5: Bug Condition - Correct Login Redirects

_For any_ successful login event, the fixed auth.js SHALL redirect to `/HRIS/dashboard/admin` or `/HRIS/dashboard/employee` (MVC routes without .html extension) matching the MVC routing structure.

**Validates: Requirements 2.5**

Property 6: Preservation - Other Static Assets Continue Working

_For any_ HTTP request to static assets (CSS, images, fonts) from `/HRIS/assets/...` that are NOT affected by Bug #1, the fixed system SHALL produce exactly the same behavior as the original system, serving them directly from the public directory with correct MIME types.

**Validates: Requirements 3.1**

Property 7: Preservation - Parameterized Routes with Numeric IDs

_For any_ HTTP GET request to `/HRIS/employees/{id}` where `{id}` is a numeric value (e.g., `/employees/123`), the fixed router SHALL produce exactly the same behavior as the original router, matching the parameterized route and executing the `EmployeeController@showView` controller action.

**Validates: Requirements 3.2**

Property 8: Preservation - Authentication Flow

_For any_ form submission on login.php with valid credentials, the fixed system SHALL produce exactly the same authentication behavior as the original system, validating credentials and storing tokens in localStorage.

**Validates: Requirements 3.3**

Property 9: Preservation - API Routes

_For any_ HTTP request to API routes (e.g., `/api/reports/attendance`, `/api/employees`, `/api/auth/login`), the fixed system SHALL produce exactly the same behavior as the original system, returning JSON responses and functioning as expected.

**Validates: Requirements 3.4**

Property 10: Preservation - Logout Functionality

_For any_ logout action, the fixed system SHALL produce exactly the same behavior as the original system, clearing session data, invalidating tokens, and redirecting to the login page.

**Validates: Requirements 3.5**

Property 11: Preservation - Security and Performance Configurations

_For any_ HTTP request, the fixed .htaccess SHALL continue to apply security headers, cache control, and compression settings exactly as defined in the original configuration.

**Validates: Requirements 3.6**

Property 12: Preservation - Middleware Execution

_For any_ HTTP request that triggers middleware (authentication, authorization, logging), the fixed router SHALL execute middleware in exactly the same order as the original router.

**Validates: Requirements 3.7**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `.htaccess`

**Function**: Asset serving rewrite rules

**Specific Changes**:
1. **Fix RewriteCond Path**: Change line 12 from `RewriteCond %{DOCUMENT_ROOT}/HRIS/public%{REQUEST_URI} -f` to `RewriteCond %{DOCUMENT_ROOT}/HRIS/public/assets/$1 -f`
   - This checks the correct file path by using the captured group `$1` from the RewriteRule pattern instead of the full REQUEST_URI
   - The pattern `^assets/(.*)$` captures everything after `assets/` into `$1`
   - The concatenation becomes `DOCUMENT_ROOT/HRIS/public/assets/{captured_path}` without doubling the base path

2. **Update RewriteRule Pattern**: Change line 11 from `RewriteCond %{REQUEST_URI} ^/HRIS/assets/` to `RewriteCond %{REQUEST_URI} ^/HRIS/assets/` (no change needed, but verify the pattern matches correctly)

3. **Verify RewriteRule Target**: Ensure line 13 `RewriteRule ^assets/(.*)$ public/assets/$1 [L]` correctly serves the file from the public directory

**File**: `config/routes.php`

**Function**: Route definitions

**Specific Changes**:
1. **Reorder Employee Routes**: Move the static route `GET /employees/create` (line 68) to appear BEFORE the parameterized route `GET /employees/{id}` (line 67)
   - Static routes must be defined before parameterized routes to prevent shadowing
   - The router uses first-match-wins logic, so order matters

2. **Reorder Employee Edit Route**: Move the static route `GET /employees/{id}/edit` (line 69) to appear AFTER the parameterized route `GET /employees/{id}` (line 67)
   - This route has a more specific pattern (`/employees/{id}/edit`) so it should come before the less specific pattern (`/employees/{id}`)
   - Correct order: `/employees/create`, `/employees/{id}/edit`, `/employees/{id}`

3. **Add Missing Web Routes**: Add new route definitions for `/reports` and `/profile`
   - `$router->addRoute('GET', '/reports', 'ReportController@index', ['logging', 'auth', 'role:admin']);`
   - `$router->addRoute('GET', '/profile', 'EmployeeController@profile', ['logging', 'auth']);`

**File**: `src/Views/auth/login.php`

**Function**: Asset path references

**Specific Changes**:
1. **Fix CSS Path**: Change line 8 from `<link rel="stylesheet" href="assets/css/custom.css">` to `<link rel="stylesheet" href="/assets/css/custom.css">`
   - Add leading slash to make it an absolute path

2. **Fix JavaScript Paths**: Change lines 73-77 from relative paths to absolute paths:
   - `<script src="assets/js/config.js"></script>` → `<script src="/assets/js/config.js"></script>`
   - `<script src="assets/js/auth.js"></script>` → `<script src="/assets/js/auth.js"></script>`
   - `<script src="assets/js/api.js"></script>` → `<script src="/assets/js/api.js"></script>`
   - `<script src="assets/js/utils.js"></script>` → `<script src="/assets/js/utils.js"></script>`
   - `<script src="assets/js/validation.js"></script>` → `<script src="/assets/js/validation.js"></script>`

**File**: `public/assets/js/auth.js`

**Function**: `getRedirectUrl()` method

**Specific Changes**:
1. **Remove .html Extensions**: Change lines 91-95 to remove .html extensions from redirect URLs:
   - `return basePath + '/dashboard/admin.html';` → `return basePath + '/dashboard/admin';`
   - `return basePath + '/dashboard/employee.html';` → `return basePath + '/dashboard/employee';`

2. **Update Other .html References**: Search for any other references to .html files in auth.js and update them:
   - Line 109: `window.location.href = window.AppConfig.url('index.html');` → `window.location.href = window.AppConfig.url('login');` or `window.location.href = window.AppConfig.url('/');`
   - Lines 177, 181, 185, 189, 193, 197: Update all `index.html` references to use `/login` or `/` instead

**File**: `src/Controllers/ReportController.php` (new method)

**Function**: Add `index()` method for web route

**Specific Changes**:
1. **Add Web Route Handler**: Create a new method `index()` in `ReportController` that returns an HTML view for the reports page
   - This method should render a view that displays the reports interface
   - It should use the same authentication and authorization as the API routes

**File**: `src/Controllers/EmployeeController.php` (new method)

**Function**: Add `profile()` method for web route

**Specific Changes**:
1. **Add Web Route Handler**: Create a new method `profile()` in `EmployeeController` that returns an HTML view for the employee profile page
   - This method should render a view that displays the current user's profile
   - It should use the same authentication as other employee routes

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bugs on unfixed code, then verify the fixes work correctly and preserve existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bugs BEFORE implementing the fixes. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate the 5 bug conditions and assert that the expected failures occur. Run these tests on the UNFIXED code to observe failures and understand the root causes.

**Test Cases**:
1. **Asset Path Doubling Test**: Request `/HRIS/assets/js/config.js` and verify that the response is HTML instead of JavaScript (will fail on unfixed code)
2. **Route Shadowing Test**: Navigate to `/HRIS/employees/create` and verify that the wrong controller action is executed (will fail on unfixed code)
3. **Relative Asset Path Test**: Load `/HRIS/login` and verify that asset requests fail with 404 or MIME type errors (will fail on unfixed code)
4. **Missing Web Route Test**: Navigate to `/HRIS/reports` and `/HRIS/profile` and verify 404 responses (will fail on unfixed code)
5. **Outdated Redirect Test**: Simulate successful login and verify that the redirect URL contains `.html` (will fail on unfixed code)

**Expected Counterexamples**:
- Asset requests return HTML content instead of JavaScript/CSS
- Static routes are shadowed by parameterized routes
- Relative asset paths resolve to incorrect URLs
- Navigation links return 404 errors
- Login redirects to non-existent .html URLs

### Fix Checking

**Goal**: Verify that for all inputs where the bug conditions hold, the fixed system produces the expected behavior.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := fixedSystem(input)
  ASSERT expectedBehavior(result)
END FOR
```

**Test Cases**:
1. **Asset Serving Test**: Request `/HRIS/assets/js/config.js` and verify response has `Content-Type: application/javascript` and correct file content
2. **Route Matching Test**: Navigate to `/HRIS/employees/create` and verify `EmployeeController@createForm` is executed
3. **Asset Loading Test**: Load `/HRIS/login` and verify all assets load successfully with correct MIME types
4. **Web Route Test**: Navigate to `/HRIS/reports` and `/HRIS/profile` and verify HTML views are returned
5. **Redirect Test**: Simulate successful login and verify redirect to `/HRIS/dashboard/admin` or `/HRIS/dashboard/employee` without .html

### Preservation Checking

**Goal**: Verify that for all inputs where the bug conditions do NOT hold, the fixed system produces the same result as the original system.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT originalSystem(input) = fixedSystem(input)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for non-bug inputs, then write property-based tests capturing that behavior.

**Test Cases**:
1. **Other Static Assets Preservation**: Request CSS files, images, and fonts from `/HRIS/assets/...` and verify they continue to be served correctly
2. **Parameterized Routes Preservation**: Navigate to `/HRIS/employees/123` (numeric ID) and verify `EmployeeController@showView` is executed with correct parameters
3. **Authentication Flow Preservation**: Submit login form with valid credentials and verify tokens are stored in localStorage
4. **API Routes Preservation**: Call API endpoints like `/api/reports/attendance` and verify JSON responses are returned
5. **Logout Preservation**: Trigger logout and verify session data is cleared and redirect to login page occurs
6. **Security Headers Preservation**: Make any request and verify security headers are present in the response
7. **Middleware Preservation**: Make authenticated requests and verify middleware executes in correct order

### Unit Tests

- Test .htaccess rewrite rules with various asset paths (JavaScript, CSS, images, fonts)
- Test route matching with static routes before and after parameterized routes
- Test asset path resolution with absolute and relative paths
- Test web route handlers return HTML views with correct content
- Test redirect URL construction with and without .html extensions

### Property-Based Tests

- Generate random asset paths and verify they are served with correct MIME types
- Generate random employee IDs (numeric and non-numeric) and verify correct route matching
- Generate random login scenarios and verify redirects to correct URLs
- Generate random navigation scenarios and verify all links work correctly

### Integration Tests

- Test full user flow: login → navigate to employees → create new employee → view employee profile
- Test admin flow: login → navigate to reports → view report → logout
- Test employee flow: login → navigate to profile → view profile → logout
- Test asset loading across different pages and contexts
- Test route matching across all defined routes with various inputs
