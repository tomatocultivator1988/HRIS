# Design Document: Recruitment Module

## Overview

This document provides the technical design for the Recruitment Module, detailing the database schema, service layer architecture, API endpoints, UI components, and integration points with the existing HRIS system.

## Database Schema Design

### Table: job_postings

Stores information about open positions that the organization is hiring for.

```sql
CREATE TABLE IF NOT EXISTS job_postings (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    job_title VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    num_openings INTEGER NOT NULL CHECK (num_openings >= 0),
    description TEXT,
    status VARCHAR(20) DEFAULT 'Open' CHECK (status IN ('Open', 'Closed', 'On Hold')),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_job_postings_status ON job_postings(status);
CREATE INDEX idx_job_postings_department ON job_postings(department);
CREATE INDEX idx_job_postings_position ON job_postings(position);
```

**Fields:**
- `id`: UUID primary key
- `job_title`: Title of the job posting (e.g., "Senior Software Developer")
- `department`: Department name (matches employees.department)
- `position`: Position name (matches employees.position)
- `num_openings`: Number of positions available (decrements when applicants are hired)
- `description`: Detailed job description
- `status`: Current status (Open, Closed, On Hold)
- `created_at`, `updated_at`: Timestamps

### Table: applicants

Stores candidate information with fields matching the employees table for seamless conversion.

```sql
CREATE TABLE IF NOT EXISTS applicants (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    job_posting_id UUID REFERENCES job_postings(id) ON DELETE RESTRICT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    work_email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    employment_status VARCHAR(50) NOT NULL CHECK (employment_status IN ('Regular', 'Probationary', 'Contractual', 'Part-time')),
    status VARCHAR(20) DEFAULT 'Applied' CHECK (status IN ('Applied', 'In Progress', 'Passed', 'Failed', 'Hired')),
    employee_id UUID REFERENCES employees(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_applicants_job_posting ON applicants(job_posting_id);
CREATE INDEX idx_applicants_status ON applicants(status);
CREATE INDEX idx_applicants_email ON applicants(work_email);
CREATE INDEX idx_applicants_employee ON applicants(employee_id);
```

**Fields:**
- `id`: UUID primary key
- `job_posting_id`: Foreign key to job_postings (RESTRICT prevents deletion of postings with applicants)
- `first_name`, `last_name`: Applicant name (matches employees table)
- `work_email`: Email address (unique, matches employees.work_email)
- `mobile_number`: Phone number (matches employees.mobile_number)
- `department`: Department (matches employees.department)
- `position`: Position (matches employees.position)
- `employment_status`: Employment type (matches employees.employment_status)
- `status`: Application status (Applied, In Progress, Passed, Failed, Hired)
- `employee_id`: Foreign key to employees (set when hired, NULL otherwise)
- `is_active`: Soft delete flag
- `created_at`, `updated_at`: Timestamps

### Table: applicant_evaluations

Stores evaluation scores and notes for each stage of the hiring process.

```sql
CREATE TABLE IF NOT EXISTS applicant_evaluations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    applicant_id UUID NOT NULL REFERENCES applicants(id) ON DELETE CASCADE,
    stage_name VARCHAR(50) NOT NULL CHECK (stage_name IN ('Screening', 'Interview 1', 'Interview 2', 'Final Interview')),
    score DECIMAL(5,2) NOT NULL CHECK (score >= 0 AND score <= 100),
    notes TEXT,
    interviewer_name VARCHAR(255) NOT NULL,
    evaluation_date DATE NOT NULL CHECK (evaluation_date <= CURRENT_DATE),
    pass_fail BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(applicant_id, stage_name)
);

CREATE INDEX idx_evaluations_applicant ON applicant_evaluations(applicant_id);
CREATE INDEX idx_evaluations_stage ON applicant_evaluations(stage_name);
CREATE INDEX idx_evaluations_pass_fail ON applicant_evaluations(pass_fail);
```

