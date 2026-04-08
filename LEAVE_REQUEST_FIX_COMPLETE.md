# Leave Request Issue - FIXED

## Problem Summary
Leave requests were showing "submitted successfully" in the frontend but were not being saved to the database. The issue was identified through Apache logs showing:
- "Leave request created: []" - Empty response from Supabase
- HTTP 400 error: "invalid input syntax for type uuid: \"2\""

## Root Cause
The `leave_type_id` field in the database is a UUID type, but the frontend was sending numeric string IDs like "1", "2", "3". Supabase rejected these inserts because they weren't valid UUIDs.

## Solution Implemented

### 1. Backend Changes (LeaveService.php)
- Added UUID mapping logic in `submitLeaveRequest()` method
- Fetches actual leave types from database using `getLeaveTypes()`
- Maps numeric IDs (1, 2, 3) to actual UUIDs from the database
- Falls back to using provided value if it's already a valid UUID format
- Fixed `getLeaveTypes()` to properly handle Supabase response format

### 2. Frontend Changes (employee.php)
- Removed hardcoded leave type options
- Added `loadLeaveTypesForModal()` function to dynamically load leave types from API
- Leave type dropdown now uses actual UUIDs from database as option values
- Added `leaveTypesMap` to store leave type names for display purposes
- Updated `displayLeaveRequests()` to use the dynamic leave types map

### 3. Debugging Enhancements (SupabaseConnection.php)
- Added extensive logging to `insert()` method to track:
  - Data being inserted
  - Response status codes
  - Raw responses from Supabase
  - Success/failure reasons

## Files Modified
1. `src/Services/LeaveService.php`
   - Enhanced `submitLeaveRequest()` with UUID mapping
   - Fixed `getLeaveTypes()` response handling
   
2. `src/Views/dashboard/employee.php`
   - Removed hardcoded leave types
   - Added dynamic leave type loading
   - Updated leave request display logic

3. `src/Core/SupabaseConnection.php`
   - Added debug logging to insert method

## How It Works Now

### Leave Request Submission Flow:
1. User selects leave type from dropdown (now contains actual UUIDs)
2. Frontend sends leave request with UUID as `leave_type_id`
3. Backend validates the UUID format
4. If numeric ID is sent (backward compatibility), maps it to UUID
5. Inserts into database with proper UUID
6. Returns created record to frontend

### Leave Type Loading Flow:
1. On dashboard load, calls `/api/leave/types` endpoint
2. Backend queries `leave_types` table from Supabase
3. Returns array of leave types with UUIDs
4. Frontend populates dropdown with UUID values
5. Stores mapping for display purposes

## Testing Steps
1. Login as employee (Kian: kiancabalumcabalum@gmail.com)
2. Go to Dashboard or Leave page
3. Click "Request Leave" button
4. Select leave type from dropdown (should show: Vacation Leave, Sick Leave, Emergency Leave, etc.)
5. Fill in dates and reason
6. Submit request
7. Check Apache logs - should see successful insert with UUID
8. Verify record appears in leave history
9. Check Supabase database - record should be present with proper UUID

## Expected Log Output (Success)
```
=== SUBMIT LEAVE REQUEST DEBUG ===
Request data received: {"leave_type_id":"<UUID>","start_date":"2026-04-10",...}
Required fields validated
Employee found: Kian Piodena
Leave type mapping: {"1":"<UUID1>","2":"<UUID2>","3":"<UUID3>"}
Requested leave_type_id: <UUID>
Using provided UUID: <UUID>
Total business days calculated: 6
No overlapping leaves found
Leave request data to create: {"employee_id":"<UUID>","leave_type_id":"<UUID>",...}
=== SUPABASE INSERT DEBUG ===
Table: leave_requests
Data to insert: {...}
Response success: true
Response status code: 201
Leave request created: {"id":"<UUID>",...}
```

## Backward Compatibility
The system still supports numeric IDs (1, 2, 3) for backward compatibility:
- If a numeric ID is sent, it's mapped to the corresponding UUID
- Mapping is based on the order of leave types returned from database
- If mapping fails, returns validation error

## Next Steps
1. Test leave request submission thoroughly
2. Verify leave requests appear in employee dashboard
3. Test admin approval/denial workflow
4. Verify leave credits are properly deducted on approval
5. Check attendance records are created for approved leave dates

## Status: ✅ COMPLETE
All changes have been implemented and are ready for testing.
