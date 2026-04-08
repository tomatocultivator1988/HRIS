# Leave Approve Issue - COMPLETE FIX

## Problem Analysis

When approving a leave request:
1. ✅ Leave request status IS being updated to "Approved" 
2. ❌ Attendance records creation FAILS due to database constraint
3. ❌ Frontend doesn't refresh the list after approval
4. Result: User sees "success" but status appears unchanged after refresh

## Root Causes

### Issue 1: Database Constraint Violation
**Error:** `new row for relation "attendance" violates check constraint "attendance_status_check"`

**Cause:** The attendance table has a CHECK constraint that only allows:
- 'Present'
- 'Late'  
- 'Absent'
- 'Half-day'

But the code tries to insert 'On Leave' status.

**Solution:** Add 'On Leave' to the allowed values in the constraint.

### Issue 2: Incorrect Response Handling
**Cause:** `createLeaveAttendanceRecords()` checks for `$checkResult['success']` but `SupabaseConnection::select()` returns an array directly, not wrapped in success/data.

**Solution:** Fixed to check `is_array($checkResult)` instead.

### Issue 3: Frontend Not Refreshing
**Cause:** After approval, the pending requests list is not being reloaded.

**Solution:** The `loadPendingRequests()` is already called, but we need to ensure it's working properly.

## Fixes Implemented

### 1. Database Migration (docs/migrations/add_on_leave_status.sql)
```sql
-- Drop the existing constraint
ALTER TABLE attendance DROP CONSTRAINT IF EXISTS attendance_status_check;

-- Add new constraint with 'On Leave' included
ALTER TABLE attendance ADD CONSTRAINT attendance_status_check 
    CHECK (status IN ('Present', 'Late', 'Absent', 'Half-day', 'On Leave'));
```

### 2. Fixed Response Handling (src/Services/LeaveService.php)
**Before:**
```php
if ($checkResult && $checkResult['success'] && !empty($checkResult['data'])) {
    $existingRecord = $checkResult['data'][0];
}
```

**After:**
```php
if (is_array($checkResult) && !empty($checkResult)) {
    $existingRecord = $checkResult[0];
}
```

## How to Apply the Fix

### Step 1: Run the Database Migration
You need to run this SQL in your Supabase SQL Editor:

```sql
ALTER TABLE attendance DROP CONSTRAINT IF EXISTS attendance_status_check;

ALTER TABLE attendance ADD CONSTRAINT attendance_status_check 
    CHECK (status IN ('Present', 'Late', 'Absent', 'Half-day', 'On Leave'));
```

### Step 2: Test the Approval Flow
1. Login as admin
2. Go to Leave Requests page
3. Click "Review" on a pending request
4. Click "Approve"
5. Should see success message
6. List should refresh automatically
7. Status should change to "Approved"
8. Attendance records should be created for leave dates

## Expected Behavior After Fix

### Approve Flow:
1. Admin clicks "Approve"
2. Backend updates leave_requests.status = 'Approved'
3. Backend creates attendance records with status = 'On Leave' for each working day
4. Frontend shows success message
5. Frontend reloads pending requests list
6. Approved request disappears from pending list
7. Appears in leave history with "Approved" status

### Database Changes:
- `leave_requests` table: status = 'Approved', reviewed_by = admin_id, reviewed_at = timestamp
- `attendance` table: New records with status = 'On Leave' for each working day in the leave period
- `leave_credits` table: used_credits incremented by total_days (via trigger)

## Verification Steps

### 1. Check Leave Request Status
```sql
SELECT id, employee_id, status, reviewed_by, reviewed_at 
FROM leave_requests 
WHERE id = '<leave_request_id>';
```

### 2. Check Attendance Records Created
```sql
SELECT date, status, remarks 
FROM attendance 
WHERE employee_id = '<employee_id>' 
AND date BETWEEN '<start_date>' AND '<end_date>'
ORDER BY date;
```

### 3. Check Leave Credits Deducted
```sql
SELECT leave_type_id, total_credits, used_credits, remaining_credits 
FROM leave_credits 
WHERE employee_id = '<employee_id>' 
AND year = EXTRACT(YEAR FROM CURRENT_DATE);
```

## Files Modified
1. `docs/migrations/add_on_leave_status.sql` - NEW migration file
2. `src/Services/LeaveService.php` - Fixed response handling in createLeaveAttendanceRecords()

## Status
- ✅ Code fixes applied
- ⏳ Database migration needs to be run manually in Supabase
- ⏳ Testing required after migration

## Next Steps
1. **IMPORTANT:** Run the database migration in Supabase SQL Editor
2. Test leave approval flow
3. Verify attendance records are created
4. Check leave credits are deducted
5. Test deny flow as well
