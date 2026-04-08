# Where to Find Your Supabase Keys

## Visual Guide

### Step 1: Go to Project Settings
```
Supabase Dashboard
└── Click the ⚙️ (Settings/Gear Icon) at bottom left
```

### Step 2: Navigate to API Section
```
Settings Menu
└── Click "API" in the left sidebar
```

### Step 3: Copy Your Keys

You'll see a page with these sections:

```
┌─────────────────────────────────────────────────────────┐
│ Project API                                             │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ Project URL                                             │
│ https://abcdefghijklmnop.supabase.co                   │
│ [Copy]                                                  │
│                                                         │
│ ─────────────────────────────────────────────────────  │
│                                                         │
│ API Keys                                                │
│                                                         │
│ anon public                                             │
│ eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJz... │
│ [Copy]                                                  │
│                                                         │
│ service_role                                            │
│ eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJz... │
│ [Copy]                                                  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## What You Need to Copy

### 1. Project URL
- **Location**: Top of the API page
- **Looks like**: `https://xxxxxxxxxxxxx.supabase.co`
- **Example**: `https://abcdefghijklmnop.supabase.co`
- **Use**: This is your Supabase project's base URL

### 2. Anon Public Key
- **Location**: Under "API Keys" section, labeled "anon public"
- **Looks like**: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...` (very long string)
- **Length**: ~200-300 characters
- **Use**: Safe to use in frontend code, has limited permissions
- **Security**: Can be exposed publicly

### 3. Service Role Key
- **Location**: Under "API Keys" section, labeled "service_role"
- **Looks like**: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...` (very long string)
- **Length**: ~200-300 characters
- **Use**: Backend only, has full database access
- **Security**: ⚠️ NEVER expose this in frontend code!

## How to Use These Keys

### In config/supabase.php:

```php
<?php
// Replace these with YOUR actual values from Supabase

// 1. Your Project URL (from Supabase Settings > API)
define('SUPABASE_URL', 'https://abcdefghijklmnop.supabase.co');

// 2. Your anon public key (from Supabase Settings > API)
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFiY2RlZmdoaWprbG1ub3AiLCJyb2xlIjoiYW5vbiIsImlhdCI6MTYzMDAwMDAwMCwiZXhwIjoxOTQ1NTc2MDAwfQ.abcdefghijklmnopqrstuvwxyz1234567890');

// 3. Your service_role key (from Supabase Settings > API)
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFiY2RlZmdoaWprbG1ub3AiLCJyb2xlIjoic2VydmljZV9yb2xlIiwiaWF0IjoxNjMwMDAwMDAwLCJleHAiOjE5NDU1NzYwMDB9.zyxwvutsrqponmlkjihgfedcba0987654321');

// Environment setting
define('ENVIRONMENT', 'development');

// Table names (don't change these)
define('TABLE_EMPLOYEES', 'employees');
define('TABLE_ADMINS', 'admins');
define('TABLE_ATTENDANCE', 'attendance');
define('TABLE_LEAVE_TYPES', 'leave_types');
define('TABLE_LEAVE_REQUESTS', 'leave_requests');
define('TABLE_LEAVE_CREDITS', 'leave_credits');
define('TABLE_ANNOUNCEMENTS', 'announcements');
define('TABLE_WORK_CALENDAR', 'work_calendar');
define('TABLE_SYSTEM_AUDIT_LOG', 'system_audit_log');
define('TABLE_LEAVE_CREDIT_AUDIT', 'leave_credit_audit');
define('TABLE_USER_SESSIONS', 'user_sessions');
?>
```

## Key Characteristics

### Project URL
```
✓ Starts with: https://
✓ Ends with: .supabase.co
✓ Contains: 16 random characters
✓ Example: https://abcdefghijklmnop.supabase.co
```

### Anon Public Key
```
✓ Starts with: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
✓ Contains: Three parts separated by dots (.)
✓ Length: ~200-300 characters
✓ Contains: "role":"anon" when decoded
```

### Service Role Key
```
✓ Starts with: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.
✓ Contains: Three parts separated by dots (.)
✓ Length: ~200-300 characters
✓ Contains: "role":"service_role" when decoded
✓ Different from anon key!
```

## Common Mistakes to Avoid

### ❌ Wrong:
```php
// Missing quotes
define('SUPABASE_URL', https://abcd.supabase.co);

// Extra spaces
define('SUPABASE_ANON_KEY', ' eyJhbGci... ');

// Incomplete key (truncated)
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6...');

// Using same key for both
define('SUPABASE_ANON_KEY', 'eyJhbGci...');
define('SUPABASE_SERVICE_KEY', 'eyJhbGci...'); // Same as above!
```

### ✅ Correct:
```php
// Wrapped in quotes, no spaces, complete key
define('SUPABASE_URL', 'https://abcdefghijklmnop.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3M...[full key]');
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3M...[different full key]');
```

## Security Best Practices

### ✅ DO:
- Keep service_role key secret
- Use environment variables in production
- Rotate keys if compromised
- Use anon key for frontend (if needed)
- Store keys in config files (not in code)

### ❌ DON'T:
- Commit service_role key to Git
- Share service_role key publicly
- Use service_role key in JavaScript
- Hardcode keys in multiple files
- Store keys in plain text files in public folders

## Testing Your Configuration

After updating config/supabase.php:

1. Start XAMPP Apache
2. Go to `http://localhost/hris-mvp`
3. Try to login

### If you see:
- ✅ **Login page loads**: Configuration file is readable
- ✅ **Can login**: Keys are correct!
- ❌ **"Invalid API key"**: Check keys are copied correctly
- ❌ **"Connection failed"**: Check Project URL
- ❌ **Blank page**: Check PHP syntax in config file

## Quick Verification

To verify your keys are correct:

1. Project URL should load in browser (you'll see Supabase API docs)
2. Keys should be ~200-300 characters long
3. Keys should start with `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.`
4. Anon and service_role keys should be DIFFERENT

## Need Help?

If you're having trouble finding your keys:

1. Make sure you're logged into Supabase
2. Select the correct project (if you have multiple)
3. Look for the gear icon (⚙️) at the bottom left
4. Click API in the settings menu
5. The keys should be visible on that page

If keys are not showing:
- Refresh the page
- Check your internet connection
- Verify your Supabase project is fully created (not still initializing)

---

**Remember**: You only need these 3 things from Supabase:
1. Project URL
2. Anon public key  
3. Service role key

Copy them carefully into `config/supabase.php` and you're ready to go!
