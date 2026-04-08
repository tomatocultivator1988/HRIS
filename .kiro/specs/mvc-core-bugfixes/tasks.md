
# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - MVC Core Bugs Exploration
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bugs exist
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bugs exist across all 8 categories
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  - Test implementation details from Bug Condition in design:
    - Category 1: Request Injection - Call GET /api/employees/123, assert "Call to member function on null" error
    - Category 2: Class Redeclaration - Load Router.php and standalone Request.php, assert "Cannot redeclare class" error
    - Category 3: Legacy Dependencies - Call GET /dashboard/admin, assert "Failed opening required file" or "Class not found" error
    - Category 4: Method Signatures - Use reflection to check controller method signatures, assert missing Request parameter
    - Category 5: Route Parameters - Call POST /api/leave/123/approve with empty body, assert "Leave request ID is required" error
    - Category 6: View Data Access - Render employees/list view, assert "Undefined variable: data" notice
    - Category 7: Asset Paths - Deploy on /HRIS/, request asset, assert 404 error
    - Category 8: Consistency - Trigger AuthenticationException in different controllers, assert inconsistent redirect targets
  - The test assertions should match the Expected Behavior Properties from design
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bugs exist)
  - Document counterexamples found to understand root cause
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 6.1, 6.2, 6.3, 6.5, 6.6, 7.1, 7.4, 8.1, 8.2_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-Buggy Functionality Preservation
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs:
    - Middleware pipeline execution for working routes
    - Controller helper methods (json(), success(), error(), view(), redirect())
    - View layout rendering for non-buggy templates
    - Route matching and parameter extraction
    - AuthMiddleware user injection
    - Role-based access control
    - LoggingMiddleware request logging
    - Asset serving for CSS and JavaScript
    - Domain root deployment (no subdirectory)
    - Request class URI parsing
    - Response class status codes and headers
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 9.1, 9.3, 9.5, 9.13_

