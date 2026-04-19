# 🧹 Codebase Cleanup Plan

## Files to DELETE (Legacy/Temporary/Redundant)

### 1. Test/Debug PHP Files (20 files)
- check_employees_structure.php
- check_payroll_setup.php
- check_position_salaries.php
- clear_opcache.php
- debug_leave_data.php
- debug_namespace_error.php
- debug_router_only.php
- force_reload.php
- populate_positions.php
- remove_loading_screens.php
- run_migration.php
- test_add_employee.php
- test_api_endpoint.php
- test_auth_header.php
- test_deduction_calculator.php
- test_deductions_standalone.php
- test_leave_report.php
- test_login.php
- test_position_logic.php
- test_profile_api.php
- test_recruitment_setup.php
- test_redirect.php
- test_routes_list.php
- test_simple_route.php
- test_verify_token.php
- update_all_sidebars.php
- update_correct_deductions.php
- update_deductions_standalone.php
- update_deductions_supabase.php

### 2. Test SQL Files (10 files)
- check_all_employee_leave_credits.sql
- check_evaluations.sql
- check_interview1.sql
- check_leave_credits_detailed.sql
- check_leave_credits_issue.sql
- check_leave_system_status.sql
- delete_old_period.sql
- FIX_EMPLOYEE_DOCUMENTS.sql
- fix_evaluation_date_constraint.sql
- FIX_LEAVE_CREDITS_SYSTEM.sql
- test_insert_evaluation.sql

### 3. Test PowerShell/Batch Files (9 files)
- run_test_calculator.bat
- run_update_deductions.bat
- test_download_proper.ps1
- test_download_simple.ps1
- test_download.ps1
- test_full_flow.ps1
- test_upload_curl.ps1
- test_upload.ps1

### 4. Test HTML/Images (2 files)
- test_login_flow.html
- test_payroll_api.html
- test_yumi.jpg

### 5. Redundant/Old Documentation (50+ files)
- ADMIN_UI_FIXES.md
- ALL_22_BUGS_FIXED_VERIFIED.md
- ALL_BUGS_FIXED_FINAL.md
- ALL_FIXES_COMPLETE.md
- ATTENDANCE_MODULE_COMPLETE.md
- ATTENDANCE_MODULE_STATUS.md
- AUTHORIZATION_FIX.md
- BUG_23_SETBODY_FIX.md
- BUGS_FIXED_SUMMARY.md
- BUGS_VERIFICATION_COMPLETE.md
- COMPLETE_SESSION_SUMMARY.md
- CONFIRMATION_MODALS_COMPLETE.md
- CONFIRMATION_MODALS_IMPLEMENTATION.md
- DASHBOARD_FIXES_SUMMARY.md
- DEPLOYMENT_GUIDE.md (keep if useful, or consolidate)
- DEPLOYMENT_SUMMARY.md
- EMPLOYEE_201_FILES_IMPLEMENTATION_PLAN.md
- EMPLOYEE_201_FILES_VERIFICATION_REPORT.md
- EMPLOYEE_DASHBOARD_COMPLETE.md
- FINAL_BUGS_STATUS.md
- FINAL_FIXES.md
- FINAL_IMPLEMENTATION_SUMMARY.md
- FINAL_SUMMARY.md
- fix_recruitment_authfetch.md
- FIX_ZERO_PAYROLL.md
- FORCE_PASSWORD_CHANGE_IMPLEMENTATION.md
- FUTURE_ATTENDANCE_FIX.md
- IMPLEMENTATION_VERIFICATION.md
- LEAVE_ADMIN_SECTIONS_COMPLETE.md
- LEAVE_APPROVE_COMPLETE_FIX.md
- LEAVE_APPROVE_DENY_FIX.md
- LEAVE_APPROVE_INVESTIGATION.md
- LEAVE_CREDITS_ANALYSIS.md
- LEAVE_CREDITS_FIX_COMPLETE.md
- LEAVE_REQUEST_FIX_COMPLETE.md
- LEAVE_SYSTEM_FLOW_ANALYSIS.md
- LOADING_SCREENS_REMOVED.md
- LOADING_SKELETONS_APPLIED_ALL_PAGES.md
- LOADING_SKELETONS_COMPLETE.md
- LOADING_SKELETONS_IMPLEMENTATION.md
- LOGIN_REDIRECT_FIXES.md
- MVC_COMPLETE.md
- MVC_CORE_BUGFIXES_COMPLETE.md
- MVC_STRUCTURE.md
- OVERTIME_LOGIC_EXPLAINED.md
- PASSWORD_CHANGE_LOGIC_EXPLAINED.md
- PASSWORD_MANAGEMENT_IMPLEMENTATION.md
- PAYROLL_DEBUG_TOOLS.md
- PAYROLL_FIXED.md
- PAYROLL_QUICK_START.md
- PAYROLL_TERMS_EXPLAINED.md
- PHILIPPINE_DEDUCTIONS_2024.md
- PIPELINE_VIEW_IMPLEMENTED.md
- POSITION_SALARY_IMPLEMENTATION.md
- POSITION_SALARY_SETUP.md
- QUICK_DEPLOY.md
- QUICK_START_POSITION_SALARIES.md
- QUICK_TEST_PAYROLL.md
- RECRUITMENT_404_FIX.md
- RECRUITMENT_MODULE_COMPATIBILITY_ANALYSIS.md
- RECRUITMENT_SESSION_FIX.md
- RECRUITMENT_SIDEBAR_ADDED.md
- REMAINING_BUGS_ANALYSIS.md
- REMAINING_IMPROVEMENTS_NEEDED.md
- REPORTS_ATTENDANCE_IMPLEMENTATION.md
- REPORTS_COMPLETE_SIMPLIFIED.md
- REPORTS_DATA_FLOW_COMPLETE.md
- REPORTS_MODULE_IMPLEMENTATION.md
- RUN_SCRIPTS_WINDOWS.md
- RUN_THIS_TO_FIX_LEAVE_CREDITS.md
- SIDEBAR_CONSISTENCY_COMPLETE.md
- SIDEBAR_CONSISTENCY_FIXED.md
- SIDEBAR_PERSISTENCE_SOLUTION.md
- TEST_IMPROVEMENTS.md
- TROUBLESHOOT_400_ERROR.md
- USE_CORRECT_DEDUCTIONS.md

## Files to KEEP (Important)

### Documentation (Keep)
- README.md (main README)
- README_PORTFOLIO.md (portfolio version)
- TECH_STACK.md (tech overview)
- N+1_QUERY_FIX_SUMMARY.md (performance docs)
- PHPSTAN_SETUP_GUIDE.md (setup guide)
- SENTRY_SETUP_GUIDE.md (setup guide)
- SESSION_SUMMARY.md (session summary)
- PROGRESS_TRACKER.md (progress tracking)
- ZERO_COST_IMPROVEMENTS.md (improvement plan)

### Configuration (Keep)
- .env
- .env.example
- .gitignore
- .htaccess
- .dockerignore
- Dockerfile
- render.yaml
- phpstan.neon

### SQL (Keep - in docs/migrations)
- RUN_THIS_SQL_IN_SUPABASE.sql (main setup)

## Total Files to Delete: ~100 files
