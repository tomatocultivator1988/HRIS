# Implementation Plan: MVC Architecture Conversion

## Overview

This implementation plan converts the existing HRIS system from its current mixed architecture to a clean MVC pattern with services layer. The migration follows a 5-phase approach to maintain system stability while addressing critical issues like broken navigation, scattered business logic, and inconsistent error handling.

## Tasks

- [x] 1. Phase 1: Core Framework Setup
  - [x] 1.1 Create core MVC framework classes
    - Implement `src/Core/Router.php` with URL pattern matching and route dispatching
    - Implement `src/Core/Controller.php` base class with request/response handling
    - Implement `src/Core/Model.php` base class with database operations
    - Implement `src/Core/Container.php` for dependency injection
    - _Requirements: 5.1, 5.2, 6.1, 6.2_

  - [ ]* 1.2 Write property test for Router class
    - **Property 9: URL Routing Consistency**
    - **Validates: Requirements 6.3**

  - [x] 1.3 Set up single entry point routing system
    - Modify `public/index.php` to use centralized routing
    - Create `config/routes.php` with route definitions
    - Implement middleware pipeline for authentication and logging
    - _Requirements: 6.1, 6.3, 6.5_

  - [ ]* 1.4 Write property test for route parameter handling
    - **Property 10: Route Parameter Handling Reliability**
    - **Validates: Requirements 6.4**

  - [x] 1.5 Create configuration management system
    - Implement `src/Config/ConfigManager.php` for centralized configuration
    - Create environment-specific config files (`config/app.php`, `config/database.php`)
    - Set up secure configuration loading with environment variable support
    - _Requirements: 7.1, 7.2, 7.4_

- [x] 2. Phase 2: Authentication Migration
  - [x] 2.1 Create AuthController and AuthService
    - Implement `src/Controllers/AuthController.php` with login/logout/verify methods
    - Implement `src/Services/AuthService.php` with authentication business logic
    - Extract logic from existing `api/auth/login.php` and `api/auth/logout.php`
    - _Requirements: 3.1, 3.2, 4.1, 4.3_

  - [ ]* 2.2 Write property test for authentication consistency
    - **Property 16: Authorization Rule Consistency**
    - **Validates: Requirements 12.2**

  - [x] 2.3 Create User model and authentication middleware
    - Implement `src/Models/User.php` with user data access methods
    - Create authentication middleware for protecting routes
    - Implement JWT token management and session handling
    - _Requirements: 1.1, 1.2, 3.6, 12.1_

  - [ ]* 2.4 Write unit tests for AuthService
    - Test authentication flow, token generation, and role validation
    - Test error conditions and invalid credentials
    - _Requirements: 4.3, 12.2_

  - [x] 2.5 Update routing to use new authentication system
    - Configure protected routes with authentication middleware
    - Update existing auth endpoints to proxy to new controllers
    - Ensure backward compatibility with existing auth flow
    - _Requirements: 6.5, 12.1_

- [x] 3. Checkpoint - Core framework and authentication working
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Phase 3: Employee Management Migration
  - [x] 4.1 Create EmployeeController and EmployeeService
    - Implement `src/Controllers/EmployeeController.php` with CRUD operations
    - Implement `src/Services/EmployeeService.php` with employee business logic
    - Extract logic from `api/employees/` endpoints (list.php, create.php, update.php, delete.php)
    - _Requirements: 3.1, 3.4, 4.1, 4.3_

  - [ ]* 4.2 Write property test for employee data validation
    - **Property 1: Model Layer Data Validation Consistency**
    - **Validates: Requirements 1.3**

  - [x] 4.3 Create Employee model with enhanced functionality
    - Implement `src/Models/Employee.php` with validation and relationships
    - Add search and filtering capabilities
    - Implement data sanitization and validation rules
    - _Requirements: 1.1, 1.3, 1.6_

  - [ ]* 4.4 Write property test for business rule enforcement
    - **Property 7: Business Rule Enforcement Consistency**
    - **Validates: Requirements 4.3**

  - [x] 4.5 Update employee management views and API endpoints
    - Create view templates for employee list and profile pages
    - Update API endpoints to use new controller structure
    - Implement consistent error handling and response formatting
    - _Requirements: 2.1, 2.3, 3.4, 3.5_

  - [ ]* 4.6 Write unit tests for EmployeeService
    - Test employee creation, validation, and search functionality
    - Test error conditions and business rule violations
    - _Requirements: 4.3, 4.6_