- [x] 3. Fix MVC Core Bugs

  - [x] 3.1 Fix Router request injection and class redeclaration (Category 1 & 2)
    - In Router::dispatch(), add $controller->setRequest($request) after controller instantiation
    - Remove duplicate Request/Response stub classes from Router.php (lines 321-500+)
    - Keep only the Route class definition in Router.php
    - Add setStartTime() and getStartTime() methods to standalone Request.php (they exist in stub but will be deleted)
    - _Bug_Condition: (routerDispatchCalled AND NOT controllerSetRequestCalled) OR (routerPhpLoaded AND standaloneRequestPhpLoaded)_
    - _Expected_Behavior: Router calls setRequest() before controller method invocation; no class redeclaration errors; LoggingMiddleware methods work_
    - _Preservation: Middleware pipeline, route matching, and parameter extraction must work identically_
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 8.1, 9.1_

  - [x] 3.2 Fix DashboardController legacy dependencies and signatures (Category 3 & 4)
    - Add Request parameter to method signatures: admin(Request $request), employee(Request $request), metrics(Request $request)
    - Create dashboard view files: src/Views/dashboard/admin.php and src/Views/dashboard/employee.php
    - Remove file_get_contents() of non-existent HTML files, use View::render() instead
    - Inject EmployeeModel, AttendanceModel, LeaveRequestModel via constructor
    - Replace DatabaseHelper static calls with Model methods (e.g., EmployeeModel::getActiveCount())
    - Change redirectToLogin() to redirect to '/login' instead of '/'
    - _Bug_Condition: (methodCallsLegacyCode) OR (methodSignature == '(): Response')_
    - _Expected_Behavior: Methods use proper MVC Model layer and have correct Request parameter_
    - _Preservation: Role-based access control and dashboard metrics must work identically_
    - _Requirements: 5.5, 5.6, 5.7, 6.1, 8.2, 9.3_

  - [x] 3.3 Fix LeaveController route parameters, signatures, and missing methods (Category 4 & 5 & 7)
    - Add Request parameter to method signatures: approve(Request $request), deny(Request $request), request(Request $request), pending(Request $request), history(Request $request)
    - Change approve() to use $this->getRouteParam('id') instead of $data['request_id']
    - Change deny() to use $this->getRouteParam('id') instead of $data['request_id']
    - Keep denial_reason from JSON body for deny()
    - Implement missing methods: balance(Request $request), types(Request $request), credits(Request $request)
    - DO NOT remove routes - they are referenced by frontend
    - _Bug_Condition: (methodSignature == '(): Response') OR (methodReadsFromJsonBodyInsteadOfRouteParam) OR (methodDoesNotExist)_
    - _Expected_Behavior: Methods read ID from route parameter, have correct Request parameter, and all referenced methods exist_
    - _Preservation: Permission validation and activity logging must work identically_
    - _Requirements: 6.1, 6.2, 6.3, 9.3_

  - [x] 3.4 Fix EmployeeController method signatures (Category 4)
    - Add Request parameter to all methods: index(), show(), create(), update(), delete(), search(), profile(), updateProfile()
    - Add Request parameter to API methods: apiIndex(), apiSearch(), apiCreate(), apiShow(), apiUpdate(), apiDelete()
    - Add Request parameter to view methods: indexView(), showView(), createForm(), editForm(), profileView()
    - Fix apiUpdate() to use getRouteParam() helper instead of direct $this->request->getRouteParameters()
    - _Bug_Condition: methodSignature == '(): Response'_
    - _Expected_Behavior: All methods have correct (Request $request): Response signature_
    - _Preservation: Support for both legacy .php endpoints and clean REST endpoints must work identically_
    - _Requirements: 6.1, 9.3_

  - [x] 3.5 Fix ReportController and AttendanceController signatures (Category 4)
    - Add Request parameter to all ReportController methods
    - Add Request parameter to all AttendanceController methods
    - In ReportController::index(), pass actual report data to the view
    - _Bug_Condition: methodSignature == '(): Response'_
    - _Expected_Behavior: All methods have correct (Request $request): Response signature_
    - _Preservation: Date format validation, filtering, and report generation must work identically_
    - _Requirements: 6.1, 9.3_

  - [x] 3.6 Fix AnnouncementController legacy dependencies (Category 3)
    - NOTE: Method signatures are ALREADY CORRECT - all have (Request $request): Response
    - Remove require of non-existent /api/announcements/AnnouncementManager.php
    - Create AnnouncementService with 6 methods: getAllAnnouncements(), getActiveAnnouncements(), getAnnouncement(), createAnnouncement(), updateAnnouncement(), deactivateAnnouncement()
    - Replace AnnouncementManager static calls with service methods
    - _Bug_Condition: methodCallsLegacyCode_
    - _Expected_Behavior: Methods use proper MVC Service layer_
    - _Preservation: Admin and employee access levels must work identically_
    - _Requirements: 5.5, 6.5, 9.3_

  - [x] 3.7 Fix view template variable access (Category 6)
    - In employees/list.php: Change $data['employees'] to $employees, $data['pagination'] to $pagination, $data['filters'] to $filters, $data['departments'] to $departments
    - In employees/profile.php: Change $data['employee'] to $employee, $data['canEdit'] to $canEdit, $data['isOwnProfile'] to $isOwnProfile
    - _Bug_Condition: templateAccessesDataArray_
    - _Expected_Behavior: Templates access extracted variables directly_
    - _Preservation: View layout support and data extraction must work identically_
    - _Requirements: 6.6, 9.5_

  - [x] 3.8 Fix asset paths for subdirectory deployment (Category 7)
    - Create base_url() helper function in src/Config/helpers.php
    - In layouts/base.php: Change hardcoded asset paths to use base_url() helper
    - In auth/login.php: Change hardcoded asset paths to use base_url() helper
    - Change /assets/css/custom.css to <?= base_url('/assets/css/custom.css') ?>
    - Change /assets/js/*.js to <?= base_url('/assets/js/*.js') ?>
    - In public/assets/js/auth.js: Change hardcoded '/HRIS/' check to use AppConfig.basePath
    - _Bug_Condition: (deploymentPath == '/HRIS/' AND assetPath STARTS_WITH '/' AND NOT assetPath STARTS_WITH '/HRIS/')_
    - _Expected_Behavior: Asset paths use dynamic base path for correct subdirectory deployment_
    - _Preservation: Asset serving and domain root deployment must work identically_
    - _Requirements: 7.1, 7.4, 9.13_

  - [x] 3.9 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - MVC Core Bugs Fixed
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bugs are fixed)
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 6.1, 6.2, 6.3, 6.5, 6.6, 7.1, 7.4, 8.1, 8.2_

  - [x] 3.10 Verify preservation tests still pass
    - **Property 2: Preservation** - Non-Buggy Functionality Preserved
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)
    - _Requirements: 9.1, 9.3, 9.5, 9.13_

- [x] 4. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
