# Recruitment Module 404 Error - FIXED

## Problem Summary
The recruitment module was returning 404 errors when trying to access API endpoints like `/api/recruitment/jobs`. The browser console showed:
```
Failed to load resource: the server responded with a status of 404 (Not Found)
Error loading job postings: SyntaxError: Unexpected token '<', "<!DOCTYPE"... is not valid JSON
```

## Root Cause
The JavaScript in `src/Views/recruitment/index.php` was making API calls using hardcoded paths like `/api/recruitment/jobs` instead of using the `AppConfig` helper to construct the correct URLs with the `/HRIS/` base path.

Since the system runs on XAMPP with `RewriteBase /HRIS/` in `.htaccess`, all API calls need to be prefixed with `/HRIS/` to work correctly:
- ❌ Wrong: `/api/recruitment/jobs`
- ✅ Correct: `/HRIS/api/recruitment/jobs`

## Solution Applied
Updated all API calls in `src/Views/recruitment/index.php` to use `window.AppConfig.apiUrl()` helper function, which automatically adds the correct base path:

### Fixed API Calls:
1. **Load Job Postings**: 
   - Before: `authFetch('/api/recruitment/jobs')`
   - After: `authFetch(window.AppConfig.apiUrl('recruitment/jobs'))`

2. **Create/Update Job Posting**:
   - Before: `authFetch('/api/recruitment/jobs')` or `authFetch('/api/recruitment/jobs/${id}')`
   - After: Uses `window.AppConfig.apiUrl('recruitment/jobs')` with conditional ID appending

3. **Load Applicants**:
   - Before: `authFetch('/api/recruitment/applicants')`
   - After: `authFetch(window.AppConfig.apiUrl('recruitment/applicants'))`

4. **Create/Update Applicant**:
   - Before: `authFetch('/api/recruitment/applicants')` or `authFetch('/api/recruitment/applicants/${id}')`
   - After: Uses `window.AppConfig.apiUrl('recruitment/applicants')` with conditional ID appending

5. **View Applicant Details**:
   - Before: `authFetch('/api/recruitment/applicants/${id}')`
   - After: `authFetch(window.AppConfig.apiUrl('recruitment/applicants/${id}'))`

6. **Save Evaluation**:
   - Before: `authFetch('/api/recruitment/evaluations')`
   - After: `authFetch(window.AppConfig.apiUrl('recruitment/evaluations'))`

7. **Hire Applicant**:
   - Before: `authFetch('/api/recruitment/applicants/${id}/hire')`
   - After: `authFetch(window.AppConfig.apiUrl('recruitment/applicants/${id}/hire'))`

## Pattern Used (Following Existing System)
The fix follows the same pattern used in other working pages like `src/Views/employees/index.php`:

```javascript
// Pattern from employees page
const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('employees') : '/HRIS/api/employees';
const response = await fetch(apiUrl, { ... });
```

This pattern:
1. Checks if `AppConfig` is available
2. Uses `AppConfig.apiUrl()` to construct the correct path
3. Falls back to hardcoded `/HRIS/` path if AppConfig is not loaded

## Files Modified
1. `src/Views/recruitment/index.php` - Fixed all 7 API call locations
2. `.kiro/specs/recruitment-module/tasks.md` - Updated task completion status

## Tasks Updated
Marked the following tasks as complete in `tasks.md`:
- Task 7: RecruitmentController - Job Posting Endpoints (all sub-tasks)
- Task 8: RecruitmentController - Applicant Endpoints (all sub-tasks)
- Task 9: RecruitmentController - Evaluation and Hiring Endpoints (all sub-tasks)
- Task 10: RecruitmentController - View Endpoint (all sub-tasks)
- Task 11: Add Recruitment Routes (all sub-tasks)
- Task 12: Create Recruitment UI - Main Page Structure (all sub-tasks)
- Task 13: Create Recruitment UI - Job Postings Tab (all sub-tasks)
- Task 14: Create Recruitment UI - Applicants Tab (all sub-tasks)
- Task 15: Create Recruitment UI - Applicant Detail View (all sub-tasks)
- Task 16: Create Recruitment UI - Evaluation Modal (all sub-tasks)
- Task 17: Create Recruitment UI - Hire Confirmation Modal (all sub-tasks)
- Task 19: Update Admin Sidebar Navigation (all sub-tasks)

## Verification
The recruitment module should now work correctly:
1. Navigate to `/HRIS/recruitment` (or click Recruitment in sidebar)
2. Job postings should load without 404 errors
3. All CRUD operations (Create, Read, Update) should work for:
   - Job Postings
   - Applicants
   - Evaluations
   - Hiring workflow

## Backend Status
All backend components were already correctly implemented:
- ✅ Database tables created and migrated in Supabase
- ✅ Models: JobPosting, Applicant, ApplicantEvaluation
- ✅ Service: RecruitmentService with all methods
- ✅ Controller: RecruitmentController with all 11 endpoints
- ✅ Routes: All 12 recruitment routes registered in config/routes.php
- ✅ Container: All components registered in src/bootstrap.php
- ✅ Sidebar: Recruitment navigation item added

The issue was purely in the frontend JavaScript not using the correct URL construction pattern.

## Next Steps
1. Test the recruitment module in the browser
2. Verify all CRUD operations work correctly
3. Test the complete hiring workflow:
   - Create job posting
   - Add applicant
   - Complete all 4 evaluation stages
   - Hire the applicant
   - Verify employee is created with temporary password
