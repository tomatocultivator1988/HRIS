# Requirements Document

## Introduction

A simple Human Resource Information System (HRIS) MVP that provides core HR functionality including employee management, attendance tracking, leave management, and basic reporting. The system will be built using HTML, Tailwind CSS, Vanilla JavaScript for the frontend, PHP for the backend, and Supabase as the database, served via XAMPP without any frameworks or build tools.

## Glossary

- **HRIS_System**: The Human Resource Information System application
- **Admin_User**: Administrative user with full system access
- **Employee_User**: Regular employee with limited self-service access
- **Employee_Record**: Complete employee information including personal and employment details
- **Attendance_Entry**: Daily time-in/time-out record for an employee
- **Leave_Request**: Employee request for time off with approval workflow
- **Leave_Credit**: Available leave days allocated to an employee per year
- **Announcement**: Company-wide message posted by administrators
- **Dashboard**: Main interface showing key HR metrics and charts
- **Supabase_API**: Database service providing REST API endpoints
- **Chart_Component**: Visual representation of data using Chart.js library

## Requirements

### Requirement 1: Dashboard Analytics

**User Story:** As an Admin_User, I want to view comprehensive HR analytics on a dashboard, so that I can monitor organizational metrics at a glance.

#### Acceptance Criteria

1. THE Dashboard SHALL display total employee count
2. THE Dashboard SHALL display count of employees present today
3. THE Dashboard SHALL display count of employees on leave today
4. THE Dashboard SHALL display count of absent employees today
5. THE Dashboard SHALL display headcount breakdown by department using Chart_Component
6. THE Dashboard SHALL display attendance trend for the last 7 days using Chart_Component
7. THE Dashboard SHALL display leave status breakdown (pending, approved, denied) using Chart_Component
8. WHEN dashboard data is loaded, THE HRIS_System SHALL fetch real-time data from Supabase_API
9. THE Dashboard SHALL refresh automatically every 5 minutes

### Requirement 2: Employee Records Management

**User Story:** As an Admin_User, I want to manage employee records, so that I can maintain accurate employee information.

#### Acceptance Criteria

1. WHEN adding a new employee, THE HRIS_System SHALL capture first name, last name, employee ID, department, position, employment status, date hired, mobile number, and work email
2. THE HRIS_System SHALL validate that employee ID is unique
3. THE HRIS_System SHALL validate that work email is unique
4. THE HRIS_System SHALL validate that employment status is one of: Regular, Probationary, Contractual, Part-time
5. WHEN editing an employee, THE HRIS_System SHALL allow modification of all employee fields except employee ID
6. WHEN viewing employees, THE HRIS_System SHALL display all Employee_Records in a searchable table
7. WHEN deleting an employee, THE HRIS_System SHALL mark the employee as inactive rather than permanently delete
8. THE HRIS_System SHALL validate required fields before saving Employee_Record

### Requirement 3: Attendance Tracking

**User Story:** As an Admin_User, I want to track employee attendance, so that I can monitor work hours and punctuality.

#### Acceptance Criteria

1. WHEN recording time-in, THE HRIS_System SHALL capture employee ID, date, and timestamp
2. WHEN recording time-out, THE HRIS_System SHALL update the existing Attendance_Entry for that date
3. WHEN time-in is after 9:00 AM, THE HRIS_System SHALL automatically flag status as "Late"
4. WHEN no time-in is recorded for a workday, THE HRIS_System SHALL flag status as "Absent"
5. THE HRIS_System SHALL allow manual override of attendance status with remarks
6. WHEN viewing daily attendance, THE HRIS_System SHALL display all employees with their time-in, time-out, and status
7. WHEN viewing weekly attendance, THE HRIS_System SHALL display attendance summary for selected week
8. THE HRIS_System SHALL calculate total work hours per day based on time-in and time-out

### Requirement 4: Leave Management System

**User Story:** As an Employee_User, I want to request leave, so that I can take time off with proper approval.

#### Acceptance Criteria

1. WHEN filing a leave request, THE HRIS_System SHALL capture employee ID, leave type, start date, end date, and reason
2. THE HRIS_System SHALL calculate total days automatically based on start and end dates
3. THE HRIS_System SHALL validate that employee has sufficient Leave_Credit for the requested days
4. WHEN a leave request is submitted, THE HRIS_System SHALL set status to "Pending"
5. WHEN an Admin_User reviews a leave request, THE HRIS_System SHALL allow approval or denial with timestamp
6. WHEN a leave request is approved, THE HRIS_System SHALL deduct days from employee's Leave_Credit
7. WHEN a leave request is denied, THE HRIS_System SHALL not deduct any Leave_Credit
8. THE HRIS_System SHALL display leave request history for each employee

### Requirement 5: Leave Credits Management

**User Story:** As an Admin_User, I want to manage employee leave credits, so that I can track available leave balances.

#### Acceptance Criteria