**Fields:**
- `id`: UUID primary key
- `applicant_id`: Foreign key to applicants (CASCADE deletes evaluations with applicant)
- `stage_name`: Evaluation stage (Screening, Interview 1, Interview 2, Final Interview)
- `score`: Numeric score from 0 to 100
- `notes`: Evaluation notes and feedback
- `interviewer_name`: Name of the person who conducted the evaluation
- `evaluation_date`: Date of evaluation (cannot be in future)
- `pass_fail`: Boolean indicating if applicant passed this stage
- `created_at`, `updated_at`: Timestamps
- **UNIQUE constraint**: One evaluation per applicant per stage

### Database Triggers

```sql
-- Trigger to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_recruitment_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_job_postings_updated_at
    BEFORE UPDATE ON job_postings
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();

CREATE TRIGGER trigger_applicants_updated_at
    BEFORE UPDATE ON applicants
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();

CREATE TRIGGER trigger_evaluations_updated_at
    BEFORE UPDATE ON applicant_evaluations
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();
```

## Service Layer Design

### Class: RecruitmentService

**Location:** `src/Services/RecruitmentService.php`

**Dependencies:**
- `Models\JobPosting`
- `Models\Applicant`
- `Models\ApplicantEvaluation`
- `Services\EmployeeService`
- `Core\ValidationException`
- `Core\NotFoundException`

#### Job Posting Methods

```php
/**
 * Create a new job posting
 * 
 * @param array $data Job posting data
 * @return array Created job posting
 * @throws ValidationException
 */
public function createJobPosting(array $data): array

/**
 * Update existing job posting
 * 
 * @param string $id Job posting ID
 * @param array $data Update data
 * @return array Updated job posting
 * @throws NotFoundException, ValidationException
 */
public function updateJobPosting(string $id, array $data): array

/**
 * Get all job postings with filtering
 * 
 * @param array $filters Filter parameters (status, department)
 * @return array Job postings list
 */
public function getJobPostings(array $filters = []): array

/**
 * Get job posting by ID
 * 
 * @param string $id Job posting ID
 * @return array Job posting data
 * @throws NotFoundException
 */
public function getJobPostingById(string $id): array
```

**Validation Rules (Job Postings):**
- `job_title`: Required, 3-255 characters
- `department`: Required, 1-100 characters
- `position`: Required, 1-100 characters
- `num_openings`: Required, positive integer
- `status`: Must be one of: Open, Closed, On Hold

#### Applicant Methods

```php
/**
 * Create a new applicant
 * 
 * @param array $data Applicant data
 * @return array Created applicant
 * @throws ValidationException
 */
public function createApplicant(array $data): array

/**
 * Update existing applicant
 * 
 * @param string $id Applicant ID
 * @param array $data Update data
 * @return array Updated applicant
 * @throws NotFoundException, ValidationException
 */
public function updateApplicant(string $id, array $data): array

/**
 * Get all applicants with filtering
 * 
 * @param array $filters Filter parameters (job_posting_id, status)
 * @return array Applicants list
 */
public function getApplicants(array $filters = []): array

/**
 * Get applicant by ID with evaluations
 * 
 * @param string $id Applicant ID
 * @return array Applicant data with evaluations and final score
 * @throws NotFoundException
 */
public function getApplicantById(string $id): array
```

**Validation Rules (Applicants):**
- `first_name`: Required, 2-100 characters
- `last_name`: Required, 2-100 characters
- `work_email`: Required, valid email format, unique
- `mobile_number`: Optional, valid phone format
- `department`: Required, 1-100 characters
- `position`: Required, 1-100 characters
- `employment_status`: Required, one of: Regular, Probationary, Contractual, Part-time
- `job_posting_id`: Required, must exist

#### Evaluation Methods

```php
/**
 * Create or update evaluation for an applicant stage
 * 
 * @param string $applicantId Applicant ID
 * @param array $data Evaluation data
 * @return array Created/updated evaluation
 * @throws ValidationException, NotFoundException
 */
public function saveEvaluation(string $applicantId, array $data): array

/**
 * Get all evaluations for an applicant
 * 
 * @param string $applicantId Applicant ID
 * @return array Evaluations list
 * @throws NotFoundException
 */
public function getEvaluations(string $applicantId): array

/**
 * Calculate final score for an applicant
 * 
 * @param string $applicantId Applicant ID
 * @return float|null Final score (average of all stages) or null if no evaluations
 */
public function calculateFinalScore(string $applicantId): ?float
```

