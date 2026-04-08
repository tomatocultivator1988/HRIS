# Authorization Fix - 403 Forbidden Error After Login

## Problem
After successful login, users were getting a 403 (Forbidden) error when trying to access the dashboard.

## Root Cause
The web routes (HTML pages) had `auth` and `role:admin` middleware that required a JWT Bearer token in the Authorization header. However, browsers don't automatically send JWT tokens in headers when navigating to pages - they only send them in JavaScript fetch/AJAX requests.

This created a mismatch:
- **API routes**: Use Bearer tokens in Authorization header ✅
- **Web routes**: Browser navigation doesn't send Bearer tokens ❌

## Solution
Separated authentication handling for web routes vs API routes:

### 1. Removed Middleware from Web Routes
**File**: `config/routes.php`

Removed `auth` and `role:admin` middleware from all web routes (HTML pages):
```php
// Before:
$router->addRoute('GET', '/dashboard/admin', 'DashboardController@admin', ['logging', 'auth', 'role:admin']);

// After:
$router->addRoute('GET', '/dashboard/admin', 'DashboardController@admin', ['logging']);
```

### 2. Updated Controllers to Not Require Backend Auth
**File**: `src/Controllers/DashboardController.php`

Modified `admin()` and `employee()` methods to:
- Remove `requireAuth()` and `requireRole()` calls
- Pass `null` for user data (will be populated by JavaScript)
- Let JavaScript handle authentication and redirects

```php
public function admin(Request $request): Response
{
    // For web routes, authentication is handled by JavaScript
    $html = $this->view->render('dashboard/admin', [
        'title' => 'Admin Dashboard - HRIS MVP',
        'user' => null, // Will be populated by JavaScript
        'metrics' => [] // Will be loaded via API call
    ]);
    
    return new Response($html, 200, ['Content-Type' => 'text/html']);
}
```

### 3. Updated Base Layout for JavaScript-Based Navigation
**File**: `src/Views/layouts/base.php`

- Changed navigation to be populated by JavaScript from localStorage
- Added script to read user from localStorage and populate nav links based on role
- Navigation is hidden by default and shown once user data is loaded

## How It Works Now

### Web Routes (HTML Pages)
1. User logs in → Token saved to localStorage
2. JavaScript redirects to dashboard
3. Dashboard HTML loads (no auth check at PHP level)
4. `auth.js` runs `initialize()` method:
   - Checks if token exists in localStorage
   - Calls `/api/auth/verify` to validate token
   - If valid: Stays on page, populates navigation
   - If invalid: Redirects to login
5. JavaScript populates user info and navigation from localStorage

### API Routes (JSON Endpoints)
1. JavaScript makes fetch request with Bearer token in header
2. `auth` middleware validates token
3. `role` middleware checks user role
4. Returns JSON response

## Files Modified
1. `config/routes.php` - Removed middleware from web routes
2. `src/Controllers/DashboardController.php` - Removed backend auth checks
3. `src/Views/layouts/base.php` - Made navigation JavaScript-based

## Testing
1. Navigate to `http://localhost/HRIS/login`
2. Login with `admin@company.com` / `Admin123!`
3. Should redirect to `/HRIS/dashboard/admin`
4. Dashboard should load successfully (no 403 error)
5. Navigation should appear with user name and role-based links
6. Metrics will be loaded via API call (separate issue if they don't load)

## Result
✅ Login flow works correctly
✅ Dashboard loads without 403 error
✅ Navigation populated from JavaScript
✅ API routes still protected by middleware
✅ Web routes use JavaScript for authentication
