<?php

/**
 * Route Configuration
 * 
 * Define all application routes for the MVC framework
 */

use Core\Router;

return function (Router $router) {
    // Add global logging middleware to all routes by default
    
    // Backward compatibility routes - MUST come first to avoid conflicts with parameterized routes
    // Legacy auth endpoints
    $router->addRoute('POST', '/api/auth/login.php', 'AuthController@login', ['logging']);
    $router->addRoute('POST', '/api/auth/logout.php', 'AuthController@logout', ['logging', 'auth']);
    $router->addRoute('GET', '/api/auth/verify.php', 'AuthController@verify', ['logging']);
    $router->addRoute('POST', '/api/auth/refresh.php', 'AuthController@refresh', ['logging']);
    
    // Legacy employee endpoints
    $router->addRoute('GET', '/api/employees/list.php', 'EmployeeController@apiIndex', ['logging', 'auth']);
    $router->addRoute('GET', '/api/employees/search.php', 'EmployeeController@apiSearch', ['logging', 'auth']);
    $router->addRoute('POST', '/api/employees/create.php', 'EmployeeController@apiCreate', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/employees/profile.php', 'EmployeeController@profile', ['logging', 'auth']);
    $router->addRoute('GET', '/api/employees/profile', 'EmployeeController@profile', ['logging', 'auth']); // Without .php extension
    $router->addRoute('PUT', '/api/employees/profile.php', 'EmployeeController@updateProfile', ['logging', 'auth']);
    $router->addRoute('PUT', '/api/employees/profile', 'EmployeeController@updateProfile', ['logging', 'auth']);
    $router->addRoute('POST', '/api/employees/update.php', 'EmployeeController@apiUpdate', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/employees/delete.php', 'EmployeeController@apiDelete', ['logging', 'auth', 'role:admin']);
    
    // Legacy dashboard endpoints
    $router->addRoute('GET', '/api/dashboard/metrics.php', 'DashboardController@metrics', ['logging', 'auth']);
    
    // Legacy attendance endpoints (for future compatibility)
    $router->addRoute('GET', '/api/attendance/daily.php', 'AttendanceController@daily', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/timein.php', 'AttendanceController@timeIn', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/timeout.php', 'AttendanceController@timeOut', ['logging', 'auth']);
    $router->addRoute('GET', '/api/attendance/history.php', 'AttendanceController@history', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/override.php', 'AttendanceController@override', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/attendance/detect_absences.php', 'AttendanceController@detectAbsences', ['logging', 'auth', 'role:admin']);
    
    // Legacy leave endpoints (for future compatibility)
    $router->addRoute('GET', '/api/leave/balance.php', 'LeaveController@balance', ['logging', 'auth']);
    $router->addRoute('POST', '/api/leave/request.php', 'LeaveController@request', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/history.php', 'LeaveController@history', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/pending.php', 'LeaveController@pending', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/leave/approve.php', 'LeaveController@approve', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/leave/deny.php', 'LeaveController@deny', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/leave/types.php', 'LeaveController@types', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/credits.php', 'LeaveController@credits', ['logging', 'auth', 'role:admin']);
    
    // Legacy announcement endpoints (for backward compatibility)
    $router->addRoute('GET', '/api/announcements/list.php', 'AnnouncementController@list', ['logging', 'auth']);
    $router->addRoute('POST', '/api/announcements/create.php', 'AnnouncementController@create', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/announcements/update.php', 'AnnouncementController@update', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/announcements/deactivate.php', 'AnnouncementController@deactivate', ['logging', 'auth', 'role:admin']);
    
    // Legacy report endpoints (for backward compatibility)
    $router->addRoute('GET', '/api/reports/attendance.php', 'ReportController@attendance', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/leave.php', 'ReportController@leave', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/headcount.php', 'ReportController@headcount', ['logging', 'auth', 'role:admin']);

    // Legacy payroll endpoints (for backward compatibility)
    $router->addRoute('POST', '/api/payroll/periods.php', 'PayrollController@createPeriod', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/periods.php', 'PayrollController@listPeriods', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/generate.php', 'PayrollController@generateRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/runs/detail.php', 'PayrollController@getRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/recompute.php', 'PayrollController@recomputeRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/finalize.php', 'PayrollController@finalizeRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/approve.php', 'PayrollController@approveRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/pay.php', 'PayrollController@markRunPaid', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/reverse.php', 'PayrollController@reverseRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/line-items/update.php', 'PayrollController@updateLineItem', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/payslips.php', 'PayrollController@employeePayslips', ['logging', 'auth']);
    $router->addRoute('GET', '/api/payroll/payslip.php', 'PayrollController@employeePayslipDetail', ['logging', 'auth']);
    
    // Web routes (HTML responses)
    $router->addRoute('GET', '/', 'AuthController@loginForm', ['logging']);
    $router->addRoute('GET', '/login', 'AuthController@loginForm', ['logging']);
    $router->addRoute('GET', '/dashboard', 'DashboardController@index', ['logging']);
    $router->addRoute('GET', '/dashboard/admin', 'DashboardController@admin', ['logging']);
    $router->addRoute('GET', '/dashboard/employee', 'DashboardController@employee', ['logging']);
    
    // Employee management routes
    $router->addRoute('GET', '/employees', 'EmployeeController@indexView', ['logging']);
    $router->addRoute('GET', '/employees/create', 'EmployeeController@createForm', ['logging']);
    $router->addRoute('GET', '/employees/{id}/edit', 'EmployeeController@editForm', ['logging']);
    $router->addRoute('GET', '/employees/{id}', 'EmployeeController@showView', ['logging']);
    $router->addRoute('GET', '/employees/profile', 'EmployeeController@profileView', ['logging']);
    
    // Attendance web route
    $router->addRoute('GET', '/attendance', 'AttendanceController@indexView', ['logging']);
    
    // Leave web route
    $router->addRoute('GET', '/leave', 'LeaveController@indexView', ['logging']);
    
    // Reports web route
    $router->addRoute('GET', '/reports', 'ReportController@index', ['logging']);
    $router->addRoute('GET', '/reports/attendance-view', 'ReportController@attendanceView', ['logging']);
    $router->addRoute('GET', '/reports/leave-view', 'ReportController@leaveView', ['logging']);
    $router->addRoute('GET', '/reports/employees-view', 'ReportController@employeesView', ['logging']);
    $router->addRoute('GET', '/reports/productivity-view', 'ReportController@productivityView', ['logging']);
    $router->addRoute('GET', '/payroll', 'PayrollController@indexView', ['logging']);
    $router->addRoute('GET', '/payroll/simple', 'PayrollController@simpleView', ['logging']);
    $router->addRoute('GET', '/payroll/manage', 'PayrollController@manageView', ['logging']);
    $router->addRoute('GET', '/payslips', 'PayrollController@payslipsView', ['logging']);
    
    // Recruitment web route
    $router->addRoute('GET', '/recruitment', 'RecruitmentController@indexView', ['logging']);
    
    // Profile web route
    $router->addRoute('GET', '/profile', 'EmployeeController@profileView', ['logging']);
    
    // API routes (JSON responses)
    $router->addRoute('POST', '/api/auth/login', 'AuthController@login', ['logging']);
    $router->addRoute('POST', '/api/auth/logout', 'AuthController@logout', ['logging']);
    $router->addRoute('POST', '/api/auth/refresh', 'AuthController@refresh', ['logging']);
    $router->addRoute('GET', '/api/auth/verify', 'AuthController@verify', ['logging']);
    
    // Password management routes
    $router->addRoute('GET', '/password/change', 'PasswordController@changePasswordForm', ['logging']);
    $router->addRoute('POST', '/api/password/change', 'PasswordController@changePassword', ['logging', 'auth']);
    $router->addRoute('POST', '/api/password/admin-reset', 'PasswordController@adminResetPassword', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/password/check-force-change', 'PasswordController@checkForcePasswordChange', ['logging', 'auth']);
    
    // Employee API routes
    $router->addRoute('GET', '/api/employees/search', 'EmployeeController@apiSearch', ['logging', 'auth']);
    $router->addRoute('GET', '/api/employees/profile', 'EmployeeController@profile', ['logging', 'auth']); // Must be before {id} route
    $router->addRoute('PUT', '/api/employees/profile', 'EmployeeController@updateProfile', ['logging', 'auth']);
    $router->addRoute('GET', '/api/employees', 'EmployeeController@apiIndex', ['logging', 'auth']);
    $router->addRoute('POST', '/api/employees', 'EmployeeController@apiCreate', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/employees/{id}', 'EmployeeController@apiShow', ['logging', 'auth']);
    $router->addRoute('PUT', '/api/employees/{id}', 'EmployeeController@apiUpdate', ['logging', 'auth', 'role:admin']);
    $router->addRoute('DELETE', '/api/employees/{id}', 'EmployeeController@apiDelete', ['logging', 'auth', 'role:admin']);
    
    // Dashboard API routes
    $router->addRoute('GET', '/api/dashboard/metrics', 'DashboardController@metrics', ['logging', 'auth']);
    
    // Attendance API routes
    $router->addRoute('GET', '/api/attendance/daily', 'AttendanceController@daily', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/timein', 'AttendanceController@timeIn', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/timeout', 'AttendanceController@timeOut', ['logging', 'auth']);
    $router->addRoute('GET', '/api/attendance/history', 'AttendanceController@history', ['logging', 'auth']);
    $router->addRoute('POST', '/api/attendance/override', 'AttendanceController@override', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/attendance/detect-absences', 'AttendanceController@detectAbsences', ['logging', 'auth', 'role:admin']);
    
    // Leave management API routes
    $router->addRoute('GET', '/api/leave/balance', 'LeaveController@balance', ['logging', 'auth']);
    $router->addRoute('POST', '/api/leave/request', 'LeaveController@request', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/history', 'LeaveController@history', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/pending', 'LeaveController@pending', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/leave/approved', 'LeaveController@approved', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/leave/denied', 'LeaveController@denied', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/leave/all', 'LeaveController@all', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/leave/{id}/approve', 'LeaveController@approve', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/leave/{id}/deny', 'LeaveController@deny', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/leave/types', 'LeaveController@types', ['logging', 'auth']);
    $router->addRoute('GET', '/api/leave/credits', 'LeaveController@credits', ['logging', 'auth', 'role:admin']);

    // Payroll API routes
    $router->addRoute('POST', '/api/payroll/periods', 'PayrollController@createPeriod', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/periods', 'PayrollController@listPeriods', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/periods/{id}/runs', 'PayrollController@getPeriodRuns', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/generate', 'PayrollController@generateRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/runs/{id}', 'PayrollController@getRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/{id}/recompute', 'PayrollController@recomputeRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/{id}/finalize', 'PayrollController@finalizeRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/{id}/approve', 'PayrollController@approveRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/runs/{id}/pay', 'PayrollController@markRunPaid', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/payroll/runs/{id}/reverse', 'PayrollController@reverseRun', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/line-items/{id}', 'PayrollController@updateLineItem', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/payroll/line-items/{id}/pay', 'PayrollController@markLineItemPaid', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/payroll/payslips', 'PayrollController@employeePayslips', ['logging', 'auth']);
    $router->addRoute('GET', '/api/payroll/payslips/{id}', 'PayrollController@employeePayslipDetail', ['logging', 'auth']);
    
    // Compensation API routes (Position-based)
    $router->addRoute('GET', '/compensation', 'CompensationController@indexView', ['logging']);
    $router->addRoute('GET', '/api/compensation/positions', 'CompensationController@listPositions', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/compensation/positions/{position}', 'CompensationController@getPositionSalary', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/compensation/positions', 'CompensationController@createPositionSalary', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/compensation/positions/{id}', 'CompensationController@updatePositionSalary', ['logging', 'auth', 'role:admin']);
    
    // Legacy employee-based compensation routes (for backward compatibility)
    $router->addRoute('GET', '/api/compensation/list', 'CompensationController@listEmployeesWithCompensation', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/compensation/{id}', 'CompensationController@getEmployeeCompensation', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/compensation', 'CompensationController@createCompensation', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/compensation/{id}', 'CompensationController@updateCompensation', ['logging', 'auth', 'role:admin']);
    
    // Reports API routes
    $router->addRoute('GET', '/api/reports/attendance', 'ReportController@attendance', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/leave', 'ReportController@leave', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/headcount', 'ReportController@headcount', ['logging', 'auth', 'role:admin']);
    
    // Recruitment API routes
    // Job Postings
    $router->addRoute('GET', '/api/recruitment/jobs', 'RecruitmentController@listJobs', ['logging', 'auth']);
    $router->addRoute('GET', '/api/recruitment/jobs/{id}', 'RecruitmentController@getJob', ['logging', 'auth']);
    $router->addRoute('POST', '/api/recruitment/jobs', 'RecruitmentController@createJob', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/recruitment/jobs/{id}', 'RecruitmentController@updateJob', ['logging', 'auth', 'role:admin']);
    
    // Applicants
    $router->addRoute('GET', '/api/recruitment/applicants', 'RecruitmentController@listApplicants', ['logging', 'auth']);
    $router->addRoute('GET', '/api/recruitment/applicants/{id}', 'RecruitmentController@getApplicant', ['logging', 'auth']);
    $router->addRoute('POST', '/api/recruitment/applicants', 'RecruitmentController@createApplicant', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/recruitment/applicants/{id}', 'RecruitmentController@updateApplicant', ['logging', 'auth', 'role:admin']);
    
    // Evaluations
    $router->addRoute('GET', '/api/recruitment/applicants/{id}/evaluations', 'RecruitmentController@getEvaluations', ['logging', 'auth']);
    $router->addRoute('POST', '/api/recruitment/evaluations', 'RecruitmentController@saveEvaluation', ['logging', 'auth', 'role:admin']);
    
    // Hiring
    $router->addRoute('POST', '/api/recruitment/applicants/{id}/hire', 'RecruitmentController@hireApplicant', ['logging', 'auth', 'role:admin']);
    
    // Announcements API routes
    $router->addRoute('GET', '/api/announcements', 'AnnouncementController@index', ['logging', 'auth']);
    $router->addRoute('POST', '/api/announcements', 'AnnouncementController@create', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/announcements/{id}', 'AnnouncementController@update', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/announcements/{id}/deactivate', 'AnnouncementController@deactivate', ['logging', 'auth', 'role:admin']);
};
