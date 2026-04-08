# Bugfix Requirements Document

## Introduction

This document addresses 5 critical bugs in the HRIS MVC system that prevent proper routing, asset loading, and navigation. These bugs cause MIME type errors, route shadowing, 404 errors, and incorrect redirects after login. The root cause stems from incorrect .htaccess path handling, improper route ordering, inconsistent asset path conventions, missing web routes, and outdated redirect URLs in JavaScript.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN the browser requests `/HRIS/assets/js/config.js` THEN the .htaccess RewriteCond checks `DOCUMENT_ROOT/HRIS/public/HRIS/assets/js/config.js` (doubled `/HRIS/` path), the file check fails, and the request falls through to index.php which returns HTML instead of JavaScript, causing "Strict MIME type checking enforced for module scripts" error

1.2 WHEN a user navigates to `/HRIS/employees/create` THEN the router matches the `GET /employees/{id}` route first with `{id}='create'` and the `GET /employees/create` route is never reached, resulting in incorrect controller action execution

1.3 WHEN login.php is served from `/HRIS/login` and uses relative paths `assets/css/custom.css` and `assets/js/config.js` THEN the browser resolves these to `/HRIS/assets/...` which triggers Bug #1 (doubled path in .htaccess), causing asset loading failures

1.4 WHEN a user clicks the "Reports" link in the admin navigation or "My Profile" link in the employee navigation THEN the system returns a 404 error because no web routes exist for `/reports` or `/profile` (only API routes exist for `/api/reports/attendance`)

1.5 WHEN a user successfully logs in THEN auth.js redirects to `dashboard/admin.html` or `dashboard/employee.html` which no longer exist in the MVC system, causing 404 errors after authentication

### Expected Behavior (Correct)

2.1 WHEN the browser requests `/HRIS/assets/js/config.js` THEN the .htaccess SHALL correctly check `DOCUMENT_ROOT/HRIS/public/assets/js/config.js` (without doubled path), find the file, and serve it with the correct `application/javascript` MIME type

2.2 WHEN a user navigates to `/HRIS/employees/create` THEN the router SHALL match the `GET /employees/create` route before the parameterized `GET /employees/{id}` route and execute the `createForm` controller action

2.3 WHEN login.php is served from any path THEN it SHALL use absolute paths with leading slashes (`/assets/css/custom.css`, `/assets/js/config.js`) consistent with base.php layout, ensuring assets load correctly regardless of the request path

2.4 WHEN a user clicks the "Reports" link in the admin navigation THEN the system SHALL route to a web route handler for `/reports` that returns an HTML view, and WHEN a user clicks "My Profile" in the employee navigation THEN the system SHALL route to `/profile` and return an HTML view

2.5 WHEN a user successfully logs in THEN auth.js SHALL redirect to `/HRIS/dashboard/admin` or `/HRIS/dashboard/employee` (PHP routes without .html extension) matching the MVC routing structure

### Unchanged Behavior (Regression Prevention)

3.1 WHEN the browser requests other static assets like CSS files, images, or fonts from `/HRIS/assets/...` THEN the system SHALL CONTINUE TO serve them directly from the public directory with correct MIME types

3.2 WHEN a user navigates to existing parameterized routes like `/employees/{id}` with numeric IDs (e.g., `/employees/123`) THEN the router SHALL CONTINUE TO match the `GET /employees/{id}` route and execute the `showView` controller action

3.3 WHEN login.php loads and the user submits credentials THEN the authentication flow SHALL CONTINUE TO work correctly, validating credentials and storing tokens in localStorage

3.4 WHEN a user accesses existing API routes like `/api/reports/attendance`, `/api/employees`, or `/api/auth/login` THEN the system SHALL CONTINUE TO return JSON responses and function as expected

3.5 WHEN a user logs out THEN the system SHALL CONTINUE TO clear session data, invalidate tokens, and redirect to the login page

3.6 WHEN security headers, cache control, and compression settings are applied by .htaccess THEN these configurations SHALL CONTINUE TO function as defined

3.7 WHEN the router processes middleware for authentication, authorization, and logging THEN these middleware SHALL CONTINUE TO execute in the correct order for all routes
