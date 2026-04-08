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
    $router->addRoute('GET', '/api/employees/profile.php', 'EmployeeController@apiShow', ['logging', 'auth']);
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
    
    // Reports API routes
    $router->addRoute('GET', '/api/reports/attendance', 'ReportController@attendance', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/leave', 'ReportController@leave', ['logging', 'auth', 'role:admin']);
    $router->addRoute('GET', '/api/reports/headcount', 'ReportController@headcount', ['logging', 'auth', 'role:admin']);
    
    // Announcements API routes
    $router->addRoute('GET', '/api/announcements', 'AnnouncementController@index', ['logging', 'auth']);
    $router->addRoute('POST', '/api/announcements', 'AnnouncementController@create', ['logging', 'auth', 'role:admin']);
    $router->addRoute('PUT', '/api/announcements/{id}', 'AnnouncementController@update', ['logging', 'auth', 'role:admin']);
    $router->addRoute('POST', '/api/announcements/{id}/deactivate', 'AnnouncementController@deactivate', ['logging', 'auth', 'role:admin']);
};