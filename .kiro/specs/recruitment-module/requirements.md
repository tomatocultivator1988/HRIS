# Requirements Document: Recruitment Module

## Introduction

The Recruitment Module extends the existing HRIS system to support structured hiring workflows. It enables administrators to manage job postings, track applicants through multiple evaluation stages with scoring, and seamlessly convert qualified applicants into employees using the existing employee creation infrastructure. The module enforces quality standards through mandatory scoring at each stage and validates readiness before hiring.

## Glossary

- **Recruitment_Module**: The system component that manages job postings, applicants, and hiring workflows
- **Job_Posting**: A record representing an open position with details like title, department, number of openings, and status
- **Applicant**: A candidate record containing personal and professional information matching employee fields
- **Evaluation_Stage**: A phase in the hiring process (Screening, Interview 1, Interview 2, Final Interview) with scoring capability
- **Stage_Score**: A numeric evaluation (0-100) assigned to an applicant at a specific evaluation stage
- **Final_Score**: The average of all stage scores for an applicant
- **Minimum_Passing_Score**: The threshold score (e.g., 70%) required for an applicant to be eligible for hiring
- **Hire_Action**: The process of converting an applicant into an employee by calling the existing createEmployee() function
- **Employee_Service**: The existing service class containing the createEmployee() method (lines 200-350 in EmployeeService.php)
- **Supabase_Auth_User**: An authentication user record created automatically during employee creation
- **Leave_Credits**: Initial leave balances automatically assigned to new employees
- **Admin**: A user with administrative privileges who can manage recruitment and employees

## Requirements

### Requirement 1: Job Posting Management

**User Story:** As an Admin, I want to create and manage job postings for open positions, so that I can organize applicants by role and track hiring needs.

#### Acceptance Criteria

1. WHEN an Admin creates a job posting, THE Recruitment_Module SHALL store the job title, department, position, number of openings, description, and status
2. THE Recruitment_Module SHALL validate that job title, department, position, and number of openings are provided
3. THE Recruitment_Module SHALL validate that number of openings is a positive integer
4. WHEN an Admin views job postings, THE Recruitment_Module SHALL display all postings with their current status (Open, Closed, On Hold)
5. WHEN an Admin updates a job posting, THE Recruitment_Module SHALL preserve the posting ID and update only the specified fields
6. WHEN an Admin closes a job posting, THE Recruitment_Module SHALL update the status to Closed
7. THE Recruitment_Module SHALL allow filtering job postings by status and department

### Requirement 2: Applicant Information Management

**User Story:** As an Admin, I want to add applicants with information matching employee fields, so that I can seamlessly convert them to employees when hired.

#### Acceptance Criteria

1. WHEN an Admin creates an applicant record, THE Recruitment_Module SHALL store first_name, last_name, work_email, mobile_number, department, position, and employment_status
2. THE Recruitment_Module SHALL validate that first_name, last_name, work_email, department, position, and employment_status are provided
3. THE Recruitment_Module SHALL validate that work_email follows a valid email format
4. THE Recruitment_Module SHALL validate that employment_status is one of: Regular, Probationary, Contractual, Part-time
5. THE Recruitment_Module SHALL validate that work_email is unique across all applicants
6. WHEN an Admin links an applicant to a job posting, THE Recruitment_Module SHALL store the job_posting_id with the applicant record
7. THE Recruitment_Module SHALL allow viewing all applicants for a specific job posting
8. THE Recruitment_Module SHALL allow filtering applicants by status (Applied, In Progress, Passed, Failed, Hired)

### Requirement 3: Evaluation Stage Scoring System

**User Story:** As an Admin, I want to score applicants at each evaluation stage, so that I can objectively assess candidates and track their progress.

#### Acceptance Criteria

