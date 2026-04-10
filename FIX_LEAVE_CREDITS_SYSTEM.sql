-- ============================================
-- COMPLETE LEAVE CREDITS SYSTEM FIX
-- Run this in Supabase SQL Editor
-- ============================================

-- STEP 1: Ensure leave_credit_audit table exists
CREATE TABLE IF NOT EXISTS leave_credit_audit (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    employee_id UUID REFERENCES employees(id) ON DELETE CASCADE,
    leave_type_id UUID REFERENCES leave_types(id),
    action VARCHAR(20) NOT NULL, -- 'USED', 'RESTORED', 'ADJUSTED', 'INITIALIZED'
    previous_used INT NOT NULL,
    new_used INT NOT NULL,
    reason TEXT,
    performed_by UUID REFERENCES employees(id),
    created_at TIMESTAMP DEFAULT NOW()
);

-- STEP 2: Create or replace the trigger function
CREATE OR REPLACE FUNCTION update_leave_credits_on_approval()
RETURNS TRIGGER AS $$
BEGIN
    -- Only process if status changed to 'Approved'
    IF NEW.status = 'Approved' AND (OLD.status IS NULL OR OLD.status != 'Approved') THEN
        -- Deduct leave days from credits
        UPDATE leave_credits 
        SET used_credits = used_credits + NEW.total_days,
            updated_at = NOW()
        WHERE employee_id = NEW.employee_id 
          AND leave_type_id = NEW.leave_type_id 
          AND year = EXTRACT(YEAR FROM NEW.start_date);
          
        -- Log the credit usage
        INSERT INTO leave_credit_audit (
            employee_id, leave_type_id, action, previous_used, new_used, 
            reason, performed_by
        )
        SELECT 
            NEW.employee_id, 
            NEW.leave_type_id, 
            'USED', 
            lc.used_credits - NEW.total_days, 
            lc.used_credits,
            'Leave request approved: ' || NEW.id,
            NEW.reviewed_by
        FROM leave_credits lc
        WHERE lc.employee_id = NEW.employee_id 
          AND lc.leave_type_id = NEW.leave_type_id 
          AND lc.year = EXTRACT(YEAR FROM NEW.start_date);
    END IF;
    
    -- If status changed from 'Approved' to something else, restore credits
    IF OLD.status = 'Approved' AND NEW.status != 'Approved' THEN
        UPDATE leave_credits 
        SET used_credits = used_credits - NEW.total_days,
            updated_at = NOW()
        WHERE employee_id = NEW.employee_id 
          AND leave_type_id = NEW.leave_type_id 
          AND year = EXTRACT(YEAR FROM NEW.start_date);
          
        -- Log the credit restoration
        INSERT INTO leave_credit_audit (
            employee_id, leave_type_id, action, previous_used, new_used, 
            reason, performed_by
        )
        SELECT 
            NEW.employee_id, 
            NEW.leave_type_id, 
            'RESTORED', 
            lc.used_credits + NEW.total_days, 
            lc.used_credits,
            'Leave request status changed: ' || NEW.id,
            NEW.reviewed_by
        FROM leave_credits lc
        WHERE lc.employee_id = NEW.employee_id 
          AND lc.leave_type_id = NEW.leave_type_id 
          AND lc.year = EXTRACT(YEAR FROM NEW.start_date);
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- STEP 3: Drop and recreate trigger to ensure it's active
DROP TRIGGER IF EXISTS trigger_update_leave_credits ON leave_requests;
CREATE TRIGGER trigger_update_leave_credits
    AFTER UPDATE ON leave_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_leave_credits_on_approval();

-- STEP 4: Initialize leave credits for ALL existing employees who don't have them
-- This will create records for current year (2026)
INSERT INTO leave_credits (employee_id, leave_type_id, total_credits, used_credits, year)
SELECT 
    e.id as employee_id,
    lt.id as leave_type_id,
    lt.days_allowed as total_credits,
    0 as used_credits,
    EXTRACT(YEAR FROM CURRENT_DATE)::INT as year
FROM employees e
CROSS JOIN leave_types lt
WHERE e.is_active = true
  AND NOT EXISTS (
    SELECT 1 FROM leave_credits lc 
    WHERE lc.employee_id = e.id 
      AND lc.leave_type_id = lt.id 
      AND lc.year = EXTRACT(YEAR FROM CURRENT_DATE)
  );

-- STEP 5: Backfill used_credits for existing approved leaves
-- This will calculate and update used_credits based on already approved leaves
UPDATE leave_credits lc
SET used_credits = COALESCE((
    SELECT SUM(lr.total_days)
    FROM leave_requests lr
    WHERE lr.employee_id = lc.employee_id
      AND lr.leave_type_id = lc.leave_type_id
      AND lr.status = 'Approved'
      AND EXTRACT(YEAR FROM lr.start_date) = lc.year
), 0),
updated_at = NOW()
WHERE lc.year = EXTRACT(YEAR FROM CURRENT_DATE);

-- STEP 6: Log the backfill action
INSERT INTO leave_credit_audit (
    employee_id, leave_type_id, action, previous_used, new_used, 
    reason, performed_by
)
SELECT 
    lc.employee_id,
    lc.leave_type_id,
    'ADJUSTED',
    0,
    lc.used_credits,
    'Backfilled credits from existing approved leaves',
    NULL
FROM leave_credits lc
WHERE lc.used_credits > 0 
  AND lc.year = EXTRACT(YEAR FROM CURRENT_DATE);

-- STEP 7: Verify the fix - Check kiancabalumcabalum@gmail.com
SELECT 
    e.first_name,
    e.last_name,
    lt.name as leave_type,
    lc.total_credits,
    lc.used_credits,
    lc.remaining_credits,
    lc.year
FROM employees e
JOIN leave_credits lc ON e.id = lc.employee_id
JOIN leave_types lt ON lc.leave_type_id = lt.id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com'
  AND lc.year = EXTRACT(YEAR FROM CURRENT_DATE)
ORDER BY lt.name;

-- STEP 8: Show approved leaves count
SELECT 
    e.work_email,
    lt.name as leave_type,
    COUNT(*) as approved_count,
    SUM(lr.total_days) as total_days_approved
FROM leave_requests lr
JOIN employees e ON lr.employee_id = e.id
JOIN leave_types lt ON lr.leave_type_id = lt.id
WHERE e.work_email = 'kiancabalumcabalum@gmail.com'
  AND lr.status = 'Approved'
  AND EXTRACT(YEAR FROM lr.start_date) = EXTRACT(YEAR FROM CURRENT_DATE)
GROUP BY e.work_email, lt.name;
