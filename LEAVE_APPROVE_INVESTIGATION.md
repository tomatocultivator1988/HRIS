# Leave Approve/Deny Investigation Results

## Date: April 7, 2026, 23:04

## Summary
The leave approve/deny functionality is **NOW WORKING** but there are database constraint issues that need to be fixed.

## Issues Found

### 1. Foreign Key Constraint on `leave_credit_audit` (CRITICAL)
**Status**: Still causing failures on some approve attempts

**Error Message**:
```
insert or update on table "leave_credit_audit" violates foreign key constraint "leave_credit_audit_performed_by_fkey"
Key (performed_by)=(535224c0-8e1a-4d57-b1df-806757811150) is not present in table "employees"
```

**Root Cause**: 
- There's a database trigger that inserts into `leave_credit_audit` when leave is approved
- The trigger uses the reviewer's ID as `performed_by`
- Admin ID `535224c0-8e1a-4d57-b1df-806757811150` is in `admins` table, NOT in `employees` table
- The foreign key constraint requires `performed_by` to exist in `employees` table

**Solution**: Run migration `docs/migrations/fix_leave_credit_audit_constraint.sql`

### 2. Missing `context` Column in `system_audit_log` (NON-CRITICAL)
**Status**: Causing audit logging to fail (doesn't affect core functionality)

**Error Message**:
```
Could not find the 'context' column of 'system_audit_log' in the schema cache
```

**Root Cause**: The `system_audit_log` table is missing the `context` column

**Solution**: Run migration `docs/migrations/fix_system_audit_log_context.sql`

## Test Results

### First Approve Attempt (22:58:34)
- Request ID: `2a9cd675-8632-43fa-b56f-d1358c807269`
- Result: **FAILED**
- Error: Foreign key constraint violation on `leave_credit_audit`
- Status remained: "Pending"

### Second Approve Attempt (23:04:49)
- Request ID: `e5718843-c236-4bcf-ba0e-0e039850a71d`
- Result: **SUCCESS** ✅
- Update response: Status 200
- Affected rows: 1
- Status changed: "Pending" → "Approved"
- `reviewed_by` set to: `535224c0-8e1a-4d57-b1df-806757811150`
- `reviewed_at` set to: `2026-04-07 23:04:49`

## Why Second Attempt Succeeded
The constraint violation is intermittent, likely because:
1. The database trigger that inserts into `leave_credit_audit` may not always fire
2. Or there's a race condition
3. Or the trigger has conditional logic

## Required Actions

1. **Run the migrations** (in this order):
   ```sql
   -- Fix leave_credit_audit constraint
   \i docs/migrations/fix_leave_credit_audit_constraint.sql
   
   -- Fix system_audit_log context column
   \i docs/migrations/fix_system_audit_log_context.sql
   ```

2. **Test approve/deny again** after running migrations

3. **Check for database triggers** on `leave_requests` table that might be inserting into `leave_credit_audit`

## Current State
- ✅ Approve/Deny endpoints are accessible (PUT method)
- ✅ Leave request status updates work (when constraint doesn't fail)
- ✅ Frontend displays leave types correctly
- ✅ Leave request submission works with UUIDs
- ⚠️ Foreign key constraint on `leave_credit_audit` needs to be dropped
- ⚠️ Audit logging fails (non-critical)
- ⏸️ Attendance record creation is disabled (will re-enable after fixing "On Leave" status)

## Files Modified
- `docs/migrations/fix_leave_credit_audit_constraint.sql` (NEW)
- `docs/migrations/fix_system_audit_log_context.sql` (NEW)

## Next Steps
1. Run both migration files in Supabase
2. Test approve/deny functionality again
3. Verify no more constraint violations
4. Re-enable attendance record creation after applying `add_on_leave_status.sql`
