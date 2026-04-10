# Leave Credits System Analysis

## Problem Statement
Leave credits are NOT being deducted when leave requests are approved. Employee `kiancabalumcabalum@gmail.com` has many approved leaves but leave credits balance remains unchanged.

## Current Implementation Analysis

### Database Schema (docs/database-schema.sql)
✅ **leave_credits table EXISTS** with proper structure:
- `total_credits`: Total credits allocated
- `used_credits`: Credits used (should increment on approval)
- `remaining_credits`: Computed column (total - used)
- `year`: Year for the credits
- Unique constraint on (employee_id, leave_type_id, year)

✅ **Database trigger EXISTS**: `trigger_update_leave_credits`
- Triggers on UPDATE of `leave_requests` table
- Should automatically deduct credits when status changes to 'Approved'
- Should restore credits if status changes from 'Approved' to something else
- Logs to `leave_credit_audit` table

✅ **Auto-initialization trigger EXISTS**: `trigger_initialize_leave_credits`
- Automatically creates leave_credits records for new employees
- Initializes credits based on leave_types.days_allowed

### PHP Implementation (src/Services/LeaveService.php)

❌ **MISSING**: `getLeaveCredits()` method
- Controller calls `$this->leaveService->getLeaveCredits($employeeId)` (line 413)
- Method does NOT exist in LeaveService.php
- This will cause a fatal error when accessing /api/leave/credits endpoint

⚠️ **INCOMPLETE**: `getLeaveBalance()` method (line 680)
- Returns hardcoded default values
- Has TODO comment: "TODO: Implement leave credits system"
- Does NOT read from leave_credits table
- Returns array format instead of reading actual database records

✅ **GOOD**: `approveLeaveRequest()` method
- Updates leave_request status to 'Approved'
- Database trigger should handle credit deduction automatically
- Creates attendance records for approved leave dates

❌ **MISSING**: Validation in `submitLeaveRequest()`
- Does NOT check if employee has enough leave credits before filing
- Should validate: remaining_credits >= total_days requested
- Should prevent filing leave if insufficient credits

## Root Cause Analysis

### Possible Issues:

1. **Trigger Not Created in Supabase**
   - The schema file exists but trigger may not be executed in Supabase
   - Need to verify trigger exists in database

2. **Leave Credits Records Don't Exist**
   - Employee may not have leave_credits records initialized
   - Auto-initialization trigger may not have run for existing employees
   - Only works for NEW employees inserted AFTER trigger creation

3. **Leave Credit Audit Table Missing**
   - Trigger tries to INSERT into leave_credit_audit
   - If table doesn't exist, trigger will FAIL SILENTLY
   - This would prevent credit deduction

4. **Year Mismatch**
   - Trigger uses: `EXTRACT(YEAR FROM NEW.start_date)`
   - If leave_credits record doesn't exist for that year, UPDATE affects 0 rows
   - No error thrown, credits not deducted

## Required Actions

### 1. Verify Database State
Run `check_leave_system_status.sql` in Supabase to check:
- Does trigger exist?
- Do leave_credits records exist for the employee?
- Do approved leave requests exist?
- Does leave_credit_audit table exist?

### 2. Fix Missing leave_credits Records
If records don't exist, need to:
- Create migration to initialize leave_credits for ALL existing employees
- Not just new employees

### 3. Implement getLeaveCredits() Method
Add method to LeaveService.php to:
- Query leave_credits table
- Join with leave_types to get type names
- Return actual database records

### 4. Fix getLeaveBalance() Method
Update to:
- Read from leave_credits table instead of hardcoded values
- Return actual employee credits

### 5. Add Validation to submitLeaveRequest()
Before creating leave request:
- Query leave_credits for the employee and leave type
- Check if remaining_credits >= total_days
- Throw ValidationException if insufficient credits

### 6. Ensure Trigger is Created
- Verify trigger exists in Supabase
- If not, execute the CREATE TRIGGER statement
- Ensure leave_credit_audit table exists

## Implementation Plan

1. **Investigate** (Run SQL checks)
2. **Fix Database** (Create missing records/triggers)
3. **Implement getLeaveCredits()** (New method)
4. **Fix getLeaveBalance()** (Update existing method)
5. **Add Validation** (Update submitLeaveRequest)
6. **Test Thoroughly** (Ensure no breaking changes)

## Testing Checklist

- [ ] Verify trigger exists and is active
- [ ] Verify leave_credits records exist for test employee
- [ ] Submit new leave request with insufficient credits (should fail)
- [ ] Submit new leave request with sufficient credits (should succeed)
- [ ] Approve leave request (should deduct credits)
- [ ] Verify used_credits incremented in database
- [ ] Verify remaining_credits decreased
- [ ] Check leave_credit_audit log entry created
- [ ] Deny previously approved leave (should restore credits)
- [ ] Verify existing features still work (no breaking changes)
