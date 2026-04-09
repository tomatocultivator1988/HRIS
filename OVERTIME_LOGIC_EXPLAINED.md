# Overtime Detection Logic - Detailed Explanation

## How It Works (Step by Step)

### Step 1: Loop Through Each Day's Attendance

```php
foreach ($attendance as $record) {
    // Each $record is ONE day of attendance
    // Example: April 1, April 2, April 3, etc.
}
```

### Step 2: Check Hours Worked Per Day

```php
foreach ($attendance as $record) {
    $hours = $record['work_hours'];  // Hours worked THIS day
    $standard = 8;                    // Standard hours per day
    
    // Check if worked MORE than standard
    if ($hours > $standard) {
        $overtimeHours += ($hours - $standard);
    }
}
```

### Step 3: Accumulate Total Overtime

The system adds up overtime from ALL days in the period.

---

## Real Example (Day by Day)

Let's say an employee works in April 2026:

```
Date       | Time In  | Time Out | Work Hours | Standard | Overtime
-----------|----------|----------|------------|----------|----------
Apr 1      | 08:00 AM | 05:00 PM | 8.00       | 8        | 0 hours
Apr 2      | 08:00 AM | 06:00 PM | 9.00       | 8        | 1 hour
Apr 3      | 08:00 AM | 07:00 PM | 10.00      | 8        | 2 hours
Apr 4      | 08:00 AM | 05:00 PM | 8.00       | 8        | 0 hours
Apr 5      | 08:00 AM | 08:00 PM | 11.00      | 8        | 3 hours
...
Apr 30     | 08:00 AM | 05:00 PM | 8.00       | 8        | 0 hours

Total Overtime for the Month: 6 hours
```

---

## The Code (Simplified)

```php
// Initialize
$overtimeHours = 0.0;
$standard = 8; // Standard work hours per day

// Loop through each day
foreach ($attendance as $record) {
    $hoursWorkedToday = $record['work_hours']; // e.g., 10 hours
    
    // Check if overtime
    if ($hoursWorkedToday > $standard) {
        $overtimeToday = $hoursWorkedToday - $standard; // 10 - 8 = 2 hours
        $overtimeHours += $overtimeToday; // Add to total
    }
}

// Calculate overtime pay
$overtimePay = $overtimeHours × $hourlyRate × 1.25;
```

---

## Visual Flow

```
Day 1: 8 hours worked
├─ 8 hours ≤ 8 standard → No OT
└─ Total OT: 0 hours

Day 2: 9 hours worked
├─ 9 hours > 8 standard → 1 hour OT
└─ Total OT: 0 + 1 = 1 hour

Day 3: 10 hours worked
├─ 10 hours > 8 standard → 2 hours OT
└─ Total OT: 1 + 2 = 3 hours

Day 4: 8 hours worked
├─ 8 hours ≤ 8 standard → No OT
└─ Total OT: 3 + 0 = 3 hours

...continue for all days...

Final Total OT: 3 hours (for example)
Overtime Pay: 3 × ₱170.45 × 1.25 = ₱639.19
```

---

## Key Points

### 1. **Per-Day Checking**
- The system checks EACH day individually
- If Day 1 has 10 hours → 2 hours OT for Day 1
- If Day 2 has 8 hours → 0 hours OT for Day 2
- And so on...

### 2. **Accumulation**
- All overtime hours are added together
- Total OT = Sum of all daily overtime

### 3. **Standard Hours**
- Default: 8 hours per day
- Configurable per employee in `employee_compensation.standard_work_hours_per_day`
- Some companies use 9 hours, some use 8

### 4. **Work Hours Source**
- Comes from `attendance.work_hours` column
- This is calculated when attendance is recorded:
  ```php
  work_hours = (time_out - time_in) - break_time
  ```

---

## Example Calculation (Full Month)

**Employee: Juan Dela Cruz**
**Period: April 1-30, 2026 (22 working days)**

```
Regular Days (20 days):
  - 8 hours each = 0 OT

Overtime Days (2 days):
  - Day 15: 10 hours = 2 hours OT
  - Day 22: 11 hours = 3 hours OT

Total Overtime: 2 + 3 = 5 hours

Overtime Pay Calculation:
  Hourly Rate: ₱170.45
  OT Multiplier: 1.25
  OT Pay: 5 × ₱170.45 × 1.25 = ₱1,065.31
```

---

## What Happens in the Database

### Attendance Table (Daily Records)
```sql
SELECT date, work_hours 
FROM attendance 
WHERE employee_id = 'juan-id' 
  AND date BETWEEN '2026-04-01' AND '2026-04-30';
```

Result:
```
date       | work_hours
-----------|------------
2026-04-01 | 8.00
2026-04-02 | 9.00  ← 1 hour OT
2026-04-03 | 10.00 ← 2 hours OT
2026-04-04 | 8.00
...
```

### Payroll Line Item (Monthly Summary)
```sql
SELECT overtime_hours, overtime_pay 
FROM payroll_line_items 
WHERE employee_id = 'juan-id';
```

Result:
```
overtime_hours | overtime_pay
---------------|-------------
5.00           | ₱1,065.31
```

---

## Important Notes

### 1. **No Partial Day Tracking**
The system doesn't track:
- "First 8 hours regular, next 2 hours overtime"
- It just checks: Total hours > 8? → Overtime

### 2. **Break Time**
If your attendance system deducts break time:
```
Time In: 8:00 AM
Time Out: 6:00 PM
Total: 10 hours
Break: 1 hour
Work Hours: 9 hours → 1 hour OT
```

### 3. **Undertime**
If someone works LESS than 8 hours:
```php
if ($hours > 0 && $hours < $standard) {
    $undertimeMinutes += ($standard - $hours) × 60;
}
```
Example: Worked 6 hours → 2 hours (120 minutes) undertime

---

## Summary

**Yes, it checks overtime PER DAY consistently:**

1. Loop through each day's attendance record
2. For each day: Check if work_hours > 8
3. If yes: Calculate OT hours for that day
4. Add all daily OT hours together
5. Multiply total OT hours by hourly rate × 1.25

**Formula:**
```
For each day:
  IF work_hours > 8 THEN
    daily_OT = work_hours - 8
    total_OT += daily_OT
  END IF

overtime_pay = total_OT × hourly_rate × 1.25
```

This ensures accurate per-day overtime tracking!
