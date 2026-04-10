# Recruitment Page - Session Expired Fix

## Problem
When accessing the recruitment page (`/HRIS/recruitment`), you see a "Session Expired" modal and are redirected to login.

## Root Cause
The recruitment page was immediately trying to load data via API calls on page load without first checking if the user is authenticated. If the user is not logged in or the session token has expired, the API call fails with a 401 error, triggering the "Session Expired" modal.

## Solution Applied
Added an authentication check in the `DOMContentLoaded` event handler before making any API calls:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    if (!window.AuthManager || !window.AuthManager.isAuthenticated()) {
        // Redirect to login if not authenticated
        const loginUrl = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
        window.location.href = loginUrl;
        return;
    }
    
    loadJobPostings();
    // ... rest of initialization
});
```

This ensures that:
1. If the user is not logged in, they are redirected to the login page immediately
2. If the user is logged in, the page loads normally and makes API calls
3. No "Session Expired" modal appears unnecessarily

## Sidebar Navigation
The Recruitment link is already present in the admin sidebar (`src/Views/layouts/admin_sidebar.php`) at line 54-60:

```php
<a href="<?= base_url('/recruitment') ?>" class="flex items-center px-4 py-3 <?= $isActive('recruitment') ?>">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
    </svg>
    Recruitment
</a>
```

The sidebar is included in ALL admin pages, so the Recruitment link appears on:
- Dashboard
- Employees
- Attendance
- Leave Requests
- Recruitment (current page)
- Manage Salaries
- Payroll
- Reports

## Logout Button
The logout button in the sidebar is automatically handled by `auth.js`. The script at the bottom of `auth.js` (lines 648-656) automatically attaches a click handler to any element with `id="logout-btn"`:

```javascript
// Set up logout button if present
const logoutBtn = document.getElementById('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.AuthManager.logout();
    });
}
```

This means the logout button works on ALL pages that include `auth.js`, including:
- All admin pages
- The recruitment page
- Any other page that includes the sidebar

## How to Test

### 1. Test Login Flow
1. Open your browser and go to `http://localhost/HRIS/login`
2. Login with your admin credentials
3. You should be redirected to the dashboard

### 2. Test Recruitment Page Access
1. After logging in, click "Recruitment" in the sidebar
2. The page should load without showing "Session Expired"
3. You should see the recruitment interface with tabs for Job Postings, Applicants, and Pipeline View

### 3. Test Logout
1. While on any admin page (including recruitment), click the "Logout" button at the bottom of the sidebar
2. You should see a confirmation modal asking "Are you sure you want to logout?"
3. Click "Logout" to confirm
4. You should be redirected to the login page

### 4. Test Session Expired (When Not Logged In)
1. Clear your browser's localStorage (F12 → Application → Local Storage → Clear)
2. Try to access `http://localhost/HRIS/recruitment` directly
3. You should be immediately redirected to the login page (no "Session Expired" modal)

### 5. Test Navigation Between Pages
1. Login as admin
2. Navigate between different pages using the sidebar:
   - Dashboard → Employees → Attendance → Recruitment → Reports
3. All pages should load correctly
4. The Recruitment link should be visible and highlighted when on the recruitment page

## Files Modified
1. `src/Views/recruitment/index.php` - Added authentication check in DOMContentLoaded

## Files Already Correct
1. `src/Views/layouts/admin_sidebar.php` - Already has Recruitment link and logout button
2. `public/assets/js/auth.js` - Already has global logout handler
3. `public/assets/js/config.js` - Already provides URL helpers

## Troubleshooting

### If you still see "Session Expired":
1. **Check if you're logged in**: Open browser console (F12) and type:
   ```javascript
   window.AuthManager.isAuthenticated()
   ```
   Should return `true` if logged in.

2. **Check your token**: In console, type:
   ```javascript
   localStorage.getItem('hris_token')
   ```
   Should return a JWT token string if logged in.

3. **Clear cache and try again**:
   - Clear browser cache (Ctrl+Shift+Delete)
   - Clear localStorage (F12 → Application → Local Storage → Clear)
   - Login again

4. **Check token expiration**: Tokens expire after a certain time. If you've been logged in for a long time, you may need to login again.

### If logout button doesn't work:
1. **Check console for errors**: Open F12 console and click logout. Look for any JavaScript errors.

2. **Verify auth.js is loaded**: In console, type:
   ```javascript
   window.AuthManager
   ```
   Should return an object with methods like `logout()`, `isAuthenticated()`, etc.

3. **Check if button exists**: In console, type:
   ```javascript
   document.getElementById('logout-btn')
   ```
   Should return the button element.

## Summary
- ✅ Recruitment page now checks authentication before loading
- ✅ Recruitment link is in the sidebar on all admin pages
- ✅ Logout button works on all pages (handled by auth.js)
- ✅ Session expired modal only shows when appropriate
- ✅ Proper redirect to login when not authenticated
