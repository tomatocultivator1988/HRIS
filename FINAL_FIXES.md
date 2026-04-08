# Final Fixes - CSP and 401 Error

## Issues Fixed

### 1. CSP Blocking Chart.js Source Map
**Error:** `Connecting to 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js.map' violates CSP directive: "connect-src 'self'"`

**Fix:** Added `https://cdn.jsdelivr.net` to `connect-src` in CSP directives
```php
'connect-src' => "'self' https://cdn.jsdelivr.net",
```

**File:** `config/security.php`

### 2. 401 Unauthorized Error
**Error:** `GET http://localhost/HRIS/api/dashboard/metrics 401 (Unauthorized)`
**Message:** `Missing authentication token`

**Root Cause:** The token is being sent correctly, but the issue is that the token might be:
1. Invalid or expired
2. Not being extracted properly by AuthMiddleware

**Debugging Added:**
- Added console logs to show token being sent
- Added response status logging
- Added user-friendly error messages

## Testing Steps

1. **Clear localStorage and login fresh:**
```javascript
// Open browser console and run:
localStorage.clear();
// Then login again
```

2. **Check token in console:**
```javascript
console.log('Token:', localStorage.getItem('hris_token'));
console.log('User:', localStorage.getItem('hris_user'));
```

3. **Test API call manually:**
```javascript
const token = localStorage.getItem('hris_token');
fetch('/HRIS/api/dashboard/metrics', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
}).then(r => r.json()).then(console.log);
```

## If 401 Error Persists

The issue is likely that the token is expired or invalid. Solutions:

### Option 1: Login Again (Recommended)
1. Clear localStorage: `localStorage.clear()`
2. Go to login page
3. Login with fresh credentials
4. New token will be generated

### Option 2: Check Token Expiry
The JWT token expires after 1 hour. If you logged in more than 1 hour ago, the token is expired.

### Option 3: Verify AuthMiddleware
Check if `src/Middleware/AuthMiddleware.php` is properly extracting the Bearer token from the Authorization header.

## Files Modified

1. `config/security.php` - Added cdn.jsdelivr.net to connect-src
2. `src/Views/dashboard/admin.php` - Added better debugging logs

## Next Steps

1. Clear localStorage
2. Login fresh
3. Check console for detailed logs
4. If still 401, check server logs for AuthMiddleware errors