**Validation Rules (Evaluations):**
- `stage_name`: Required, one of: Screening, Interview 1, Interview 2, Final Interview
- `score`: Required, number between 0 and 100
- `notes`: Optional, text
- `interviewer_name`: Required, 1-255 characters
- `evaluation_date`: Required, valid date, not in future
- `pass_fail`: Required, boolean

#### Hiring Method

```php
/**
 * Hire an applicant by creating an employee record
 * 
 * This method:
 * 1. Validates hiring eligibility (all stages complete, passing scores)
 * 2. Calls EmployeeService.createEmployee() with applicant data
 * 3. Updates applicant status to 'Hired' and links to employee
 * 4. Decrements job posting openings
 * 5. Auto-closes job posting if openings reach zero
 * 
 * @param string $applicantId Applicant ID
 * @param float $minimumPassingScore Minimum required final score (default: 70.0)
 * @return array Hiring result with employee and applicant data
 * @throws ValidationException, NotFoundException, Exception
 */
public function hireApplicant(string $applicantId, float $minimumPassingScore = 70.0): array
```

**Hiring Validation Logic:**
1. Check all 4 stages have evaluations
2. Check each evaluation has all required fields (score, notes, interviewer, date, pass_fail)
3. Check all stages marked as Pass
4. Calculate final score (average of all stage scores)
5. Check final score >= minimum passing score
6. Check job posting has available openings (num_openings > 0)
7. Check applicant not already hired

**Hiring Process Flow:**
```
1. BEGIN TRANSACTION
2. Validate hiring eligibility
3. Prepare employee data from applicant:
   - first_name → first_name
   - last_name → last_name
   - work_email → work_email
   - mobile_number → mobile_number
   - department → department
   - position → position
   - employment_status → employment_status
   - date_hired → current date
4. Call EmployeeService.createEmployee(employeeData)
5. Update applicant:
   - status = 'Hired'
   - employee_id = created employee ID
6. Decrement job_posting.num_openings by 1
7. If num_openings == 0, set job_posting.status = 'Closed'
8. COMMIT TRANSACTION
9. Return success with employee and applicant data
```

**Error Handling:**
- If validation fails: Throw ValidationException with specific errors
- If createEmployee() fails: ROLLBACK transaction, throw Exception
- If database update fails: ROLLBACK transaction, throw Exception

## Controller Layer Design

### Class: RecruitmentController

**Location:** `src/Controllers/RecruitmentController.php`

**Extends:** `Core\Controller`

**Dependencies:**
- `Services\RecruitmentService`
- `Core\Request`
- `Core\Response`
- `Core\View`

#### API Endpoints

##### Job Postings

```php
/**
 * List all job postings
 * GET /api/recruitment/jobs
 * Query params: status, department, limit, offset
 */
public function listJobs(Request $request): Response

/**
 * Get specific job posting
 * GET /api/recruitment/jobs/{id}
 */
public function getJob(Request $request): Response

/**
 * Create new job posting
 * POST /api/recruitment/jobs
 * Body: {job_title, department, position, num_openings, description, status}
 */
public function createJob(Request $request): Response

/**
 * Update job posting
 * PUT /api/recruitment/jobs/{id}
 * Body: {job_title?, department?, position?, num_openings?, description?, status?}
 */
public function updateJob(Request $request): Response
```

##### Applicants

```php
/**
 * List all applicants
 * GET /api/recruitment/applicants
 * Query params: job_posting_id, status, limit, offset
 */
public function listApplicants(Request $request): Response

/**
 * Get specific applicant with evaluations
 * GET /api/recruitment/applicants/{id}
 */
public function getApplicant(Request $request): Response

/**
 * Create new applicant
 * POST /api/recruitment/applicants
 * Body: {job_posting_id, first_name, last_name, work_email, mobile_number, 
 *        department, position, employment_status}
 */
public function createApplicant(Request $request): Response

/**
 * Update applicant
 * PUT /api/recruitment/applicants/{id}
 * Body: {first_name?, last_name?, work_email?, mobile_number?, 
 *        department?, position?, employment_status?}
 */
public function updateApplicant(Request $request): Response
```

