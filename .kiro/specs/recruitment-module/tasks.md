# Implementation Tasks: Recruitment Module

## Task 1: Database Schema Setup

Create the database tables and triggers for the Recruitment Module.

### Sub-tasks:
- [x] 1.1 Create migration script `docs/migrations/create_recruitment_tables.sql`
- [x] 1.2 Add job_postings table with all fields, constraints, and indexes
- [x] 1.3 Add applicants table with all fields, constraints, and indexes
- [x] 1.4 Add applicant_evaluations table with all fields, constraints, and indexes
- [x] 1.5 Create update_recruitment_updated_at() trigger function
- [x] 1.6 Add triggers for all three tables to auto-update updated_at timestamps
- [ ] 1.7 Test migration script in Supabase dashboard

## Task 2: Create Model Classes

Implement the Model layer for database operations.

### Sub-tasks:
- [x] 2.1 Create `src/Models/JobPosting.php` extending base Model
- [x] 2.2 Implement JobPosting model methods (all, find, create, update, where)
- [x] 2.3 Create `src/Models/Applicant.php` extending base Model
- [x] 2.4 Implement Applicant model methods (all, find, create, update, where)
- [x] 2.5 Create `src/Models/ApplicantEvaluation.php` extending base Model
- [x] 2.6 Implement ApplicantEvaluation model methods (all, find, create, update, where)

## Task 3: Implement RecruitmentService - Job Posting Methods

Implement job posting business logic in the service layer.

### Sub-tasks:
- [x] 3.1 Create `src/Services/RecruitmentService.php` with constructor and dependencies
- [x] 3.2 Implement createJobPosting() with validation
- [x] 3.3 Implement updateJobPosting() with validation
- [x] 3.4 Implement getJobPostings() with filtering (status, department)
- [x] 3.5 Implement getJobPostingById() with error handling
- [x] 3.6 Add validation helper methods for job postings

## Task 4: Implement RecruitmentService - Applicant Methods

Implement applicant business logic in the service layer.

### Sub-tasks:
- [x] 4.1 Implement createApplicant() with validation
- [x] 4.2 Implement updateApplicant() with validation
- [x] 4.3 Implement getApplicants() with filtering (job_posting_id, status)
- [x] 4.4 Implement getApplicantById() with evaluations and final score
- [x] 4.5 Add validation helper methods for applicants
- [x] 4.6 Add email uniqueness validation

## Task 5: Implement RecruitmentService - Evaluation Methods

Implement evaluation scoring business logic in the service layer.

### Sub-tasks:
- [x] 5.1 Implement saveEvaluation() for creating/updating evaluations
- [x] 5.2 Implement getEvaluations() to fetch all evaluations for an applicant
- [x] 5.3 Implement calculateFinalScore() to compute average of all stage scores
- [x] 5.4 Add validation helper methods for evaluations
- [x] 5.5 Validate stage names (Screening, Interview 1, Interview 2, Final Interview)
- [x] 5.6 Validate score range (0-100)
- [x] 5.7 Validate evaluation date (not in future)

## Task 6: Implement RecruitmentService - Hiring Method

Implement the hiring workflow that integrates with EmployeeService.

### Sub-tasks:
- [x] 6.1 Implement hireApplicant() method skeleton with transaction handling
- [x] 6.2 Add hiring eligibility validation (all 4 stages complete)
- [x] 6.3 Add validation for all required evaluation fields
- [x] 6.4 Add validation for all stages marked as Pass
- [x] 6.5 Add validation for final score >= minimum passing score
- [x] 6.6 Add validation for job posting has available openings
- [x] 6.7 Prepare employee data mapping from applicant fields
- [x] 6.8 Call EmployeeService.createEmployee() with applicant data
- [x] 6.9 Update applicant status to 'Hired' and link employee_id
- [x] 6.10 Decrement job posting num_openings
- [x] 6.11 Auto-close job posting if num_openings reaches zero
- [x] 6.12 Add error handling with transaction rollback
- [x] 6.13 Return success response with employee and applicant data

## Task 7: Implement RecruitmentController - Job Posting Endpoints

Create API endpoints for job posting management.

