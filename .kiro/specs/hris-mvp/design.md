# Design Document: HRIS MVP

## Overview

The HRIS MVP is a comprehensive Human Resource Information System designed to provide core HR functionality through a simple, accessible web interface. The system serves both administrative users who need full access to manage organizational HR data and employees who require self-service capabilities for personal HR tasks.

The system architecture follows a traditional three-tier approach optimized for XAMPP deployment: a presentation layer built with HTML, Tailwind CSS, and vanilla JavaScript; a business logic layer implemented in PHP; and a data layer powered by Supabase's cloud database service. This design ensures simplicity in deployment while maintaining scalability and security.

Key design principles include:
- **Simplicity**: No frameworks or build tools required - pure HTML/CSS/JS files served directly by Apache
- **Security**: Role-based access control with secure authentication via Supabase
- **Responsiveness**: Mobile-first design using Tailwind CSS utilities
- **Real-time**: Dashboard auto-refresh and live data updates
- **Maintainability**: Clear separation of concerns between frontend and backend

## Architecture

### System Architecture Overview

The HRIS MVP follows a client-server architecture with clear separation between presentation, business logic, and data layers:

```
┌─────────────────────────────────────────────────────────────┐
│                    Client Layer (Browser)                   │
├─────────────────────────────────────────────────────────────┤
│  HTML Pages  │  Tailwind CSS  │  Vanilla JS  │  Chart.js   │
└─────────────────────────────────────────────────────────────┘
                              │
                         HTTP/AJAX
                              │
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer (XAMPP)                 │
├─────────────────────────────────────────────────────────────┤
│           PHP Scripts (Business Logic & API Layer)          │
│  • Authentication  • Employee Management  • Attendance      │
│  • Leave Management  • Reporting  • Dashboard Analytics     │
└─────────────────────────────────────────────────────────────┘
                              │
                         REST API
                              │
┌─────────────────────────────────────────────────────────────┐
│                     Data Layer (Supabase)                   │
├─────────────────────────────────────────────────────────────┤
│  PostgreSQL Database  │  Authentication  │  Real-time APIs  │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

**Frontend Technologies:**
- **HTML5**: Semantic markup for accessibility and SEO
- **Tailwind CSS**: Utility-first CSS framework for rapid UI development
- **Vanilla JavaScript**: Pure JavaScript for DOM manipulation and AJAX calls
- **Chart.js**: Lightweight charting library for dashboard visualizations

**Backend Technologies:**
- **PHP 8.x**: Server-side scripting for business logic and API endpoints
- **Apache HTTP Server**: Web server (via XAMPP)
- **Supabase**: Backend-as-a-Service providing PostgreSQL database and authentication

**Development Environment:**
- **XAMPP**: Cross-platform web server solution stack
- **No build tools**: Direct file serving without compilation or bundling

### Deployment Architecture

The system is designed for simple XAMPP deployment with the following directory structure:

```
htdocs/hris-mvp/
├── index.html                 # Login page
├── dashboard/
│   ├── admin.html            # Admin dashboard
│   └── employee.html         # Employee dashboard
├── modules/
│   ├── employees/            # Employee management
│   ├── attendance/           # Attendance tracking
│   ├── leave/               # Leave management
│   ├── reports/             # Reporting system
│   └── announcements/       # Announcements
├── api/
│   ├── auth/                # Authentication endpoints
│   ├── employees/           # Employee CRUD operations
│   ├── attendance/          # Attendance operations
│   ├── leave/              # Leave management operations
│   └── dashboard/          # Dashboard data endpoints
├── assets/
│   ├── css/                # Custom CSS (minimal)
│   ├── js/                 # JavaScript modules
│   └── images/             # Static assets
└── config/
    └── supabase.php        # Supabase configuration