##### Evaluations

```php
/**
 * Get evaluations for an applicant
 * GET /api/recruitment/applicants/{id}/evaluations
 */
public function getEvaluations(Request $request): Response

/**
 * Create or update evaluation
 * POST /api/recruitment/evaluations
 * Body: {applicant_id, stage_name, score, notes, interviewer_name, 
 *        evaluation_date, pass_fail}
 */
public function saveEvaluation(Request $request): Response
```

##### Hiring

```php
/**
 * Hire an applicant
 * POST /api/recruitment/applicants/{id}/hire
 * Body: {minimum_passing_score?} (optional, defaults to 70.0)
 */
public function hireApplicant(Request $request): Response
```

#### View Endpoints

```php
/**
 * Display recruitment management interface
 * GET /recruitment
 */
public function indexView(Request $request): Response
```

#### Request/Response Formats

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": "Validation error message"
  }
}
```

**Hire Response (Success):**
```json
{
  "success": true,
  "data": {
    "employee": {
      "id": "uuid",
      "employee_id": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "work_email": "john.doe@company.com",
      "temporary_password": "John09123456789",
      "password_message": "This is a temporary password..."
    },
    "applicant": {
      "id": "uuid",
      "status": "Hired",
      "employee_id": "uuid",
      "final_score": 85.5
    },
    "job_posting": {
      "id": "uuid",
      "num_openings": 2,
      "status": "Open"
    }
  },
  "message": "Applicant hired successfully"
}
```

## UI Component Design

### Main Recruitment Page

**Location:** `src/Views/recruitment/index.php`

**Layout Structure:**
```
┌─────────────────────────────────────────────────────────┐
│ Header: "Recruitment"                    [+ New Posting] │
├─────────────────────────────────────────────────────────┤
│ Tabs: [Job Postings] [Applicants] [Pipeline View]       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Content Area (Tab-specific)                            │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Tab 1: Job Postings

**Features:**
- Table with columns: Job Title, Department, Position, Openings, Status, Actions
- Filter by: Status, Department
- Actions: View, Edit, View Applicants
- "New Posting" button opens modal

**Job Posting Modal:**
```
Fields:
- Job Title * (text input)
- Department * (text input)
- Position * (text input)
- Number of Openings * (number input, min=1)
- Description (textarea)
- Status (select: Open, Closed, On Hold)

Buttons: [Cancel] [Save]
```

### Tab 2: Applicants

**Features:**
- Table with columns: Name, Email, Position, Job Posting, Status, Final Score, Actions
- Filter by: Job Posting, Status
- Actions: View Details, Edit, Evaluate, Hire
- "Add Applicant" button opens modal

**Applicant Modal:**
```
Fields:
- Job Posting * (select dropdown)
- First Name * (text input)
- Last Name * (text input)
- Email * (email input)
- Phone (tel input)
- Department * (text input)
- Position * (text input)
- Employment Status * (select: Regular, Probationary, Contractual, Part-time)

Buttons: [Cancel] [Save]
```

### Applicant Detail View

**Modal Layout:**
```
┌─────────────────────────────────────────────────────────┐
│ John Doe - Software Developer                      [×]   │
├─────────────────────────────────────────────────────────┤
│ Personal Info:                                          │
│ Email: john.doe@company.com                             │
│ Phone: 09123456789                                      │
│ Department: IT | Position: Developer                    │
│ Employment Status: Regular                              │
├─────────────────────────────────────────────────────────┤
│ Evaluation Stages:                    Final Score: 85.5 │
│                                                          │
│ ✓ Screening              Score: 80  [View/Edit]         │
│   Interviewer: Jane Smith | Date: 2024-01-15            │
│   Status: Pass                                          │
│                                                          │
│ ✓ Interview 1            Score: 85  [View/Edit]         │
│   Interviewer: Bob Johnson | Date: 2024-01-20           │
│   Status: Pass                                          │
│                                                          │
│ ✓ Interview 2            Score: 90  [View/Edit]         │
│   Interviewer: Alice Brown | Date: 2024-01-25           │
│   Status: Pass                                          │
│                                                          │
│ ✓ Final Interview        Score: 87  [View/Edit]         │
│   Interviewer: CEO | Date: 2024-01-30                   │
│   Status: Pass                                          │
├─────────────────────────────────────────────────────────┤
│ Hiring Eligibility: ✓ All requirements met              │
│                                                          │
│                    [Close] [Hire Applicant]             │
└─────────────────────────────────────────────────────────┘
```

