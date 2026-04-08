# Bugfix Requirements Document

## Introduction

This document addresses 16 critical bugs in the HRIS MVC system that prevent core functionality from working correctly. These bugs span across the Router, Controllers, Middleware, and View layers, causing fatal errors, incorrect behavior, and deployment issues. The bugs are categorized by severity:

- **FATAL (4 bugs)**: Cause immediate application crashes or complete feature failures
- **HIGH (5 bugs)**: Break core functionality but don't crash the entire application
- **MEDIUM (3 bugs)**: Cause incorrect behavior or deployment issues
- **LOW (3 bugs)**: Code quality issues that contribute to other bugs

The fixes will restore proper request handling, eliminate duplicate class declarations, fix method signatures, remove legacy dependencies, correct view rendering, and resolve deployment path issues.

## Bug Analysis

### Current Behavior (Defect)

#### FATAL BUGS

1.1 WHEN Router::dispatch() calls a controller method THEN the system never calls setRequest() on the controller before invoking the method, causing $this->request to remain null

1.2 WHEN a controller method calls getRouteParam(), getQueryParam(), getJsonData(), or getAuthenticatedUser() THEN the system throws "Call to member function on null" fatal error because $this->request is null

1.3 WHEN both Router.php and standalone Request.php/Response.php files are loaded THEN PHP throws "Cannot redeclare class Core\Request" and "Cannot redeclare class Core\Response" fatal errors

1.4 WHEN LoggingMiddleware::handle() calls $request->setStartTime() or LoggingMiddleware::logResponse() calls $request->getStartTime() THEN the system throws "Call to undefined method" fatal error because standalone Request.php doesn't have these methods

1.5 WHEN DashboardController methods try to load dashboard HTML files THEN the system throws file-not-found fatal error because /dashboard/admin.html and /dashboard/employee.html don't exist

1.6 WHEN DashboardController methods try to require legacy API files THEN the system throws file-not-found fatal error because /api/config/database.php doesn't exist

1.7 WHEN DashboardController methods call \DatabaseHelper::count() or \DatabaseHelper::select() THEN the system throws "Class not found" fatal error because DatabaseHelper doesn't exist in MVC architecture

#### HIGH SEVERITY BUGS

2.1 WHEN Router calls $controller->$method($request) on AttendanceController, DashboardController, EmployeeController, LeaveController, or ReportController methods THEN the system silently drops the $request argument in PHP 8 strict mode because methods declare (): Response with no parameter

2.2 WHEN a client calls POST /api/leave/{id}/approve with ID in URL THEN LeaveController::approve() ignores the route parameter and reads $data['request_id'] from JSON body instead, requiring ID to be sent twice

2.3 WHEN a client calls POST /api/leave/{id}/deny with ID in URL THEN LeaveController::deny() ignores the route parameter and reads $data['request_id'] from JSON body instead, requiring ID to be sent twice

2.4 WHEN routes reference LeaveController@balance, LeaveController@types, or LeaveController@credits THEN the system throws "Method not found in controller" fatal error because these methods don't exist

2.5 WHEN AnnouncementController methods execute THEN the system throws file-not-found or "Class not found" fatal error because they require non-existent /api/announcements/AnnouncementManager.php and call \AnnouncementManager static methods

2.6 WHEN View::render() calls extract($data, EXTR_SKIP) to make variables directly available THEN templates that access $data['employees'] or $data['employee'] get undefined variable notices because variables are extracted as $employees and $employee

#### MEDIUM SEVERITY BUGS