1. THE Recruitment_Module SHALL support four evaluation stages: Screening, Interview 1, Interview 2, Final Interview
2. WHEN an Admin records a stage evaluation, THE Recruitment_Module SHALL store the stage name, score (0-100), notes, interviewer name, evaluation date, and pass/fail status
3. THE Recruitment_Module SHALL validate that score is a number between 0 and 100 inclusive
4. THE Recruitment_Module SHALL validate that stage name is one of: Screening, Interview 1, Interview 2, Final Interview
5. THE Recruitment_Module SHALL validate that evaluation date is not in the future
6. WHEN an Admin views an applicant's evaluations, THE Recruitment_Module SHALL display all stages with their scores, notes, interviewer, date, and pass/fail status
7. WHEN an applicant has multiple stage evaluations, THE Recruitment_Module SHALL calculate the Final_Score as the average of all stage scores
8. THE Recruitment_Module SHALL display the Final_Score alongside individual stage scores
9. WHEN an Admin updates a stage evaluation, THE Recruitment_Module SHALL recalculate the Final_Score
10. THE Recruitment_Module SHALL allow an Admin to mark a stage as Pass or Fail independently of the score

### Requirement 4: Hiring Eligibility Validation

**User Story:** As an Admin, I want the system to validate hiring eligibility before allowing me to hire an applicant, so that I only hire candidates who meet all requirements.

#### Acceptance Criteria

1. WHEN an Admin attempts to hire an applicant, THE Recruitment_Module SHALL validate that all four evaluation stages have been completed
2. WHEN an Admin attempts to hire an applicant, THE Recruitment_Module SHALL validate that each stage has a score, notes, interviewer, date, and pass/fail status
3. WHEN an Admin attempts to hire an applicant, THE Recruitment_Module SHALL validate that the Final_Score meets or exceeds the Minimum_Passing_Score
4. WHEN an Admin attempts to hire an applicant, THE Recruitment_Module SHALL validate that all stages are marked as Pass
5. IF any validation fails, THEN THE Recruitment_Module SHALL display an error modal with specific missing or failing requirements
6. WHEN all validations pass, THE Recruitment_Module SHALL enable the Hire button
7. WHEN validations fail, THE Recruitment_Module SHALL disable the Hire button

### Requirement 5: Employee Creation Integration

**User Story:** As an Admin, I want to hire an applicant by clicking a Hire button that creates an employee record, so that I can seamlessly onboard qualified candidates.

#### Acceptance Criteria

1. WHEN an Admin clicks the Hire button for a validated applicant, THE Recruitment_Module SHALL call the Employee_Service createEmployee() method
2. THE Recruitment_Module SHALL pass applicant data to createEmployee() with fields: first_name, last_name, work_email (as email), mobile_number (as phone), department, position, employment_status, and date_hired (set to current date)
3. WHEN createEmployee() executes, THE Employee_Service SHALL create a Supabase_Auth_User with an auto-generated password
4. WHEN createEmployee() executes, THE Employee_Service SHALL create an Employee record linked to the Supabase_Auth_User
5. WHEN createEmployee() executes, THE Employee_Service SHALL set force_password_change to true
6. WHEN createEmployee() executes, THE Employee_Service SHALL initialize Leave_Credits for the new employee
7. WHEN createEmployee() succeeds, THE Recruitment_Module SHALL update the applicant status to Hired
8. WHEN createEmployee() succeeds, THE Recruitment_Module SHALL store the employee_id in the applicant record
9. WHEN createEmployee() succeeds, THE Recruitment_Module SHALL decrement the job posting's number of openings by 1
10. IF createEmployee() fails, THEN THE Recruitment_Module SHALL display an error modal with the failure reason
11. IF createEmployee() fails, THEN THE Recruitment_Module SHALL NOT update the applicant status or job posting openings

### Requirement 6: Post-Hire Job Posting Management

**User Story:** As an Admin, I want job postings to automatically close when all openings are filled, so that I don't accidentally over-hire.

#### Acceptance Criteria

1. WHEN a job posting's number of openings reaches zero, THE Recruitment_Module SHALL automatically update the job posting status to Closed
2. WHEN an Admin views a closed job posting, THE Recruitment_Module SHALL display the status as Closed
3. THE Recruitment_Module SHALL prevent hiring additional applicants for a job posting with zero openings
4. WHEN an Admin manually reopens a closed job posting, THE Recruitment_Module SHALL allow updating the number of openings and status

### Requirement 7: User Interface Consistency