```

## Components and Interfaces

### Frontend Components

#### 1. Authentication Component
**Purpose**: Handles user login and session management
**Files**: `index.html`, `assets/js/auth.js`
**Responsibilities**:
- User credential validation
- Role-based redirection (Admin vs Employee)
- Session token management
- Logout functionality

**Interface**:
```javascript
class AuthManager {
    async login(email, password)
    async logout()
    getCurrentUser()
    isAuthenticated()
    hasRole(role)
}
```

#### 2. Dashboard Component
**Purpose**: Displays real-time HR analytics and metrics
**Files**: `dashboard/admin.html`, `dashboard/employee.html`, `assets/js/dashboard.js`
**Responsibilities**:
- Real-time data fetching from API
- Chart rendering using Chart.js
- Auto-refresh functionality (5-minute intervals)
- Responsive layout adaptation

**Interface**:
```javascript
class DashboardManager {
    async loadDashboardData()
    renderCharts(data)
    startAutoRefresh()
    stopAutoRefresh()
}
```

#### 3. Employee Management Component
**Purpose**: CRUD operations for employee records
**Files**: `modules/employees/`, `assets/js/employees.js`
**Responsibilities**:
- Employee form validation
- Search and filter functionality
- Bulk operations support
- Data table rendering

**Interface**:
```javascript
class EmployeeManager {
    async createEmployee(employeeData)
    async updateEmployee(id, employeeData)
    async deleteEmployee(id)
    async searchEmployees(criteria)
    validateEmployeeData(data)
}
```

#### 4. Attendance Component
**Purpose**: Time tracking and attendance management
**Files**: `modules/attendance/`, `assets/js/attendance.js`
**Responsibilities**:
- Time-in/time-out recording
- Attendance status calculation
- Weekly/monthly view rendering
- Manual override capabilities

**Interface**:
```javascript
class AttendanceManager {
    async recordTimeIn(employeeId)
    async recordTimeOut(employeeId)
    async getAttendanceHistory(employeeId, dateRange)
    calculateWorkHours(timeIn, timeOut)
    determineStatus(timeIn, date)
}
```

#### 5. Leave Management Component
**Purpose**: Leave request and approval workflow
**Files**: `modules/leave/`, `assets/js/leave.js`
**Responsibilities**:
- Leave request form handling
- Credit balance validation
- Approval workflow management
- Leave calendar integration

**Interface**:
```javascript
class LeaveManager {
    async submitLeaveRequest(requestData)
    async approveLeaveRequest(requestId)
    async denyLeaveRequest(requestId, reason)
    async getLeaveBalance(employeeId)
    validateLeaveRequest(requestData)
}
```

### Backend API Components

#### 1. Authentication API
**Endpoint**: `/api/auth/`
**Purpose**: User authentication and session management
**Methods**:
- `POST /api/auth/login.php` - User login
- `POST /api/auth/logout.php` - User logout
- `GET /api/auth/verify.php` - Session verification

#### 2. Employee API
**Endpoint**: `/api/employees/`
**Purpose**: Employee data management
**Methods**:
- `GET /api/employees/list.php` - Get all employees
- `POST /api/employees/create.php` - Create new employee
- `PUT /api/employees/update.php` - Update employee
- `DELETE /api/employees/delete.php` - Soft delete employee

#### 3. Attendance API
**Endpoint**: `/api/attendance/`
**Purpose**: Attendance tracking operations
**Methods**:
- `POST /api/attendance/timein.php` - Record time-in
- `POST /api/attendance/timeout.php` - Record time-out
- `GET /api/attendance/daily.php` - Get daily attendance
- `GET /api/attendance/weekly.php` - Get weekly summary

#### 4. Leave API
**Endpoint**: `/api/leave/`
**Purpose**: Leave management operations
**Methods**:
- `POST /api/leave/request.php` - Submit leave request
- `PUT /api/leave/approve.php` - Approve leave request
- `PUT /api/leave/deny.php` - Deny leave request
- `GET /api/leave/balance.php` - Get leave balance

#### 5. Dashboard API
**Endpoint**: `/api/dashboard/`
**Purpose**: Dashboard analytics data
**Methods**:
- `GET /api/dashboard/metrics.php` - Get key metrics
- `GET /api/dashboard/charts.php` - Get chart data
- `GET /api/dashboard/trends.php` - Get trend analysis

## Data Models

### Database Schema Design

The system uses Supabase (PostgreSQL) with the following table structure:

#### 1. Employees Table
```sql
CREATE TABLE employees (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    supabase_user_id UUID UNIQUE NOT NULL, -- Links to Supabase auth.users
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    work_email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),
    department VARCHAR(100),
    position VARCHAR(100),
    employment_status VARCHAR(50) CHECK (employment_status IN ('Regular', 'Probationary', 'Contractual', 'Part-time')),
    date_hired DATE,
    manager_id UUID REFERENCES employees(id), -- For leave approval hierarchy
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### 2. Attendance Table
```sql
CREATE TABLE attendance (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    time_in TIMESTAMP,
    time_out TIMESTAMP,
    status VARCHAR(20) CHECK (status IN ('Present', 'Late', 'Absent', 'Half-day')),
    work_hours DECIMAL(4,2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(employee_id, date)
);
```

#### 3. Leave Types Table
```sql
CREATE TABLE leave_types (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    days_allowed INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
```

