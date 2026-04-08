# Leave Approve/Deny and Unknown Leave Type - FIXED

## Issues Identified

### Issue 1: Approve/Deny Endpoints Not Found
- Frontend was calling endpoints with `PUT` method
- Routes were configured for `POST` method
- Result: 404 Not Found errors

### Issue 2: Leave Type Showing as "Unknown"
- Frontend had hardcoded `leaveTypes` object with numeric keys (1, 2, 3)
- Database now uses UUID keys for leave types
- Mapping didn't match, so all leave types showed as "Unknown"

## Solutions Implemented

### Fix 1: Updated Routes (config/routes.php)
Changed approve/deny routes from `POST` to `PUT`:
```php
// Before:
$router->addRoute('POST', '/api/leave/{id}/approve', ...);
$router->addRoute('POST', '/api/leave/{id}/deny', ...);

// After:
$router->addRoute('PUT', '/api/leave/{id}/approve', ...);
$router->addRoute('PUT', '/api/leave/{id}/deny', ...);
```

### Fix 2: Dynamic Leave Types Mapping (src/Views/leave/index.php)
1. Changed hardcoded `leaveTypes` object to dynamic `leaveTypesMap`
2. Updated `loadLeaveTypes()` to populate the map with UUID keys
3. Replaced all references from `leaveTypes[...]` to `leaveTypesMap[...]`

**Before:**
```javascript
const leaveTypes = {
    '1': 'Vacation Leave',
    '2': 'Sick Leave',
    '3': 'Emergency Leave'
};
```

**After:**
```javascript
let leaveTypesMap = {}; // Populated dynamically from API

// In loadLeaveTypes():
leaveTypesMap[type.id] = type.name; // UUID -> Name mapping
```

## Files Modified
1. `config/routes.php` - Changed POST to PUT for approve/deny routes
2. `src/Views/leave/index.php` - Dynamic leave types mapping

## Testing Steps

### Test Approve/Deny:
1. Login as admin (admin@hris.com)
2. Go to Leave Requests page
3. Click "Review" on any pending request
4. Click "Approve" or "Deny"
5. Should see success message
6. Request should update status in the list

### Test Leave Type Display:
1. Login as admin or employee
2. Go to Leave Requests page
3. Check pending requests table
4. Leave type column should show actual names (Vacation Leave, Sick Leave, etc.)
5. Not "Unknown"

## Expected Behavior

### Approve Flow:
1. Admin clicks "Approve" button
2. Confirmation dialog appears
3. PUT request to `/api/leave/{id}/approve`
4. Backend updates status to "Approved"
5. Frontend shows success message
6. List refreshes with updated status

### Deny Flow:
1. Admin clicks "Deny" button
2. Confirmation dialog appears
3. Optional denial reason can be entered
4. PUT request to `/api/leave/{id}/deny`
5. Backend updates status to "Denied"
6. Frontend shows success message
7. List refreshes with updated status

### Leave Type Display:
- Pending requests show correct leave type names
- My leave history shows correct leave type names
- Review modal shows correct leave type name
- All based on UUID mapping from database

## Status: ✅ COMPLETE
All fixes have been implemented and are ready for testing.