**User Story:** As an Admin, I want the recruitment interface to match the existing HRIS design patterns, so that I have a consistent user experience.

#### Acceptance Criteria

1. THE Recruitment_Module SHALL use modal dialogs for all confirmations and errors
2. THE Recruitment_Module SHALL use Tailwind CSS classes consistent with existing views (e.g., employees/index.php)
3. THE Recruitment_Module SHALL use AuthManager.authFetch() for all API calls
4. THE Recruitment_Module SHALL display loading states during API operations without full-screen overlays
5. THE Recruitment_Module SHALL include a sidebar navigation item for Recruitment
6. THE Recruitment_Module SHALL follow the existing table layout patterns for listing job postings and applicants
7. THE Recruitment_Module SHALL use the same color scheme and button styles as the employee management interface

### Requirement 8: Data Persistence and Integrity

**User Story:** As a system administrator, I want recruitment data to be stored reliably in Supabase, so that no information is lost during the hiring process.

#### Acceptance Criteria

1. THE Recruitment_Module SHALL store all job postings in a job_postings table in Supabase
2. THE Recruitment_Module SHALL store all applicants in an applicants table in Supabase
3. THE Recruitment_Module SHALL store all evaluation stages in an applicant_evaluations table in Supabase
4. THE Recruitment_Module SHALL use UUID primary keys for all recruitment tables
5. THE Recruitment_Module SHALL use foreign key constraints to link applicants to job postings
6. THE Recruitment_Module SHALL use foreign key constraints to link evaluations to applicants
7. WHEN an applicant is hired, THE Recruitment_Module SHALL use a foreign key to link the applicant record to the employee record
8. THE Recruitment_Module SHALL use timestamps (created_at, updated_at) for all recruitment tables
9. THE Recruitment_Module SHALL prevent deletion of job postings that have associated applicants
10. THE Recruitment_Module SHALL allow soft deletion of applicants by setting is_active to false

### Requirement 9: Recruitment Service Layer

**User Story:** As a developer, I want a RecruitmentService class that handles business logic, so that the system follows the existing service layer pattern.

#### Acceptance Criteria

1. THE Recruitment_Module SHALL implement a RecruitmentService class in src/Services/RecruitmentService.php
2. THE RecruitmentService SHALL provide methods for creating, updating, and retrieving job postings
3. THE RecruitmentService SHALL provide methods for creating, updating, and retrieving applicants
4. THE RecruitmentService SHALL provide methods for creating, updating, and retrieving evaluation stages
5. THE RecruitmentService SHALL provide a hireApplicant() method that validates eligibility and calls Employee_Service createEmployee()
6. THE RecruitmentService SHALL validate all input data before database operations
7. THE RecruitmentService SHALL throw ValidationException for invalid data with descriptive error messages
8. THE RecruitmentService SHALL throw NotFoundException when requested records do not exist
9. THE RecruitmentService SHALL use database transactions for the hire operation to ensure atomicity

### Requirement 10: Recruitment Controller and Routing

**User Story:** As a developer, I want a RecruitmentController that handles HTTP requests, so that the system follows the existing MVC pattern.

#### Acceptance Criteria

1. THE Recruitment_Module SHALL implement a RecruitmentController class in src/Controllers/RecruitmentController.php
2. THE RecruitmentController SHALL extend the base Controller class
3. THE RecruitmentController SHALL require admin role for all recruitment operations
4. THE RecruitmentController SHALL provide API endpoints for job posting CRUD operations
5. THE RecruitmentController SHALL provide API endpoints for applicant CRUD operations
6. THE RecruitmentController SHALL provide API endpoints for evaluation stage CRUD operations
7. THE RecruitmentController SHALL provide a POST /api/recruitment/applicants/{id}/hire endpoint for hiring
8. THE RecruitmentController SHALL provide a GET /recruitment view endpoint for the recruitment interface
9. THE RecruitmentController SHALL return JSON responses for API endpoints
10. THE RecruitmentController SHALL return HTML responses for view endpoints
11. THE RecruitmentController SHALL log all recruitment activities using the logActivity() method
12. THE Recruitment_Module SHALL register all recruitment routes in config/routes.php following existing patterns