3.1 WHEN the application runs on XAMPP at /HRIS/ subdirectory THEN asset paths like /assets/css/custom.css and /assets/js/*.js return 404 errors because they're root-relative without /HRIS prefix

3.2 WHEN EmployeeController::apiUpdate() calls $this->request->getRouteParameters() or $this->request->setRouteParameters() THEN the system throws null-dereference fatal error because Router never sets $this->request (Bug #1)

3.3 WHEN ReportController::index() renders the reports view THEN the page displays only title and user data without actual report data because the method doesn't pass report data to the view

3.4 WHEN the application is deployed on a domain-root (e.g., hris.company.com/) THEN auth.js redirect-after-login logic breaks because it checks hardcoded window.location.pathname === '/HRIS/'

#### LOW SEVERITY BUGS

4.1 WHEN Router.php is loaded THEN duplicate Request and Response stub classes at lines 321-500+ remain in the file after extraction to standalone files, directly causing Bug #1.3

4.2 WHEN DashboardController catches AuthenticationException THEN it redirects to '/', but when EmployeeController or ReportController catch AuthenticationException THEN they redirect to '/login', causing inconsistent user experience

4.3 WHEN public/index.php uses inline Request/Response use statements THEN after Bug #1.3 is fixed, the code needs verification to ensure it still works correctly with standalone classes

### Expected Behavior (Correct)

#### FATAL BUGS - FIXES

5.1 WHEN Router::dispatch() calls a controller method THEN the system SHALL call $controller->setRequest($request) before invoking $controller->$method($request)

5.2 WHEN a controller method calls getRouteParam(), getQueryParam(), getJsonData(), or getAuthenticatedUser() THEN the system SHALL execute successfully because $this->request is properly set

5.3 WHEN both Router.php and standalone Request.php/Response.php files are loaded THEN PHP SHALL load only the standalone classes without redeclaration errors because stub classes are removed from Router.php

5.4 WHEN LoggingMiddleware::handle() calls $request->setStartTime() or LoggingMiddleware::logResponse() calls $request->getStartTime() THEN the system SHALL execute successfully because standalone Request.php has these methods

5.5 WHEN DashboardController methods need to display dashboard content THEN the system SHALL render proper MVC views instead of trying to load non-existent HTML files

5.6 WHEN DashboardController methods need database access THEN the system SHALL use MVC Model classes instead of trying to require non-existent legacy API files

5.7 WHEN DashboardController methods need to query data THEN the system SHALL use proper Service layer methods instead of calling non-existent DatabaseHelper static methods

#### HIGH SEVERITY BUGS - FIXES

6.1 WHEN Router calls $controller->$method($request) on any controller method THEN the system SHALL successfully pass the $request parameter because all controller methods declare (Request $request): Response signature

6.2 WHEN a client calls POST /api/leave/{id}/approve with ID in URL THEN LeaveController::approve() SHALL read the ID from route parameter using getRouteParam('id') instead of requiring it in JSON body

6.3 WHEN a client calls POST /api/leave/{id}/deny with ID in URL THEN LeaveController::deny() SHALL read the ID from route parameter using getRouteParam('id') instead of requiring it in JSON body

6.4 WHEN routes reference LeaveController@balance, LeaveController@types, or LeaveController@credits THEN the system SHALL execute successfully because these methods exist in LeaveController or routes are removed if methods are not needed

6.5 WHEN AnnouncementController methods execute THEN the system SHALL use proper MVC Service layer instead of trying to require non-existent legacy API files and call non-existent static methods

6.6 WHEN View::render() calls extract($data, EXTR_SKIP) to make variables directly available THEN templates SHALL access variables directly as $employees and $employee instead of $data['employees'] and $data['employee']

#### MEDIUM SEVERITY BUGS - FIXES

7.1 WHEN the application runs on XAMPP at /HRIS/ subdirectory THEN asset paths SHALL use AppConfig.basePath or base_url() helper to generate correct paths like /HRIS/assets/css/custom.css

7.2 WHEN EmployeeController::apiUpdate() needs route parameters THEN the system SHALL use helper methods getRouteParam() instead of directly accessing $this->request->getRouteParameters()

7.3 WHEN ReportController::index() renders the reports view THEN the page SHALL display actual report data because the method passes report data to the view

7.4 WHEN the application is deployed on any base path THEN auth.js redirect-after-login logic SHALL work correctly because it uses AppConfig.basePath instead of hardcoded '/HRIS/' check

#### LOW SEVERITY BUGS - FIXES

8.1 WHEN Router.php is loaded THEN the system SHALL not contain duplicate Request and Response stub classes because they are removed after extraction to standalone files

8.2 WHEN any controller catches AuthenticationException THEN the system SHALL consistently redirect to '/login' for uniform user experience

8.3 WHEN public/index.php uses inline Request/Response use statements THEN the system SHALL work correctly with standalone classes after Bug #1.3 is fixed

### Unchanged Behavior (Regression Prevention)

9.1 WHEN Router matches a route and dispatches to a controller THEN the system SHALL CONTINUE TO execute middleware pipeline before controller invocation

9.2 WHEN Router injects route parameters into request THEN the system SHALL CONTINUE TO make parameters available via getRouteParameter() method

9.3 WHEN Controller base class provides helper methods THEN the system SHALL CONTINUE TO support json(), success(), error(), view(), and redirect() methods

9.4 WHEN AuthMiddleware validates authentication THEN the system SHALL CONTINUE TO set user data on request via setUser() method

9.5 WHEN View::render() processes templates THEN the system SHALL CONTINUE TO support layouts and extract data variables for template access

9.6 WHEN EmployeeController handles API requests THEN the system SHALL CONTINUE TO support both legacy .php endpoints and clean REST endpoints

9.7 WHEN LeaveController processes leave requests THEN the system SHALL CONTINUE TO validate user permissions and log activities

9.8 WHEN DashboardController serves dashboard pages THEN the system SHALL CONTINUE TO enforce role-based access control

9.9 WHEN ReportController generates reports THEN the system SHALL CONTINUE TO validate date formats and filter parameters

9.10 WHEN AnnouncementController handles announcements THEN the system SHALL CONTINUE TO support both admin and employee access levels

9.11 WHEN LoggingMiddleware logs requests THEN the system SHALL CONTINUE TO capture method, URI, IP, user agent, and timestamp

9.12 WHEN asset files are requested THEN the system SHALL CONTINUE TO serve CSS and JavaScript files correctly

9.13 WHEN application runs on different deployment paths THEN the system SHALL CONTINUE TO support both subdirectory and domain-root deployments

9.14 WHEN Request class parses URI THEN the system SHALL CONTINUE TO remove query strings and decode URLs correctly

9.15 WHEN Response class sends output THEN the system SHALL CONTINUE TO set proper HTTP status codes and headers