#### 4. Leave Requests Table
```sql
CREATE TABLE leave_requests (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    leave_type_id UUID REFERENCES leave_types(id),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(3,1) NOT NULL, -- Allow half days (0.5)
    reason TEXT,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Denied', 'Cancelled')),
    reviewed_by UUID REFERENCES employees(id),
    reviewed_at TIMESTAMP,
    denial_reason TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    CONSTRAINT no_self_approval CHECK (employee_id != reviewed_by),
    CONSTRAINT valid_date_range CHECK (end_date >= start_date),
    -- Prevent overlapping leave requests for same employee
    EXCLUDE USING gist (
        employee_id WITH =,
        daterange(start_date, end_date, '[]') WITH &&
    ) WHERE (status IN ('Pending', 'Approved'))
);
```

#### 5. Leave Credits Table
```sql
CREATE TABLE leave_credits (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    leave_type_id UUID REFERENCES leave_types(id),
    total_credits INT DEFAULT 0,
    used_credits INT DEFAULT 0,
    remaining_credits INT GENERATED ALWAYS AS (total_credits - used_credits) STORED,
    year INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(employee_id, leave_type_id, year)
);
```

#### 6. Announcements Table
```sql
CREATE TABLE announcements (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    posted_by UUID REFERENCES admins(id), -- Only admins can post announcements
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### 7. Admins Table
```sql
CREATE TABLE admins (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    supabase_user_id UUID UNIQUE NOT NULL, -- Links to Supabase auth.users
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'admin' CHECK (role IN ('admin', 'hr_manager', 'super_admin')),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

#### 8. Leave Credit Audit Table
```sql
CREATE TABLE leave_credit_audit (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id),
    leave_type_id UUID REFERENCES leave_types(id),
    action VARCHAR(50) NOT NULL, -- 'ALLOCATED', 'ADJUSTED', 'USED', 'RESTORED'
    previous_total INT,
    new_total INT,
    previous_used INT,
    new_used INT,
    reason TEXT,
    performed_by UUID REFERENCES employees(id),
    created_at TIMESTAMP DEFAULT NOW()
);
```

#### 9. Work Calendar Table
```sql
CREATE TABLE work_calendar (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    is_working_day BOOLEAN DEFAULT true,
    description VARCHAR(255), -- Holiday name or special day description
    created_at TIMESTAMP DEFAULT NOW()
);
```

#### 10. System Audit Log Table
```sql
CREATE TABLE system_audit_log (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    user_id UUID, -- Can reference either employees(id) or admins(id)
    user_type VARCHAR(20) CHECK (user_type IN ('employee', 'admin')),
    action VARCHAR(100) NOT NULL, -- 'LOGIN', 'LOGOUT', 'CREATE_EMPLOYEE', 'UPDATE_ATTENDANCE', etc.
    table_name VARCHAR(50),
    record_id UUID,
    old_values JSONB,
    new_values JSONB,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);
```

#### 11. User Sessions Table (Optional - for additional session tracking)
```sql
CREATE TABLE user_sessions (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    supabase_user_id UUID NOT NULL, -- Links to Supabase auth.users
    user_type VARCHAR(20) NOT NULL CHECK (user_type IN ('employee', 'admin')),
    ip_address INET,
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW()
);
```

### Data Relationships

The database follows these key relationships:
- Admins → Independent authentication table
- Employees → Attendance (1:many)
- Employees → Leave_Credits (1:many)
- Employees → Leave_Requests (1:many as employee)
- Employees → Leave_Requests (1:many as reviewed_by)
- Admins → Announcements (1:many as posted_by)
- Leave_Types → Leave_Credits (1:many)
- Leave_Types → Leave_Requests (1:many)

### Data Validation Rules

**Employee Data Validation**:
- Employee ID: Alphanumeric, 3-20 characters, unique
- Email: Valid email format, unique
- Employment Status: Must be one of predefined values
- Date Hired: Cannot be future date

**Attendance Data Validation**:
- Time-in: Cannot be null for Present/Late status
- Time-out: Must be after time-in when provided
- Work Hours: Calculated automatically, max 24 hours
- Status: Auto-calculated based on time-in and business rules
- Date: Must be a working day (checked against work_calendar table)

**Leave Request Validation**:
- Start Date: Cannot be in the past (except same day)
- End Date: Must be same or after start date
- Total Days: Auto-calculated using business days (excludes weekends and holidays)
- Credit Check: Must have sufficient remaining credits
- Self-approval: Employee cannot approve their own leave request

### Business Logic Rules

**Working Day Definition**:
- Working days are defined in the `work_calendar` table
- Default: Monday-Friday are working days, weekends are not
- Holidays and special non-working days are marked in the calendar
- Absence detection only applies to working days

**Leave Credit Management**:
- Leave credits are automatically initialized when an employee is created
- Credits are allocated based on `leave_types.days_allowed` for the current year
- All leave credit changes are logged in `leave_credit_audit` table
- Manual adjustments require admin privileges and reason

**Leave Days Calculation**:
- Total days calculated as business days between start and end dates (inclusive)
- Excludes weekends and holidays as defined in work_calendar
- Minimum leave request is 0.5 days (half-day)
- Maximum leave request cannot exceed remaining credits

**Attendance Status Logic**:
- Present: Time-in recorded before 9:00 AM
- Late: Time-in recorded at or after 9:00 AM
- Absent: No time-in recorded for a working day
- Half-day: Manual override or time-in after 12:00 PM
- Work hours calculated as difference between time-out and time-in (in decimal hours)

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

After analyzing the acceptance criteria, the following properties have been identified for property-based testing. These properties focus on core business logic, data validation, and system behavior that should hold consistently across all valid inputs.

### Property 1: Employee Data Validation

*For any* employee data submitted to the system, all required fields must be present and valid, employee ID must be unique, and work email must be unique before the record can be saved.

**Validates: Requirements 2.1, 2.2, 2.3, 2.8**

### Property 2: Employment Status Validation

*For any* employment status value submitted, the system should only accept values from the predefined set: Regular, Probationary, Contractual, Part-time.

**Validates: Requirements 2.4**

### Property 3: Employee Soft Delete Behavior

*For any* employee record that is deleted, the system should mark the employee as inactive rather than permanently removing the record from the database.

**Validates: Requirements 2.7**

### Property 4: Employee Data Modification Rules

*For any* employee record being updated, all fields except employee ID should be modifiable, while employee ID should remain immutable.

**Validates: Requirements 2.5**

### Property 5: Attendance Time-In Recording

*For any* valid employee ID and date, when time-in is recorded, the system should capture the employee ID, date, and timestamp accurately.

**Validates: Requirements 3.1**

### Property 6: Attendance Status Calculation

*For any* time-in recorded after 9:00 AM, the system should automatically flag the attendance status as "Late".

**Validates: Requirements 3.3**

### Property 7: Absence Detection

*For any* workday where no time-in is recorded for an employee, the system should flag the attendance status as "Absent".

**Validates: Requirements 3.4**

### Property 8: Work Hours Calculation

*For any* valid time-in and time-out pair, the system should calculate total work hours as the difference between time-out and time-in.

**Validates: Requirements 3.8**

### Property 9: Attendance Record Updates

*For any* existing attendance entry, when time-out is recorded, the system should update the existing record rather than creating a new one.

**Validates: Requirements 3.2**

### Property 10: Leave Request Data Capture

*For any* valid leave request data, the system should capture employee ID, leave type, start date, end date, and reason accurately.

**Validates: Requirements 4.1**

### Property 11: Leave Days Calculation

*For any* valid start date and end date pair, the system should automatically calculate the total days correctly.

**Validates: Requirements 4.2**

### Property 12: Leave Credit Validation

*For any* leave request, the system should validate that the employee has sufficient remaining leave credits before allowing the request to be submitted.

**Validates: Requirements 4.3**

### Property 13: Leave Request Initial Status

*For any* newly submitted leave request, the system should set the initial status to "Pending".

**Validates: Requirements 4.4**

### Property 14: Leave Credit Deduction on Approval

*For any* leave request that is approved, the system should deduct the requested days from the employee's remaining leave credits.

**Validates: Requirements 4.6**

### Property 15: Leave Credit Preservation on Denial

*For any* leave request that is denied, the system should not deduct any days from the employee's leave credits.

**Validates: Requirements 4.7**

### Property 16: Leave Credit Calculation

*For any* employee's leave credits, the remaining credits should always equal total credits minus used credits.

**Validates: Requirements 5.3**

### Property 17: Leave Credit Usage Tracking

*For any* approved leave that is taken, the system should increment the used credits for the corresponding leave type.

**Validates: Requirements 5.4**

### Property 18: Leave Credit Limit Enforcement

*For any* leave request that would exceed an employee's remaining credits, the system should prevent the request from being submitted.

**Validates: Requirements 5.5**

### Property 19: Employee Self-Service Data Access

*For any* employee user logged into the system, they should only be able to access their own personal data and not other employees' information.

**Validates: Requirements 6.1, 6.7**

### Property 20: Employee Self-Service Functionality

*For any* employee user, the system should allow them to view their attendance history, leave balance, profile information, submit leave requests, and view their leave request status.

**Validates: Requirements 6.2, 6.3, 6.4, 6.5, 6.6**

### Property 21: Report Generation with Date Ranges

*For any* valid date range, the system should generate accurate attendance summary reports containing all relevant data within that range.

**Validates: Requirements 7.1**

### Property 22: Report Filtering Functionality

*For any* valid filter criteria (date range, department, or employee), the system should generate reports that contain only data matching the specified filters.

**Validates: Requirements 7.4**

### Property 23: Attendance and Leave Calculations

*For any* set of attendance and leave data, the system should calculate accurate attendance percentages and leave utilization rates.

**Validates: Requirements 7.6**

### Property 24: Announcement Data Capture

*For any* valid announcement data, the system should capture the title, content, and author information accurately with automatic timestamping.

**Validates: Requirements 8.1, 8.2**

### Property 25: Announcement Chronological Ordering

*For any* set of announcements, the system should display them in chronological order with the newest announcements first.

**Validates: Requirements 8.5**

### Property 26: Authentication Access Control

*For any* access attempt to the system, valid login credentials should be required, and access should be denied for any request without proper authentication.

**Validates: Requirements 9.1**

### Property 27: Role-Based Access Control

*For any* authenticated user, the system should provide access levels appropriate to their role (full access for Admin users, restricted access for Employee users).

**Validates: Requirements 9.2, 9.3, 9.4**

### Property 28: Session Security Management

*For any* user session, the system should maintain security with appropriate timeouts and redirect unauthorized users to the login page.

**Validates: Requirements 9.5, 9.7**

### Property 29: API Error Handling

*For any* API response (success or error), the system should handle it gracefully and display appropriate messages to users.

**Validates: Requirements 10.2, 10.4**

### Property 30: Data Validation Before API Calls

*For any* data being sent to the Supabase API, the system should validate the data before making the API call.

**Validates: Requirements 10.3**

### Property 31: SQL Injection Prevention

*For any* database operation, the system should use prepared statement equivalents to prevent SQL injection attacks.

**Validates: Requirements 10.6**

### Property 32: Data Consistency Maintenance

*For any* sequence of operations performed on the system, data consistency should be maintained across all related records.

**Validates: Requirements 10.7**

### Property 33: Loading Indicator Display

*For any* data loading operation, the system should display appropriate loading indicators to inform users of the ongoing process.

**Validates: Requirements 11.4**

### Property 34: User Action Feedback

*For any* user action performed, the system should display appropriate success or error messages based on the action result.

**Validates: Requirements 11.6**

## Error Handling

The HRIS MVP implements comprehensive error handling across all system layers to ensure reliability and user experience:

### Frontend Error Handling

**AJAX Request Failures**:
- Network connectivity issues: Display "Connection lost" message with retry option
- Server errors (5xx): Display "Server temporarily unavailable" with automatic retry
- Client errors (4xx): Display specific error message from server response
- Timeout errors: Display "Request timed out" with retry option

**Form Validation Errors**:
- Real-time validation feedback for required fields
- Format validation for email addresses and phone numbers
- Business rule validation (e.g., date ranges, credit limits)
- Clear error messages positioned near relevant form fields

**Chart Rendering Errors**:
- Fallback to tabular data display if Chart.js fails to load
- Error message for insufficient data scenarios
- Graceful degradation for unsupported browsers

### Backend Error Handling

**Database Connection Errors**:
- Supabase API connection failures: Log error and return 503 Service Unavailable
- Authentication failures: Return 401 Unauthorized with clear message
- Rate limiting: Implement exponential backoff and return 429 Too Many Requests

**Data Validation Errors**:
- Input sanitization to prevent XSS attacks
- SQL injection prevention through parameterized queries
- Business rule validation with specific error codes
- Comprehensive logging of validation failures

**API Response Handling**:
- Structured error responses with error codes and messages
- Consistent error format across all endpoints
- Appropriate HTTP status codes for different error types
- Error logging for debugging and monitoring

### Security Error Handling

**Authentication Errors**:
- Invalid credentials: Generic "Invalid login" message to prevent user enumeration
- Session expiration: Automatic redirect to login with session timeout message
- Unauthorized access attempts: Log security event and return 403 Forbidden

**Authorization Errors**:
- Role-based access violations: Return 403 Forbidden with appropriate message
- Resource access violations: Verify user ownership before allowing operations
- Cross-user data access attempts: Log security violation and deny access

### Data Integrity Error Handling

**Constraint Violations**:
- Unique constraint violations: User-friendly messages for duplicate data
- Foreign key violations: Prevent orphaned records with clear error messages
- Check constraint violations: Validate business rules with specific feedback

**Concurrent Access Handling**:
- Optimistic locking for critical operations
- Conflict resolution for simultaneous updates
- Clear messaging for data conflicts

## Testing Strategy

The HRIS MVP employs a comprehensive testing strategy that combines property-based testing for core business logic with example-based testing for UI components and integration testing for external services.

### Property-Based Testing

**Framework**: PHPUnit with custom property generators
**Configuration**: Minimum 100 iterations per property test
**Coverage**: All 34 correctness properties defined above

**Property Test Implementation**:
- Each property test references its corresponding design document property
- Tag format: **Feature: hris-mvp, Property {number}: {property_text}**
- Custom generators for employee data, attendance records, leave requests
- Randomized test data to explore edge cases and boundary conditions

**Key Property Test Areas**:
- Employee data validation and uniqueness constraints
- Attendance calculation and status determination
- Leave credit management and validation
- Role-based access control verification
- Data consistency across operations

### Unit Testing

**Framework**: PHPUnit for backend, Jest for frontend JavaScript
**Focus Areas**:
- Individual function behavior with specific examples
- Edge cases not covered by property tests
- Error condition handling
- UI component rendering with mock data

**Unit Test Coverage**:
- PHP API endpoints with mock Supabase responses
- JavaScript utility functions and form validation
- Chart rendering with sample data sets
- Authentication and session management functions

### Integration Testing

**Framework**: PHPUnit with Supabase test environment
**Focus Areas**:
- Supabase API integration and error handling
- End-to-end user workflows
- Cross-module data consistency
- Performance under realistic load

**Integration Test Scenarios**:
- Complete employee lifecycle (create, update, delete)
- Leave request approval workflow
- Dashboard data aggregation and display
- Report generation with real data sets

### UI Testing

**Framework**: Selenium WebDriver for automated browser testing
**Focus Areas**:
- Cross-browser compatibility (Chrome, Firefox, Safari, Edge)
- Responsive design across device sizes
- Form submission and validation workflows
- Chart rendering and interaction

**Accessibility Testing**:
- WCAG 2.1 compliance verification
- Screen reader compatibility
- Keyboard navigation functionality
- Color contrast and visual accessibility

### Performance Testing

**Tools**: Apache Bench (ab) for load testing
**Metrics**:
- Response time under normal load (< 200ms for API calls)
- Concurrent user handling (target: 50 concurrent users)
- Database query performance optimization
- Frontend asset loading optimization

### Security Testing

**Areas of Focus**:
- SQL injection prevention verification
- XSS attack prevention
- CSRF protection implementation
- Authentication and authorization security
- Data encryption in transit and at rest

### Test Data Management

**Test Database**:
- Separate Supabase project for testing
- Automated test data setup and teardown
- Realistic data volumes for performance testing
- Data privacy compliance for test scenarios

**Mock Data Generation**:
- Faker library for realistic test data
- Consistent data relationships across tests
- Boundary value testing for edge cases
- Invalid data generation for negative testing

### Continuous Integration

**CI Pipeline**:
- Automated test execution on code commits
- Property test execution with full iteration counts
- Integration test runs against test database
- Performance regression detection
- Security vulnerability scanning

**Test Reporting**:
- Coverage reports for all test types
- Property test failure analysis with counterexamples
- Performance metrics tracking over time
- Security scan results and remediation tracking
## Implementation Approach

### Development Phases

The HRIS MVP will be developed in four phases to ensure systematic delivery and testing:

**Phase 1: Foundation and Authentication (Week 1-2)**
- Supabase project setup and database schema creation
- Basic HTML structure and Tailwind CSS integration
- User authentication system implementation
- Role-based access control foundation
- Basic navigation and layout components

**Phase 2: Core Employee Management (Week 3-4)**
- Employee CRUD operations
- Employee data validation and uniqueness checks
- Admin dashboard basic structure
- Employee self-service portal foundation
- Basic reporting infrastructure

**Phase 3: Attendance and Leave Systems (Week 5-6)**
- Attendance tracking functionality
- Time-in/time-out recording and status calculation
- Leave request submission and approval workflow
- Leave credit management system
- Dashboard analytics and Chart.js integration

**Phase 4: Reporting and Polish (Week 7-8)**
- Comprehensive reporting system
- Announcements functionality
- Dashboard completion with real-time updates
- Performance optimization and security hardening
- User acceptance testing and bug fixes

### File Structure and Organization

```
htdocs/hris-mvp/
├── index.html                          # Login page
├── dashboard/
│   ├── admin.html                      # Admin dashboard
│   ├── employee.html                   # Employee dashboard
│   └── components/
│       ├── charts.js                   # Chart.js components
│       ├── metrics.js                  # Metrics display
│       └── refresh.js                  # Auto-refresh functionality
├── modules/
│   ├── employees/
│   │   ├── list.html                   # Employee listing
│   │   ├── create.html                 # Add new employee
│   │   ├── edit.html                   # Edit employee
│   │   ├── view.html                   # View employee details
│   │   └── profile.html                # Employee self-service profile
│   ├── attendance/
│   │   ├── daily.html                  # Daily attendance view
│   │   ├── weekly.html                 # Weekly attendance summary
│   │   ├── record.html                 # Time-in/time-out recording
│   │   └── history.html                # Attendance history
│   ├── leave/
│   │   ├── request.html                # Leave request form
│   │   ├── approve.html                # Leave approval interface
│   │   ├── balance.html                # Leave balance view
│   │   └── history.html                # Leave request history
│   ├── reports/
│   │   ├── attendance.html             # Attendance reports
│   │   ├── leave.html                  # Leave reports
│   │   ├── headcount.html              # Department headcount
│   │   └── export.html                 # Report export functionality
│   └── announcements/
│       ├── list.html                   # Announcements listing
│       ├── create.html                 # Create announcement
│       └── edit.html                   # Edit announcement
├── api/
│   ├── config/
│   │   ├── supabase.php                # Supabase configuration
│   │   ├── database.php                # Database connection helper
│   │   └── auth.php                    # Authentication helper
│   ├── auth/
│   │   ├── login.php                   # User login endpoint
│   │   ├── logout.php                  # User logout endpoint
│   │   ├── verify.php                  # Session verification
│   │   └── refresh.php                 # Token refresh
│   ├── employees/
│   │   ├── list.php                    # Get employees list
│   │   ├── create.php                  # Create new employee
│   │   ├── update.php                  # Update employee
│   │   ├── delete.php                  # Soft delete employee
│   │   ├── search.php                  # Search employees
│   │   └── profile.php                 # Get employee profile
│   ├── attendance/
│   │   ├── timein.php                  # Record time-in
│   │   ├── timeout.php                 # Record time-out
│   │   ├── daily.php                   # Get daily attendance
│   │   ├── weekly.php                  # Get weekly summary
│   │   ├── history.php                 # Get attendance history
│   │   └── override.php                # Manual attendance override
│   ├── leave/
│   │   ├── request.php                 # Submit leave request
│   │   ├── approve.php                 # Approve leave request
│   │   ├── deny.php                    # Deny leave request
│   │   ├── balance.php                 # Get leave balance
│   │   ├── history.php                 # Get leave history
│   │   └── credits.php                 # Manage leave credits
│   ├── reports/
│   │   ├── attendance.php              # Attendance reports
│   │   ├── leave.php                   # Leave reports
│   │   ├── headcount.php               # Headcount reports
│   │   └── export.php                  # Export functionality
│   ├── announcements/
│   │   ├── list.php                    # Get announcements
│   │   ├── create.php                  # Create announcement
│   │   ├── update.php                  # Update announcement
│   │   └── deactivate.php              # Deactivate announcement
│   └── dashboard/
│       ├── metrics.php                 # Dashboard metrics
│       ├── charts.php                  # Chart data
│       └── trends.php                  # Trend analysis
├── assets/
│   ├── css/
│   │   ├── custom.css                  # Custom styles (minimal)
│   │   └── print.css                   # Print-specific styles
│   ├── js/
│   │   ├── app.js                      # Main application logic
│   │   ├── auth.js                     # Authentication handling
│   │   ├── api.js                      # API communication layer
│   │   ├── utils.js                    # Utility functions
│   │   ├── validation.js               # Form validation
│   │   ├── charts.js                   # Chart.js integration
│   │   └── modules/
│   │       ├── employees.js            # Employee management
│   │       ├── attendance.js           # Attendance functionality
│   │       ├── leave.js                # Leave management
│   │       ├── reports.js              # Reporting functionality
│   │       └── announcements.js       # Announcements
│   ├── images/
│   │   ├── logo.png                    # Company logo
│   │   ├── icons/                      # UI icons
│   │   └── avatars/                    # Default user avatars
│   └── vendor/
│       ├── tailwindcss/                # Tailwind CSS files
│       └── chartjs/                    # Chart.js library files
├── tests/
│   ├── unit/
│   │   ├── php/                        # PHP unit tests
│   │   └── js/                         # JavaScript unit tests
│   ├── integration/                    # Integration tests
│   ├── property/                       # Property-based tests
│   └── fixtures/                       # Test data fixtures
└── docs/
    ├── api.md                          # API documentation
    ├── deployment.md                   # Deployment guide
    └── user-guide.md                   # User manual
```

### Database Setup and Configuration

**Supabase Project Configuration**:
1. Create new Supabase project for HRIS MVP
2. Configure Row Level Security (RLS) policies for data protection
3. Set up authentication providers and user roles
4. Create database functions for complex business logic
5. Configure real-time subscriptions for dashboard updates

**Database Initialization Script**:
```sql
-- Enable Row Level Security (Optional - for additional security layer)
ALTER TABLE employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE attendance ENABLE ROW LEVEL SECURITY;
ALTER TABLE leave_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE leave_credits ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcements ENABLE ROW LEVEL SECURITY;
ALTER TABLE admins ENABLE ROW LEVEL SECURITY;

-- Note: RLS policies are optional since authentication is handled at application level
-- These can be implemented for additional security if needed

-- Initialize default leave types
INSERT INTO leave_types (name, days_allowed) VALUES
    ('Annual Leave', 15),
    ('Sick Leave', 10),
    ('Emergency Leave', 5),
    ('Maternity/Paternity Leave', 60);

-- Initialize work calendar with basic working days (Monday-Friday)
-- This should be populated with company-specific holidays and non-working days
INSERT INTO work_calendar (date, is_working_day, description)
SELECT 
    generate_series::date,
    CASE 
        WHEN EXTRACT(DOW FROM generate_series) IN (0, 6) THEN false -- Sunday=0, Saturday=6
        ELSE true 
    END,
    CASE 
        WHEN EXTRACT(DOW FROM generate_series) = 0 THEN 'Sunday'
        WHEN EXTRACT(DOW FROM generate_series) = 6 THEN 'Saturday'
        ELSE NULL
    END
FROM generate_series(
    CURRENT_DATE,
    CURRENT_DATE + INTERVAL '2 years',
    INTERVAL '1 day'
);

-- Create indexes for performance
CREATE INDEX idx_attendance_employee_date ON attendance(employee_id, date);
CREATE INDEX idx_leave_requests_employee ON leave_requests(employee_id);
CREATE INDEX idx_leave_credits_employee_year ON leave_credits(employee_id, year);
CREATE INDEX idx_work_calendar_date ON work_calendar(date);
CREATE INDEX idx_leave_credit_audit_employee ON leave_credit_audit(employee_id);
```

### Security Implementation

**Authentication Security**:
- **Supabase Authentication Integration**: 
  - All user authentication handled via Supabase Auth API as required
  - Admins and employees both use Supabase authentication with role-based metadata
  - JWT token validation for all API requests
  - Supabase handles password hashing, reset, and security
- **Role Management**: User roles stored in Supabase user metadata
- **Session Management**: Supabase JWT tokens with automatic refresh
- **Password Security**: Handled entirely by Supabase with industry standards
- **Account Security**: Supabase provides built-in brute force protection

**Authorization Security**:
- Role-based access control using Supabase user metadata and JWT claims
- Database-level Row Level Security (RLS) policies enforced by Supabase
- API endpoint authorization using Supabase JWT verification
- Frontend route protection based on Supabase authentication state

**Data Security**:
- HTTPS enforcement for all communications
- Input sanitization and validation on all forms
- SQL injection prevention through parameterized queries
- XSS protection through proper output encoding
- CSRF protection for state-changing operations

**API Security**:
- Rate limiting to prevent abuse
- Request validation and sanitization
- Proper error handling without information disclosure
- API key management for Supabase integration
- Audit logging for sensitive operations

### Performance Optimization

**Frontend Optimization**:
- Lazy loading of non-critical JavaScript modules
- Image optimization and compression
- CSS minification and critical path optimization
- Browser caching strategies for static assets
- Progressive enhancement for better perceived performance

**Backend Optimization**:
- Database query optimization with proper indexing
- API response caching for frequently accessed data
- Connection pooling for database connections
- Efficient pagination for large data sets
- Background processing for heavy operations

**Database Optimization**:
- Proper indexing strategy for common queries
- Query optimization and execution plan analysis
- Data archiving strategy for historical records
- Regular maintenance and statistics updates
- Connection pooling and timeout configuration

### Deployment Configuration

**XAMPP Setup Requirements**:
- PHP 8.0 or higher with required extensions
- Apache HTTP Server with mod_rewrite enabled
- SSL certificate configuration for HTTPS
- PHP configuration optimization for production
- Error logging and monitoring setup

**Environment Configuration**:
```php
// config/environment.php
<?php
define('ENVIRONMENT', 'production'); // or 'development'
define('SUPABASE_URL', 'your-supabase-url');
define('SUPABASE_ANON_KEY', 'your-anon-key');
define('SUPABASE_SERVICE_KEY', 'your-service-key');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('API_RATE_LIMIT', 100); // requests per minute
?>
```

**Apache Configuration**:
```apache
# .htaccess for clean URLs and security
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Cache control for static assets
<FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</FilesMatch>
```

### Monitoring and Maintenance

**Application Monitoring**:
- Error logging and alerting system
- Performance metrics tracking
- User activity monitoring
- API usage analytics
- Database performance monitoring

**Maintenance Procedures**:
- Regular database backups and recovery testing
- Security updates and patch management
- Performance optimization reviews
- User feedback collection and analysis
- System health checks and monitoring

**Backup Strategy**:
- Automated daily database backups via Supabase
- Configuration file backups
- Application code version control
- Disaster recovery procedures
- Data retention and archival policies

This comprehensive design provides a solid foundation for implementing the HRIS MVP with all specified requirements while maintaining security, performance, and maintainability standards suitable for XAMPP deployment.