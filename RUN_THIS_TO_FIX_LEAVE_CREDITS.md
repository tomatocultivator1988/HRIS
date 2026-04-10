# QUICK FIX GUIDE - Leave Credits System

## Step 1: Run SQL in Supabase
1. Open Supabase SQL Editor
2. Copy and paste contents of `FIX_LEAVE_CREDITS_SYSTEM.sql`
3. Click "Run"
4. Check the verification results at the bottom

## Step 2: Verify the Fix
After running SQL, you should see:
- kiancabalumcabalum@gmail.com now has leave credits with `used_credits` > 0
- Her approved leaves are counted in the credits
- Trigger exists and is active

## Step 3: Test in Browser
1. Login as kiancabalumcabalum@gmail.com
2. Go to Leave page
3. Try to file a leave request that exceeds her remaining credits
4. Should see error: "Insufficient [Type] credits. You have X days remaining but requested Y days"

## Step 4: Test Approval
1. Login as admin
2. File a leave request for an employee with sufficient credits
3. Approve the leave
4. Check database: `used_credits` should increase
5. Check `leave_credit_audit` table: log entry should exist

## What Was Fixed

### Before:
- ❌ No validation when filing leave
- ❌ Credits not deducted on approval
- ❌ Fake hardcoded credit balances
- ❌ Missing getLeaveCredits() method

### After:
- ✅ Validates credits before filing
- ✅ Automatic deduction via database trigger
- ✅ Real credit balances from database
- ✅ Complete getLeaveCredits() implementation
- ✅ Backfilled existing approved leaves

## Files Changed
- `FIX_LEAVE_CREDITS_SYSTEM.sql` - Database migration
- `src/Services/LeaveService.php` - Added validation + fixed methods

## That's It!
Run the SQL, test it, and leave credits will work perfectly.