- [x] 5. Phase 4: Dashboard and Reporting Migration
  - [x] 5.1 Create DashboardController and fix navigation issues
    - Implement `src/Controllers/DashboardController.php` for admin and employee dashboards
    - Fix broken navigation paths in `dashboard/admin.html`
    - Implement proper URL routing for dashboard modules
    - _Requirements: 3.1, 3.4, 6.3_

  - [ ]* 5.2 Write property test for controller response consistency
    - **Property 5: Controller Response Format Consistency**
    - **Validates: Requirements 3.4**

  - [x] 5.3 Create ReportService and migrate reporting functionality
    - Implement `src/Services/ReportService.php` with report generation logic
    - Extract logic from `api/reports/` endpoints
    - Implement consistent report formatting and data aggregation
    - _Requirements: 4.1, 4.3, 4.6_

  - [ ]* 5.4 Write property test for error handling uniformity
    - **Property 6: Controller Error Handling Uniformity**
    - **Validates: Requirements 3.5**

  - [x] 5.5 Update dashboard views and metrics API
    - Create reusable dashboard components and templates
    - Update `api/dashboard/metrics.php` to use new controller structure
    - Implement proper error handling and loading states
    - _Requirements: 2.1, 2.4, 3.4_

- [x] 6. Phase 5: Remaining Modules Migration
  - [x] 6.1 Create AttendanceController and AttendanceService
    - Implement `src/Controllers/AttendanceController.php` for attendance operations
    - Implement `src/Services/AttendanceService.php` with attendance business logic
    - Extract logic from `api/attendance/` endpoints
    - _Requirements: 3.1, 4.1, 4.3_

  - [ ]* 6.2 Write property test for database error handling
    - **Property 2: Database Error Handling Consistency**
    - **Validates: Requirements 1.4**

  - [x] 6.3 Create LeaveController and LeaveService
    - Implement `src/Controllers/LeaveController.php` for leave management
    - Implement `src/Services/LeaveService.php` with leave request business logic
    - Extract logic from `api/leave/` endpoints
    - _Requirements: 3.1, 4.1, 4.3_

  - [ ]* 6.4 Write property test for input sanitization
    - **Property 3: Input Sanitization Completeness**
    - **Validates: Requirements 1.6**

  - [x] 6.5 Create remaining models and services
    - Implement `src/Models/Attendance.php` and `src/Models/LeaveRequest.php`
    - Implement `src/Services/AnnouncementService.php`
    - Create corresponding controllers for announcements
    - _Requirements: 1.1, 1.2, 4.1_

  - [ ]* 6.6 Write unit tests for attendance and leave services
    - Test attendance calculation and leave approval workflows
    - Test business rule validation and error conditions
    - _Requirements: 4.3, 4.6_

- [x] 7. System-wide Error Handling and Security
  - [x] 7.1 Implement centralized error handling system
    - Create `src/Core/ErrorHandler.php` with exception hierarchy
    - Implement consistent error logging and user message formatting
    - Set up proper HTTP status code mapping for different error types
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [ ]* 7.2 Write property test for system-wide error handling
    - **Property 11: System-Wide Error Handling Uniformity**
    - **Validates: Requirements 8.1**

  - [ ]* 7.3 Write property test for error classification
    - **Property 13: Error Classification Accuracy**
    - **Validates: Requirements 8.3**

  - [x] 7.4 Implement security enhancements
    - Add input validation and sanitization across all controllers
    - Implement CSRF protection and security headers
    - Add rate limiting and audit logging for security events
    - _Requirements: 12.3, 12.4, 12.6_

  - [ ]* 7.5 Write property test for security vulnerability protection
    - **Property 18: Security Vulnerability Protection**
    - **Validates: Requirements 12.4**

- [x] 8. Integration and Testing
  - [x] 8.1 Wire all components together
    - Update dependency injection container with all services and models
    - Configure complete routing table for all endpoints
    - Ensure backward compatibility with existing API endpoints
    - _Requirements: 5.1, 5.3, 6.1_

  - [ ]* 8.2 Write integration tests for complete workflows
    - Test end-to-end authentication and employee management flows
    - Test dashboard functionality and report generation
    - Test error handling across all system layers
    - _Requirements: 10.2, 10.3_

  - [x] 8.3 Performance optimization and caching
    - Implement route caching and query optimization
    - Add database connection pooling and lazy loading
    - Configure appropriate HTTP caching headers
    - _Requirements: 11.1, 11.2, 11.4_

- [x] 9. Final checkpoint and deployment preparation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation and system stability
- Property tests validate universal correctness properties from the design
- Unit tests validate specific examples and edge cases
- Migration maintains backward compatibility throughout the process
- Focus on fixing critical navigation and routing issues early in the process