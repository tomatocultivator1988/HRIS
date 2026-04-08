# Implementation Plan

## Phase 1: Exploration Tests (BEFORE Fix)

- [x] 1. Write bug condition exploration tests
  - **Property 1: Bug Condition** - Routing and Asset Path Bugs
  - **CRITICAL**: These tests MUST FAIL on unfixed code - failure confirms the bugs exist
  - **DO NOT attempt to fix the tests or the code when they fail**
  - **NOTE**: These tests encode the expected behavior - they will validate the fixes when they pass after implementation
  - **GOAL**: Surface counterexamples that demonstrate the 5 bugs exist
  - **Scoped PBT Approach**: Scope properties to concrete failing cases for reproducibility

  - [x] 1.1 Bug #1: Asset path doubling test
    - Test that requesting `/HRIS/assets/js/config.js` returns JavaScript content with `Content-Type: application/javascript`
    - Run on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (returns HTML instead of JavaScript, confirming Bug #1)
    - Document counterexample: "Request to `/HRIS/assets/js/config.js` returns HTML with 200 status instead of JavaScript content"
    - _Requirements: 1.1, 2.1_

  - [x] 1.2 Bug #2: Route shadowing test
    - Test that navigating to `/HRIS/employees/create` executes `EmployeeController@createForm` action
    - Run on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (executes `EmployeeController@showView` with id='create' instead, confirming Bug #2)
    - Document counterexample: "Route `/employees/create` matches parameterized route `/employees/{id}` with id='create'"
    - _Requirements: 1.2, 2.2_

  - [x] 1.3 Bug #3: Relative asset path test
    - Test that loading `/HRIS/login` page successfully loads all assets (CSS and JavaScript files)
    - Run on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (assets fail to load due to relative paths, confirming Bug #3)
    - Document counterexample: "Asset `assets/js/config.js` resolves to `/HRIS/login/assets/js/config.js` instead of `/HRIS/assets/js/config.js`"
    - _Requirements: 1.3, 2.3_

  - [x] 1.4 Bug #4: Missing web routes test
    - Test that navigating to `/HRIS/reports` and `/HRIS/profile` returns HTML views
    - Run on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (returns 404 errors, confirming Bug #4)
    - Document counterexample: "Routes `/reports` and `/profile` return 404 - no route definitions exist"
    - _Requirements: 1.4, 2.4_

  - [x] 1.5 Bug #5: Outdated redirect URLs test
    - Test that successful login redirects to `/HRIS/dashboard/admin` or `/HRIS/dashboard/employee` (without .html)
    - Run on UNFIXED code
    - **EXPECTED OUTCOME**: Test FAILS (redirects to .html URLs that don't exist, confirming Bug #5)
    - Document counterexample: "Login redirect URL is `/HRIS/dashboard/admin.html` instead of `/HRIS/dashboard/admin`"
    - _Requirements: 1.5, 2.5_

## Phase 2: Preservation Tests (BEFORE Fix)

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-Buggy Behavior Preservation
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (confirms baseline behavior to preserve)

  - [x] 2.1 Preservation: Other static assets continue working
    - Observe: CSS files, images, and fonts from `/HRIS/assets/...` are served correctly on unfixed code
    - Write property-based test: for all non-JavaScript asset requests (CSS, images, fonts), verify correct MIME types and content
    - Verify test passes on UNFIXED code
    - _Requirements: 3.1_

  - [x] 2.2 Preservation: Parameterized routes with numeric IDs
    - Observe: `/HRIS/employees/123` executes `EmployeeController@showView` with id=123 on unfixed code
    - Write property-based test: for all numeric employee IDs, verify correct route matching and controller execution
    - Verify test passes on UNFIXED code
    - _Requirements: 3.2_

  - [x] 2.3 Preservation: Authentication flow
    - Observe: Login form submission with valid credentials stores tokens in localStorage on unfixed code
    - Write test: verify authentication flow works correctly (credential validation, token storage)
    - Verify test passes on UNFIXED code
    - _Requirements: 3.3_

  - [x] 2.4 Preservation: API routes
    - Observe: API routes like `/api/reports/attendance`, `/api/employees` return JSON responses on unfixed code
    - Write property-based test: for all API routes, verify JSON responses and correct functionality
    - Verify test passes on UNFIXED code
    - _Requirements: 3.4_

  - [x] 2.5 Preservation: Logout functionality
    - Observe: Logout clears session data, invalidates tokens, and redirects to login page on unfixed code
    - Write test: verify logout behavior is unchanged
    - Verify test passes on UNFIXED code
    - _Requirements: 3.5_

  - [x] 2.6 Preservation: Security headers and configurations
    - Observe: Security headers, cache control, and compression settings are applied on unfixed code
    - Write test: verify .htaccess configurations continue to function
    - Verify test passes on UNFIXED code
    - _Requirements: 3.6_

  - [x] 2.7 Preservation: Middleware execution
    - Observe: Middleware (authentication, authorization, logging) executes in correct order on unfixed code
    - Write test: verify middleware execution order is unchanged
    - Verify test passes on UNFIXED code
    - _Requirements: 3.7_

## Phase 3: Implementation

- [x] 3. Fix Bug #1: Asset path doubling in .htaccess

  - [x] 3.1 Update .htaccess RewriteCond for asset serving
    - Change line 12 from `RewriteCond %{DOCUMENT_ROOT}/HRIS/public%{REQUEST_URI} -f` to `RewriteCond %{DOCUMENT_ROOT}/HRIS/public/assets/$1 -f`
    - Use captured group `$1` from RewriteRule pattern instead of full REQUEST_URI
    - Verify RewriteRule pattern `^assets/(.*)$` captures everything after `assets/` into `$1`
    - _Bug_Condition: isBugCondition(input) where input.uri matches '/HRIS/assets/.*' and htaccessCheckPath contains '/HRIS/HRIS/'_
    - _Expected_Behavior: Asset served with correct MIME type from correct file path_
    - _Preservation: Other static assets continue to be served correctly (3.1)_
    - _Requirements: 1.1, 2.1, 3.1_

  - [x] 3.2 Verify Bug #1 exploration test now passes
    - **Property 1: Expected Behavior** - Asset Serving with Correct MIME Types
    - **IMPORTANT**: Re-run the SAME test from task 1.1 - do NOT write a new test
    - Run test: Request `/HRIS/assets/js/config.js` and verify JavaScript content with correct MIME type
    - **EXPECTED OUTCOME**: Test PASSES (confirms Bug #1 is fixed)
    - _Requirements: 2.1_

- [x] 4. Fix Bug #2: Route shadowing in config/routes.php

  - [x] 4.1 Reorder employee routes to prevent shadowing
    - Move static route `GET /employees/create` to appear BEFORE parameterized route `GET /employees/{id}`
    - Move static route `GET /employees/{id}/edit` to appear BEFORE parameterized route `GET /employees/{id}`
    - Correct order: `/employees/create`, `/employees/{id}/edit`, `/employees/{id}`
    - Static routes must be defined before parameterized routes (first-match-wins logic)
    - _Bug_Condition: isBugCondition(input) where input.uri == '/HRIS/employees/create' and matchedRoute.handler == 'EmployeeController@showView'_
    - _Expected_Behavior: Router matches static route '/employees/create' and executes 'EmployeeController@createForm'_
    - _Preservation: Parameterized routes with numeric IDs continue to work correctly (3.2)_
    - _Requirements: 1.2, 2.2, 3.2_

  - [x] 4.2 Verify Bug #2 exploration test now passes
    - **Property 1: Expected Behavior** - Correct Route Matching for Static Routes
    - **IMPORTANT**: Re-run the SAME test from task 1.2 - do NOT write a new test
    - Run test: Navigate to `/HRIS/employees/create` and verify `EmployeeController@createForm` is executed
    - **EXPECTED OUTCOME**: Test PASSES (confirms Bug #2 is fixed)
    - _Requirements: 2.2_

- [x] 5. Fix Bug #3: Relative asset paths in login.php

  - [x] 5.1 Update asset paths to use absolute paths
    - Change line 8: `<link rel="stylesheet" href="assets/css/custom.css">` → `<link rel="stylesheet" href="/assets/css/custom.css">`
    - Change line 73: `<script src="assets/js/config.js"></script>` → `<script src="/assets/js/config.js"></script>`
    - Change line 74: `<script src="assets/js/auth.js"></script>` → `<script src="/assets/js/auth.js"></script>`
    - Change line 75: `<script src="assets/js/api.js"></script>` → `<script src="/assets/js/api.js"></script>`
    - Change line 76: `<script src="assets/js/utils.js"></script>` → `<script src="/assets/js/utils.js"></script>`
    - Change line 77: `<script src="assets/js/validation.js"></script>` → `<script src="/assets/js/validation.js"></script>`
    - Add leading slash to make paths absolute (consistent with base.php layout)
    - _Bug_Condition: isBugCondition(input) where input.uri == '/HRIS/login' and responseHTML contains 'src="assets/js/config.js"'_
    - _Expected_Behavior: Assets load correctly from any path using absolute paths_
    - _Preservation: Authentication flow continues to work correctly (3.3)_
    - _Requirements: 1.3, 2.3, 3.3_

  - [x] 5.2 Verify Bug #3 exploration test now passes
    - **Property 1: Expected Behavior** - Asset Loading from Any Path
    - **IMPORTANT**: Re-run the SAME test from task 1.3 - do NOT write a new test
    - Run test: Load `/HRIS/login` and verify all assets load successfully
    - **EXPECTED OUTCOME**: Test PASSES (confirms Bug #3 is fixed)
    - _Requirements: 2.3_

- [x] 6. Fix Bug #4: Missing web routes for navigation links

  - [x] 6.1 Add web route for /reports
    - Add route definition: `$router->addRoute('GET', '/reports', 'ReportController@index', ['logging', 'auth', 'role:admin']);`
    - Create `ReportController@index()` method that returns HTML view for reports page
    - Use same authentication and authorization as API routes
    - _Bug_Condition: isBugCondition(input) where input.uri == '/HRIS/reports' and matchedRoute == null_
    - _Expected_Behavior: Router matches web route and returns HTML view_
    - _Preservation: API routes continue to return JSON responses (3.4)_
    - _Requirements: 1.4, 2.4, 3.4_

  - [x] 6.2 Add web route for /profile
    - Add route definition: `$router->addRoute('GET', '/profile', 'EmployeeController@profile', ['logging', 'auth']);`
    - Create `EmployeeController@profile()` method that returns HTML view for employee profile page
    - Display current user's profile information
    - _Bug_Condition: isBugCondition(input) where input.uri == '/HRIS/profile' and matchedRoute == null_
    - _Expected_Behavior: Router matches web route and returns HTML view_
    - _Preservation: API routes continue to return JSON responses (3.4)_
    - _Requirements: 1.4, 2.4, 3.4_

  - [x] 6.3 Verify Bug #4 exploration test now passes
    - **Property 1: Expected Behavior** - Navigation Links Work
    - **IMPORTANT**: Re-run the SAME test from task 1.4 - do NOT write a new test
    - Run test: Navigate to `/HRIS/reports` and `/HRIS/profile` and verify HTML views are returned
    - **EXPECTED OUTCOME**: Test PASSES (confirms Bug #4 is fixed)
    - _Requirements: 2.4_

- [x] 7. Fix Bug #5: Outdated redirect URLs in auth.js

  - [x] 7.1 Remove .html extensions from redirect URLs
    - Change line 91: `return basePath + '/dashboard/admin.html';` → `return basePath + '/dashboard/admin';`
    - Change line 95: `return basePath + '/dashboard/employee.html';` → `return basePath + '/dashboard/employee';`
    - Update line 109: `window.location.href = window.AppConfig.url('index.html');` → `window.location.href = window.AppConfig.url('login');`
    - Search for other .html references in auth.js and update them (lines 177, 181, 185, 189, 193, 197)
    - _Bug_Condition: isBugCondition(input) where input.isLoginSuccess == true and redirectUrl contains '.html'_
    - _Expected_Behavior: Redirect to MVC routes without .html extensions_
    - _Preservation: Logout functionality continues to work correctly (3.5)_
    - _Requirements: 1.5, 2.5, 3.5_

  - [x] 7.2 Verify Bug #5 exploration test now passes
    - **Property 1: Expected Behavior** - Correct Login Redirects
    - **IMPORTANT**: Re-run the SAME test from task 1.5 - do NOT write a new test
    - Run test: Simulate successful login and verify redirect to correct MVC routes
    - **EXPECTED OUTCOME**: Test PASSES (confirms Bug #5 is fixed)
    - _Requirements: 2.5_

## Phase 4: Verification

- [x] 8. Verify all preservation tests still pass
  - **Property 2: Preservation** - Non-Buggy Behavior Preservation
  - **IMPORTANT**: Re-run the SAME tests from Phase 2 - do NOT write new tests
  - Run all preservation tests from task 2
  - **EXPECTED OUTCOME**: All tests PASS (confirms no regressions)
  - Verify: Other static assets (2.1), Parameterized routes (2.2), Authentication flow (2.3), API routes (2.4), Logout (2.5), Security headers (2.6), Middleware (2.7)
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

- [x] 9. Checkpoint - Ensure all tests pass
  - Ensure all exploration tests pass (Bug #1-5 fixed)
  - Ensure all preservation tests pass (no regressions)
  - Ask the user if questions arise
