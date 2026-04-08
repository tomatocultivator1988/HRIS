# LEAVE SYSTEM FLOW - COMPLETE ANALYSIS

## OVERVIEW
Ari ang complete flow sang leave request system from filing to automatic status updates.

---

## STEP 1: EMPLOYEE FILES LEAVE REQUEST

### What Employee Inputs:
```javascript
{
    "leave_type_id": 1,        // ID sang leave type (Vacation, Sick, Emergency)
    "start_date": "2024-04-15", // Start date sang leave
    "end_date": "2024-04-17",   // End date sang leave
    "reason": "Family vacation" // Reason (optional)
}
```

### What Happens (LeaveService::submitLeaveRequest):
1. ✅ **Validates employee exists and is active**
2. ✅ **Calculates business days** (Monday-Friday only)
   - Excludes weekends automatically
   - Example: April 15-17 (Mon-Wed) = 3 days
3. ✅ **Checks for overlapping leaves**
   - Prevents double-booking
4. ✅ **Creates leave request with status = "Pending"**

### Database Record Created:
```sql
leave_requests table:
- id: auto-generated UUID
- employee_id: employee's ID
- leave_type_id: 1
- start_date: 2024-04-15
- end_date: 2024-04-17
- total_days: 3 (business days only)
- reason: "Family vacation"
- status: "Pending"  ← IMPORTANT!
- reviewed_by: NULL
- reviewed_at: NULL
```

---

## STEP 2: ADMIN VIEWS PENDING REQUESTS

### API Endpoint:
`GET /api/leave/pending` (Admin only)

### What Admin Sees:
```javascript
{
    "pending_requests": [
        {
            "id": "leave-uuid-123",
            "employee_id": "emp-uuid-456",
            "employee_name": "Juan Dela Cruz",
            "employee_number": "EMP001",
            "department": "IT",
            "position": "Developer",
            "leave_type_id": 1,
            "start_date": "2024-04-15",
            "end_date": "2024-04-17",
            "total_days": 3,
            "reason": "Family vacation",
            "status": "Pending"
        }
    ]
}
```

---

## STEP 3: ADMIN APPROVES OR DENIES

### Option A: APPROVE
**API:** `POST /api/leave/{id}/approve`

**What Happens (LeaveService::approveLeaveRequest):**
1. ✅ **Updates leave request status to "Approved"**
2. ✅ **Records reviewer_id and reviewed_at timestamp**
3. ✅ **AUTOMATICALLY CREATES ATTENDANCE RECORDS** ← KEY FEATURE!

### Automatic Attendance Creation (createLeaveAttendanceRecords):
```php
// For EACH DAY in the leave period:
foreach ($startDate to $endDate) {
    if (isWorkingDay($date)) {  // Monday-Friday only
        // Check if attendance record already exists
        if (!existingRecord) {
            // Create "On Leave" attendance record
            attendance table:
            - employee_id: emp-uuid-456
            - date: 2024-04-15 (then 04-16, 04-17)
            - time_in: NULL
            - time_out: NULL
            - status: "On Leave"  ← AUTOMATIC!
            - work_hours: 0.00
            - remarks: "On approved leave (Leave ID: leave-uuid-123)"
        }
    }
}
```

### Result:
- ✅ Leave request status = "Approved"
- ✅ 3 attendance records created with status = "On Leave"
- ✅ One record per working day (April 15, 16, 17)

### Option B: DENY
**API:** `POST /api/leave/{id}/deny`

**What Happens:**
1. ✅ Updates leave request status to "Denied"
2. ✅ Records denial_reason
3. ❌ NO attendance records created

---

## STEP 4: ATTENDANCE STATUS DURING LEAVE PERIOD

### When Absence Detection Runs:
`POST /api/attendance/detect-absences` (Daily cron job)

**What Happens (AttendanceService::detectAbsentEmployees):**
```php
foreach ($activeEmployees as $employee) {
    // Check if attendance record exists for today
    $record = findByEmployeeAndDate($employee, $today);
    
    if (!$record) {
        // Check if employee has approved leave for today
        $approvedLeave = getApprovedLeavesForDate($today);
        
        if ($approvedLeave) {
            // Create "On Leave" record
            status = "On Leave"
        } else {
            // Create "Absent" record
            status = "Absent"
        }
    }
}
```

### Key Points:
- ✅ If leave is approved BEFORE the date, attendance record already exists
- ✅ If leave is approved ON the date, absence detection creates "On Leave" record
- ✅ Employees on approved leave are NOT marked as "Absent"