### Evaluation Modal

```
┌─────────────────────────────────────────────────────────┐
│ Evaluate: John Doe - Screening Stage              [×]   │
├─────────────────────────────────────────────────────────┤
│ Stage: Screening (read-only)                            │
│                                                          │
│ Score * (0-100):  [____]                                │
│                                                          │
│ Interviewer Name *: [_________________________]         │
│                                                          │
│ Evaluation Date *:  [____-__-__]                        │
│                                                          │
│ Pass/Fail *:  ( ) Pass  ( ) Fail                        │
│                                                          │
│ Notes:                                                  │
│ [____________________________________________]           │
│ [____________________________________________]           │
│ [____________________________________________]           │
│                                                          │
│                    [Cancel] [Save Evaluation]           │
└─────────────────────────────────────────────────────────┘
```

### Hire Confirmation Modal

```
┌─────────────────────────────────────────────────────────┐
│ ⚠ Confirm Hiring                                   [×]   │
├─────────────────────────────────────────────────────────┤
│ You are about to hire:                                  │
│                                                          │
│ Name: John Doe                                          │
│ Position: Software Developer                            │
│ Final Score: 85.5 / 100                                 │
│                                                          │
│ This will:                                              │
│ ✓ Create an employee record                             │
│ ✓ Generate Supabase authentication account              │
│ ✓ Send temporary password to admin                      │
│ ✓ Initialize leave credits                              │
│ ✓ Decrement job posting openings                        │
│                                                          │
│ ⚠ This action cannot be undone.                         │
│                                                          │
│                    [Cancel] [Confirm Hire]              │
└─────────────────────────────────────────────────────────┘
```

### Success Modal (After Hiring)

```
┌─────────────────────────────────────────────────────────┐
│ ✓ Employee Created Successfully!                   [×]   │
├─────────────────────────────────────────────────────────┤
│ John Doe has been hired as Software Developer.          │
│                                                          │
│ Employee ID: EMP001                                     │
│                                                          │
│ Temporary Password:                                     │
│ ┌─────────────────────────────────────┐                │
│ │ John09123456789              [Copy] │                │
│ └─────────────────────────────────────┘                │
│                                                          │
│ ⚠ Please save this password. It will not be shown      │
│   again. Employee must change password on first login.  │
│                                                          │
│                              [Close]                    │
└─────────────────────────────────────────────────────────┘
```

### Tab 3: Pipeline View (Optional Enhancement)

**Features:**
- Kanban-style board with columns: Applied, Screening, Interview 1, Interview 2, Final Interview, Hired
- Drag-and-drop applicant cards between stages
- Each card shows: Name, Position, Current Score
- Click card to open detail view

## Routing Configuration

**Location:** `config/routes.php`

```php
// Recruitment Module Routes

// View Routes
$router->get('/recruitment', 'RecruitmentController@indexView');

// API Routes - Job Postings
$router->get('/api/recruitment/jobs', 'RecruitmentController@listJobs');
$router->get('/api/recruitment/jobs/{id}', 'RecruitmentController@getJob');
$router->post('/api/recruitment/jobs', 'RecruitmentController@createJob');
$router->put('/api/recruitment/jobs/{id}', 'RecruitmentController@updateJob');

// API Routes - Applicants
$router->get('/api/recruitment/applicants', 'RecruitmentController@listApplicants');
$router->get('/api/recruitment/applicants/{id}', 'RecruitmentController@getApplicant');
$router->post('/api/recruitment/applicants', 'RecruitmentController@createApplicant');
$router->put('/api/recruitment/applicants/{id}', 'RecruitmentController@updateApplicant');

// API Routes - Evaluations
$router->get('/api/recruitment/applicants/{id}/evaluations', 'RecruitmentController@getEvaluations');
$router->post('/api/recruitment/evaluations', 'RecruitmentController@saveEvaluation');

// API Routes - Hiring
$router->post('/api/recruitment/applicants/{id}/hire', 'RecruitmentController@hireApplicant');
```