### Sub-tasks:
- [x] 7.1 Create `src/Controllers/RecruitmentController.php` extending base Controller
- [x] 7.2 Add constructor with RecruitmentService dependency injection
- [x] 7.3 Implement listJobs() - GET /api/recruitment/jobs
- [x] 7.4 Implement getJob() - GET /api/recruitment/jobs/{id}
- [x] 7.5 Implement createJob() - POST /api/recruitment/jobs
- [x] 7.6 Implement updateJob() - PUT /api/recruitment/jobs/{id}
- [x] 7.7 Add admin role requirement checks for all endpoints
- [x] 7.8 Add activity logging for all operations

## Task 8: Implement RecruitmentController - Applicant Endpoints

Create API endpoints for applicant management.

### Sub-tasks:
- [x] 8.1 Implement listApplicants() - GET /api/recruitment/applicants
- [x] 8.2 Implement getApplicant() - GET /api/recruitment/applicants/{id}
- [x] 8.3 Implement createApplicant() - POST /api/recruitment/applicants
- [x] 8.4 Implement updateApplicant() - PUT /api/recruitment/applicants/{id}
- [x] 8.5 Add query parameter handling for filtering
- [x] 8.6 Add activity logging for all operations

## Task 9: Implement RecruitmentController - Evaluation and Hiring Endpoints

Create API endpoints for evaluations and hiring.

### Sub-tasks:
- [x] 9.1 Implement getEvaluations() - GET /api/recruitment/applicants/{id}/evaluations
- [x] 9.2 Implement saveEvaluation() - POST /api/recruitment/evaluations
- [x] 9.3 Implement hireApplicant() - POST /api/recruitment/applicants/{id}/hire
- [x] 9.4 Add proper error handling for hiring failures
- [x] 9.5 Add activity logging for hiring operations

## Task 10: Implement RecruitmentController - View Endpoint

Create the view endpoint for the recruitment interface.

### Sub-tasks:
- [x] 10.1 Implement indexView() - GET /recruitment
- [x] 10.2 Add authentication check (admin only)
- [x] 10.3 Render recruitment/index.php view

## Task 11: Add Recruitment Routes

Register all recruitment routes in the routing configuration.

### Sub-tasks:
- [x] 11.1 Add view route: GET /recruitment
- [x] 11.2 Add job posting routes (GET, POST, PUT for /api/recruitment/jobs)
- [x] 11.3 Add applicant routes (GET, POST, PUT for /api/recruitment/applicants)
- [x] 11.4 Add evaluation routes (GET, POST for /api/recruitment/evaluations)
- [x] 11.5 Add hiring route (POST /api/recruitment/applicants/{id}/hire)
- [x] 11.6 Test all routes are properly registered

## Task 12: Create Recruitment UI - Main Page Structure

Build the main recruitment interface page.

### Sub-tasks:
- [x] 12.1 Create `src/Views/recruitment/index.php` with HTML structure
- [x] 12.2 Add page header with "Recruitment" title and "New Posting" button
- [x] 12.3 Add tab navigation (Job Postings, Applicants, Pipeline View)
- [x] 12.4 Add sidebar navigation with $currentPage = 'recruitment'
- [x] 12.5 Include Tailwind CSS and custom CSS
- [x] 12.6 Include config.js and auth.js scripts
- [x] 12.7 Add loading, success, and confirmation modal templates

## Task 13: Create Recruitment UI - Job Postings Tab

Build the job postings management interface.

### Sub-tasks:
- [x] 13.1 Add job postings table with columns (Job Title, Department, Position, Openings, Status, Actions)
- [x] 13.2 Add filter dropdowns (Status, Department)
- [x] 13.3 Create job posting modal with form fields
- [x] 13.4 Add JavaScript to load and display job postings
- [x] 13.5 Add JavaScript for creating new job posting
- [x] 13.6 Add JavaScript for editing job posting
- [x] 13.7 Add JavaScript for filtering job postings
- [x] 13.8 Add JavaScript for viewing applicants for a job posting

## Task 14: Create Recruitment UI - Applicants Tab

Build the applicants management interface.

### Sub-tasks:
- [x] 14.1 Add applicants table with columns (Name, Email, Position, Job Posting, Status, Final Score, Actions)
- [x] 14.2 Add filter dropdowns (Job Posting, Status)
- [x] 14.3 Create applicant modal with form fields
- [x] 14.4 Add JavaScript to load and display applicants
- [x] 14.5 Add JavaScript for creating new applicant
- [x] 14.6 Add JavaScript for editing applicant
- [x] 14.7 Add JavaScript for filtering applicants
- [x] 14.8 Populate job posting dropdown from API