---

## STEP 5: STATUS AFTER LEAVE ENDS

### IMPORTANT: Status Does NOT Auto-Remove!

**Current Behavior:**
- ❌ "On Leave" status does NOT automatically change after end_date
- ❌ No scheduled job to update status
- ✅ Attendance records are PERMANENT historical records

**Why This is Correct:**
- Attendance records are historical data
- "On Leave" on April 15 should ALWAYS show "On Leave"
- It's a record of what happened that day

**What Happens After Leave Ends:**
```
April 15: Status = "On Leave" (leave period)
April 16: Status = "On Leave" (leave period)
April 17: Status = "On Leave" (leave period)
April 18: Status = "Present" or "Absent" (normal attendance)
         ↑ NEW record created when employee times in
         ↑ OR marked absent if no time-in
```

---

## COMPLETE FLOW DIAGRAM

```
EMPLOYEE FILES LEAVE
    ↓
[leave_requests table]
status = "Pending"
    ↓
ADMIN REVIEWS
    ↓
    ├─→ APPROVE
    │   ↓
    │   [leave_requests table]
    │   status = "Approved"
    │   ↓
    │   AUTO-CREATE ATTENDANCE RECORDS
    │   ↓
    │   [attendance table]
    │   date = 2024-04-15, status = "On Leave"
    │   date = 2024-04-16, status = "On Leave"
    │   date = 2024-04-17, status = "On Leave"
    │   ↓
    │   EMPLOYEE IS ON LEAVE
    │   (Shows purple badge in UI)
    │
    └─→ DENY
        ↓
        [leave_requests table]
        status = "Denied"
        ↓
        NO ATTENDANCE RECORDS CREATED
        ↓
        EMPLOYEE MUST COME TO WORK
        (Will be marked "Absent" if no time-in)
```

---

## ABSENCE DETECTION INTEGRATION

### Daily Process:
```
1. Run detectAbsentEmployees(today)
2. Get all active employees
3. Get all approved leaves for today
4. For each employee:
   - Has attendance record? → Skip
   - No record + On approved leave? → Create "On Leave"
   - No record + Not on leave? → Create "Absent"
```

### Result:
- ✅ Employees on approved leave are NEVER marked absent
- ✅ "On Leave" status is automatically applied
- ✅ Dashboard shows separate counts for "Absent" vs "On Leave"

---

## UI DISPLAY

### Attendance Page:
```
Date: April 15, 2024

Employee Name    | Status      | Time In | Time Out | Hours
Juan Dela Cruz   | On Leave    | -       | -        | 0.00
                   ↑ Purple badge
                   ↑ Shows leave dates in modal
```

### Dashboard Metrics:
```
Today's Attendance:
- Present: 45
- Late: 3
- Absent: 2
- On Leave: 5  ← Separate count!
```

---

## ANSWERS TO YOUR QUESTIONS

### Q1: Ano ang i-input sang employee pag file sang leave?
**A:** 
- leave_type_id (Vacation/Sick/Emergency)
- start_date
- end_date
- reason (optional)

### Q2: Ma-approve or ma-deny sang admin?
**A:** Yes! Admin can:
- Approve → Creates "On Leave" attendance records
- Deny → No attendance records, employee must work

### Q3: Ma-update ang status to "On Leave" based sa duration?
**A:** YES! Automatic!
- When approved, creates attendance records for ALL working days
- Each record has status = "On Leave"
- Covers entire duration (start_date to end_date)

### Q4: Naga-kadula ang "On Leave" status after end date?
**A:** NO! And this is CORRECT!
- Attendance records are permanent historical data
- "On Leave" on April 15 stays "On Leave" forever
- After leave ends, NEW records are created for new dates
- Old records remain unchanged

---

## SUMMARY

✅ **WORKING FEATURES:**
1. Employee files leave with dates and reason
2. Admin sees pending requests
3. Admin approves → Auto-creates "On Leave" attendance
4. Admin denies → No attendance records
5. Absence detection checks for approved leaves
6. "On Leave" employees NOT marked absent
7. Dashboard shows separate "On Leave" count
8. UI displays purple badge for "On Leave"

✅ **CORRECT BEHAVIOR:**
- Status does NOT auto-remove after end date
- Historical records remain unchanged
- New dates get new attendance records

🎯 **SYSTEM IS COMPLETE AND WORKING AS DESIGNED!**
