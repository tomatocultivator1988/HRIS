# HRIS MVC Architecture - Complete Setup

## ✅ MVC Conversion Complete

All files have been converted to strict MVC architecture. The system now follows proper separation of concerns with no static HTML pages.

## Architecture Overview

```
Browser Request
    ↓
Apache (.htaccess)
    ↓
public/index.php (Front Controller)
    ↓
Router (matches route)
    ↓
Middleware Pipeline
    ↓
Controller (handles request)
    ↓
Service Layer (business logic)
    ↓
Model (database access)
    ↓
View (renders HTML)
    ↓
Response (sent to browser)
```

## How to Access the Application

**Main URL:** `http://localhost/HRIS/`

This will:
1. Route through `.htaccess` to `public/index.php`
2. Match route `/` to `AuthController@loginForm`
3. Render `src/Views/auth/login.php`
4. Load JavaScript assets from `public/assets/js/`

## Static Assets

Static files (JS, CSS, images) are served directly by Apache:
- **URL:** `http://localhost/HRIS/assets/js/config.js`
- **File:** `public/assets/js/config.js`

The `.htaccess` rules ensure:
- Assets are served with correct MIME types
- No routing through PHP for static files
- Proper caching headers

## Key Files

### Entry Point
- `public/index.php` - Front controller, handles all requests

### Routing
- `config/routes.php` - All route definitions
- `src/Core/Router.php` - Route matching and dispatching

### Request Flow
- `src/Core/Request.php` - HTTP request wrapper (strips `/HRIS/` prefix)
- `src/Core/Response.php` - HTTP response wrapper
- `src/Core/Controller.php` - Base controller with helper methods

### Views
- `src/Views/auth/login.php` - Login page (rendered by AuthController)
- `src/Core/View.php` - View rendering engine

### Configuration
- `.htaccess` - Root Apache config (routes to public/)
- `public/.htaccess` - Public directory config (serves assets, routes to index.php)

## Important Routes

### Web Routes (HTML responses)
- `GET /` → `AuthController@loginForm` - Login page
- `GET /login` → `AuthController@loginForm` - Login page
- `GET /dashboard` → `DashboardController@index` - Dashboard
- `GET /dashboard/admin` → `DashboardController@admin` - Admin dashboard
- `GET /dashboard/employee` → `DashboardController@employee` - Employee dashboard

### API Routes (JSON responses)
- `POST /api/auth/login` → `AuthController@login` - Login API
- `POST /api/auth/logout` → `AuthController@logout` - Logout API
- `GET /api/auth/verify` → `AuthController@verify` - Verify token
- `GET /api/employees` → `EmployeeController@apiIndex` - List employees
- `GET /api/dashboard/metrics` → `DashboardController@metrics` - Dashboard metrics

## JavaScript Configuration

The frontend JavaScript automatically detects the base path:

```javascript
// public/assets/js/config.js
window.AppConfig = {
    basePath: '/HRIS',           // Auto-detected
    apiPath: '/HRIS/api',        // API base path
    apiUrl: function(endpoint) {  // Helper to build API URLs
        return this.apiPath + '/' + endpoint;
    }
};
```

Usage in JavaScript:
```javascript
// Login API call
const response = await fetch(window.AppConfig.apiUrl('auth/login'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
});
```

## Troubleshooting

### Issue: Blank page or 404
**Solution:** Make sure you're accessing `http://localhost/HRIS/` (without `/public/`)

### Issue: JavaScript files not loading
**Check:**
1. Browser console for errors
2. Network tab - check if assets return HTML instead of JavaScript
3. Verify `.htaccess` files are in place
4. Check Apache `mod_rewrite` is enabled

### Issue: Routes not matching
**Check:**
1. `logs/app.log` for request URI
2. Verify `Request::parseUri()` is stripping `/HRIS/` prefix
3. Check `config/routes.php` for route definitions

## Testing

Access these URLs to verify everything works:

1. **Login Page:** `http://localhost/HRIS/`
   - Should show login form
   - JavaScript should load (check console)
   - No errors in browser console

2. **API Test:** `http://localhost/HRIS/api/auth/verify`
   - Should return JSON: `{"success":false,"message":"Authorization token required"}`

3. **Static Asset:** `http://localhost/HRIS/assets/js/config.js`
   - Should return JavaScript code
   - Content-Type should be `application/javascript`

## Next Steps

1. Access `http://localhost/HRIS/` in your browser
2. Check browser console for any errors
3. Try logging in with demo credentials:
   - Admin: `admin@company.com` / `admin123`
   - Employee: `employee@company.com` / `emp123`

## Files Cleaned Up

Removed non-MVC files:
- ❌ `public/login.html` (now `src/Views/auth/login.php`)
- ❌ `public/test.html` (test file)
- ❌ `public/debug.php` (debug file)
- ❌ `public/debug2.php` (debug file)
- ❌ `public/force_reload.php` (debug file)
- ❌ `public/test.php` (test file)

## Pure MVC Structure

✅ All HTML pages rendered through Controllers/Views
✅ All routes defined in `config/routes.php`
✅ Static assets (JS/CSS/images) served directly
✅ No direct PHP file access (except index.php)
✅ Proper separation of concerns
✅ Middleware pipeline for authentication/authorization
✅ Service layer for business logic
✅ Model layer for database access

The system is now a **strict MVC application** with proper architecture!