## Task 15: Create Recruitment UI - Applicant Detail View

Build the applicant detail modal with evaluation stages.

### Sub-tasks:
- [x] 15.1 Create applicant detail modal template
- [x] 15.2 Display applicant personal information
- [x] 15.3 Display all 4 evaluation stages with scores
- [x] 15.4 Display final score (average of all stages)
- [x] 15.5 Display hiring eligibility status
- [x] 15.6 Add "View/Edit" buttons for each evaluation stage
- [x] 15.7 Add "Hire Applicant" button (enabled/disabled based on eligibility)
- [x] 15.8 Add JavaScript to load applicant details with evaluations

## Task 16: Create Recruitment UI - Evaluation Modal

Build the evaluation scoring interface.

### Sub-tasks:
- [x] 16.1 Create evaluation modal template
- [x] 16.2 Add form fields (Stage, Score, Interviewer Name, Date, Pass/Fail, Notes)
- [x] 16.3 Make stage name read-only (passed as parameter)
- [x] 16.4 Add score validation (0-100)
- [x] 16.5 Add date validation (not in future)
- [x] 16.6 Add JavaScript to save evaluation
- [x] 16.7 Recalculate final score after saving
- [x] 16.8 Update applicant detail view after saving

## Task 17: Create Recruitment UI - Hire Confirmation Modal

Build the hiring confirmation and success flow.

### Sub-tasks:
- [x] 17.1 Create hire confirmation modal template
- [x] 17.2 Display applicant name, position, and final score
- [x] 17.3 Display list of actions that will be performed
- [x] 17.4 Add warning about action being irreversible
- [x] 17.5 Add JavaScript to validate hiring eligibility before showing modal
- [x] 17.6 Add JavaScript to call hire API endpoint
- [x] 17.7 Create success modal template with temporary password display
- [x] 17.8 Add copy-to-clipboard functionality for password
- [x] 17.9 Display employee ID and password message
- [x] 17.10 Handle hiring errors with error modal

## Task 18: Create Recruitment UI - Pipeline View (Optional)

Build the Kanban-style pipeline view.

### Sub-tasks:
- [ ]* 18.1 Create pipeline view tab content
- [ ]* 18.2 Add columns for each stage (Applied, Screening, Interview 1, Interview 2, Final Interview, Hired)
- [ ]* 18.3 Create applicant card component
- [ ]* 18.4 Add drag-and-drop functionality
- [ ]* 18.5 Update applicant status on drop
- [ ]* 18.6 Add click handler to open applicant detail view

## Task 19: Update Admin Sidebar Navigation

Add the Recruitment navigation item to the admin sidebar.

### Sub-tasks:
- [x] 19.1 Open `src/Views/layouts/admin_sidebar.php`
- [x] 19.2 Add Recruitment navigation item with icon
- [x] 19.3 Position it between "Leave Requests" and "Manage Salaries"
- [x] 19.4 Add active state highlighting for $currentPage === 'recruitment'
- [x] 19.5 Test navigation from all admin pages

## Task 20: Testing and Validation

Test the complete recruitment workflow end-to-end.

### Sub-tasks:
- [ ] 20.1 Test creating job postings with valid and invalid data
- [ ] 20.2 Test updating job postings
- [ ] 20.3 Test filtering job postings by status and department
- [ ] 20.4 Test creating applicants with valid and invalid data
- [ ] 20.5 Test updating applicants
- [ ] 20.6 Test filtering applicants by job posting and status
- [ ] 20.7 Test creating evaluations for all 4 stages
- [ ] 20.8 Test final score calculation
- [ ] 20.9 Test hiring eligibility validation (missing stages, failing scores)
- [ ] 20.10 Test successful hiring workflow (employee creation, status update, openings decrement)
- [ ] 20.11 Test auto-closing job posting when openings reach zero
- [ ] 20.12 Test error handling for hiring failures
- [ ] 20.13 Test all modals open and close correctly
- [ ] 20.14 Test UI consistency with existing HRIS pages
- [ ] 20.15 Test admin-only access to all recruitment features

## Task 21: Documentation and Migration Instructions

Create documentation for the recruitment module.

### Sub-tasks:
- [ ] 21.1 Create README for recruitment module usage
- [ ] 21.2 Document the hiring workflow
- [ ] 21.3 Document evaluation scoring system
- [ ] 21.4 Create migration instructions for Supabase
- [ ] 21.5 Document API endpoints with examples