## Integration Points

### EmployeeService Integration

**Method:** `RecruitmentService::hireApplicant()` → `EmployeeService::createEmployee()`

**Data Mapping:**
```php
// In RecruitmentService::hireApplicant()
$employeeData = [
    'first_name' => $applicant['first_name'],
    'last_name' => $applicant['last_name'],
    'work_email' => $applicant['work_email'],
    'mobile_number' => $applicant['mobile_number'],
    'department' => $applicant['department'],
    'position' => $applicant['position'],
    'employment_status' => $applicant['employment_status'],
    'date_hired' => date('Y-m-d') // Current date
];

// Call existing employee creation
$employee = $this->employeeService->createEmployee($employeeData);
```

**What EmployeeService.createEmployee() Does:**
1. Validates employee data
2. Generates employee_id (e.g., "EMP001")
3. Creates Supabase Auth User with auto-generated password
4. Creates employee record in database
5. Sets `force_password_change = true`
6. Initializes leave credits for all leave types
7. Returns employee data including temporary password

**Error Handling:**
- If `createEmployee()` throws ValidationException: Catch, rollback transaction, re-throw
- If `createEmployee()` throws any Exception: Catch, rollback transaction, throw new Exception with context
- If database updates fail after successful employee creation: Log error, but employee creation is already committed (EmployeeService handles its own transaction)

### Sidebar Navigation Integration

**Location:** `src/Views/layouts/admin_sidebar.php`

Add new navigation item:
```html
<a href="<?= base_url('/recruitment') ?>" 
   class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors <?= $currentPage === 'recruitment' ? 'bg-slate-700 text-white border-l-4 border-blue-500' : '' ?>">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
    </svg>
    Recruitment
</a>
```

**Navigation Order:**
1. Dashboard
2. Employees
3. Attendance
4. Leave Requests
5. **Recruitment** (NEW)
6. Manage Salaries
7. Payroll
8. Reports

## Security Considerations

1. **Authentication:** All recruitment endpoints require admin authentication
2. **Authorization:** Only admin role can access recruitment features
3. **Input Validation:** All user inputs validated before database operations
4. **SQL Injection Prevention:** Use parameterized queries (handled by Model layer)
5. **XSS Prevention:** Escape all output in views
6. **CSRF Protection:** Use CSRF tokens for all forms (if implemented in system)
7. **Email Uniqueness:** Prevent duplicate applicant emails
8. **Transaction Safety:** Use database transactions for hire operation

## Performance Considerations

1. **Database Indexes:** Created on frequently queried fields (status, job_posting_id, applicant_id)
2. **Pagination:** Implement pagination for job postings and applicants lists
3. **Lazy Loading:** Load evaluations only when viewing applicant details
4. **Caching:** Consider caching job posting list for public view (future enhancement)
5. **Query Optimization:** Use JOIN queries to fetch related data in single query

## Future Enhancements

1. **Email Notifications:** Send emails to applicants at each stage
2. **Document Upload:** Allow applicants to upload resume and cover letter
3. **Interview Scheduling:** Calendar integration for scheduling interviews
4. **Applicant Portal:** Self-service portal for applicants to check status
5. **Reporting:** Analytics dashboard for recruitment metrics
6. **Bulk Operations:** Bulk import applicants from CSV
7. **Custom Evaluation Stages:** Allow admins to configure custom stages
8. **Collaborative Evaluation:** Multiple interviewers can score same stage

## Testing Considerations

1. **Unit Tests:** Test all service methods with various inputs
2. **Integration Tests:** Test hiring workflow end-to-end
3. **Validation Tests:** Test all validation rules
4. **Transaction Tests:** Verify rollback on errors
5. **UI Tests:** Test all modals and form submissions
6. **Edge Cases:** Test hiring with zero openings, duplicate emails, incomplete evaluations

## Migration Script

**Location:** `docs/migrations/create_recruitment_tables.sql`

This script will create all three tables with proper constraints, indexes, and triggers.