1. THE HRIS_System SHALL initialize leave credits for each employee based on leave types
2. WHEN leave credits are allocated, THE HRIS_System SHALL set total credits per leave type per year
3. THE HRIS_System SHALL automatically calculate remaining credits as total minus used credits
4. WHEN approved leave is taken, THE HRIS_System SHALL increment used credits
5. THE HRIS_System SHALL prevent leave requests that exceed remaining credits
6. WHEN viewing leave credits, THE HRIS_System SHALL display total, used, and remaining credits per leave type
7. THE HRIS_System SHALL allow manual adjustment of leave credits with audit trail

### Requirement 6: Employee Self-Service Portal

**User Story:** As an Employee_User, I want to access my personal HR information, so that I can view my records and status.

#### Acceptance Criteria

1. WHEN an Employee_User logs in, THE HRIS_System SHALL display only their personal information
2. THE HRIS_System SHALL allow Employee_User to view their attendance history
3. THE HRIS_System SHALL allow Employee_User to view their leave balance per leave type
4. THE HRIS_System SHALL allow Employee_User to view their profile information
5. THE HRIS_System SHALL allow Employee_User to submit leave requests
6. THE HRIS_System SHALL allow Employee_User to view status of their leave requests
7. THE HRIS_System SHALL restrict Employee_User from accessing other employees' data

### Requirement 7: Reporting System

**User Story:** As an Admin_User, I want to generate HR reports, so that I can analyze workforce data and trends.

#### Acceptance Criteria

1. THE HRIS_System SHALL generate attendance summary report by date range
2. THE HRIS_System SHALL generate leave summary report showing leave usage by employee
3. THE HRIS_System SHALL generate department headcount report
4. WHEN generating reports, THE HRIS_System SHALL allow filtering by date range, department, or employee
5. THE HRIS_System SHALL display reports in tabular format with export capability
6. THE HRIS_System SHALL calculate attendance percentages and leave utilization rates
7. THE HRIS_System SHALL show trends and patterns in attendance and leave data

### Requirement 8: Announcements System

**User Story:** As an Admin_User, I want to post company announcements, so that I can communicate important information to all employees.

#### Acceptance Criteria

1. WHEN creating an announcement, THE HRIS_System SHALL capture title, content, and posting admin
2. THE HRIS_System SHALL timestamp all announcements with creation date
3. THE HRIS_System SHALL display active announcements to all users on login
4. THE HRIS_System SHALL allow Admin_User to deactivate announcements
5. THE HRIS_System SHALL display announcements in chronological order (newest first)
6. THE HRIS_System SHALL allow Admin_User to edit announcement content
7. THE HRIS_System SHALL show announcement author and posting date

### Requirement 9: Authentication and Authorization

**User Story:** As a system user, I want secure access to the HRIS system, so that employee data remains protected.

#### Acceptance Criteria

1. THE HRIS_System SHALL require login credentials for all access
2. THE HRIS_System SHALL differentiate between Admin_User and Employee_User access levels
3. WHEN an Admin_User logs in, THE HRIS_System SHALL provide full system access
4. WHEN an Employee_User logs in, THE HRIS_System SHALL restrict access to personal data only
5. THE HRIS_System SHALL maintain session security with appropriate timeouts
6. THE HRIS_System SHALL validate user credentials against Supabase_API
7. THE HRIS_System SHALL redirect unauthorized users to login page

### Requirement 10: Data Integration and API

**User Story:** As a system administrator, I want reliable data storage and retrieval, so that the HRIS system operates consistently.

#### Acceptance Criteria

1. THE HRIS_System SHALL use Supabase_API for all database operations
2. WHEN performing CRUD operations, THE HRIS_System SHALL handle API responses and errors gracefully
3. THE HRIS_System SHALL validate data before sending to Supabase_API
4. WHEN API calls fail, THE HRIS_System SHALL display appropriate error messages to users
5. THE HRIS_System SHALL implement proper error handling for network connectivity issues
6. THE HRIS_System SHALL use prepared statements equivalent for SQL injection prevention
7. THE HRIS_System SHALL maintain data consistency across all operations

### Requirement 11: User Interface and Experience

**User Story:** As a system user, I want an intuitive and responsive interface, so that I can efficiently perform HR tasks.

#### Acceptance Criteria

1. THE HRIS_System SHALL use Tailwind CSS for consistent styling and responsive design
2. THE HRIS_System SHALL display properly on desktop, tablet, and mobile devices
3. THE HRIS_System SHALL use Chart.js for all data visualizations
4. WHEN loading data, THE HRIS_System SHALL show loading indicators
5. THE HRIS_System SHALL provide clear navigation between different modules
6. THE HRIS_System SHALL display success and error messages for user actions
7. THE HRIS_System SHALL maintain consistent color scheme and typography throughout
8. THE HRIS_System SHALL ensure accessibility compliance for form inputs and navigation