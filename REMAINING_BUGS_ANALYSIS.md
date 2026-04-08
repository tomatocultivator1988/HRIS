# Remaining Bugs Analysis

## Summary
Based on the 22 bugs identified and the completed MVC Core Bugfixes spec, here's what has been fixed and what remains:

---

## ✅ ALREADY FIXED (From MVC Core Bugfixes Spec)

### Bug #5 - Navigation Links Missing Base Path ✅
**Status**: FIXED in MVC Core Bugfixes
- **Fix**: Added `base_url()` helper function in `src/Config/helpers.php`
- **Files Updated**: 
  - `src/Views/layouts/base.php` - All nav links now use `base_url()`
  - `src/Views/auth/login.php` - All asset paths use `base_url()`
- **Related**: Bug #7, #19 (all asset path issues)

### Bug #7 - admin.php Hardcoded charts.js Path ✅
**Status**: FIXED in MVC Core Bugfixes (Asset Paths category)
- **Fix**: All asset paths updated to use `base_url()` helper
- **File**: `src/Views/dashboard/admin.php`

### Bug #12 - redirectToLogin() Missing Base Path ✅
**Status**: FIXED in MVC Core Bugfixes (Consistency category)
- **Fix**: Changed redirect from '/' to '/login'
- **File**: `src/Controllers/DashboardController.php`
- **Note**: Still needs `base_url('/login')` wrapper

### Bug #19 - Navigation Links Absolute Paths ✅
**Status**: FIXED in MVC Core Bugfixes (Asset Paths category)
- **Fix**: All navigation links in base.php now use `base_url()`
- **File**: `src/Views/layouts/base.php`

### Partial Fix: Bug #3 - Duplicate Exception Classes
**Status**: PARTIALLY ADDRESSED
- **What was fixed**: Duplicate Request/Response classes removed from Router.php
- **What remains**: Exception classes still duplicated in Controller.php and ErrorHandler.php

---

## 🔴 CRITICAL BUGS STILL REMAINING (Will Crash)

### Bug #1 - Form Missing method="POST" 🔴
**File**: `src/Views/auth/login.php` line 20
**Issue**: `<form id="login-form">` has no method attribute
**Impact**: Form defaults to GET, credentials sent in URL
**Fix Needed**: Add `method="POST"` to form tag

### Bug #2 - Login Response Nesting Mismatch 🔴
**Files**: `src/Controllers/AuthController.php` + `public/assets/js/auth.js`
**Issue**: Controller wraps response in `data` key, JS reads wrong level
**Impact**: Token never saved, all logins fail silently
**Fix Needed**: Either unwrap in controller OR update JS to read `data.data.access_token`

### Bug #3 - Duplicate Exception Classes (Partial) 🔴
**Files**: `src/Core/Controller.php` + `src/Core/ErrorHandler.php`
**Issue**: Both define ValidationException, AuthenticationException, etc.
**Impact**: Fatal "Cannot redeclare class" error
**Fix Needed**: Remove exception classes from one file (keep in ErrorHandler.php)

### Bug #4 - base.php Reads $data['key'] After extract() 🔴
**File**: `src/Views/layouts/base.php`
**Issue**: View::renderLayout() calls extract($data), but base.php reads $data['title']
**Impact**: Undefined variable errors, user info never displays
**Fix Needed**: Change `$data['title']` to `$title`, `$data['user']` to `$user`, etc.

### Bug #6 - RoleMiddleware Calls Non-Existent Methods 🔴
**File**: `src/Middleware/RoleMiddleware.php`
**Issue**: Calls `$db->getConfig()` and `$db->insert()` on wrong class
**Impact**: Fatal error on role-checked routes
**Fix Needed**: Fix database connection resolution or remove broken logging

---

## 🟠 HIGH SEVERITY BUGS (Wrong Logic)

### Bug #8 - AuthController Empty Array Check 🟠
**File**: `src/Controllers/AuthController.php`
**Issue**: `if (!$input)` treats empty array as falsy
**Impact**: Returns "Invalid JSON input" for non-JSON requests
**Fix Needed**: Change to `if ($input === null || empty($input))`

### Bug #9 - SupabaseConnection Array Conditions 🟠
**File**: `src/Core/SupabaseConnection.php`
**Issue**: Array conditions JSON-encoded but key never appended to query
**Impact**: IN queries produce garbage SQL
**Fix Needed**: Fix array condition handling in select() method

### Bug #10 - AuthMiddleware is_active Field Missing 🟠
**File**: `src/Middleware/AuthMiddleware.php`
**Issue**: Checks `$userData['is_active']` which may not exist
**Impact**: All authenticated requests rejected if field missing
**Fix Needed**: Add null coalescing: `$userData['is_active'] ?? true`

### Bug #11 - Double ViewRenderer Registration 🟠
**Files**: `src/Core/Container.php` + `src/bootstrap.php`
**Issue**: ViewRenderer registered twice with different signatures
**Impact**: Conflicting registrations, breaks if bootstrap not loaded
**Fix Needed**: Remove duplicate registration from Container.php

### Bug #13 - auth.js checkPageAccess() Loop 🟠
**File**: `public/assets/js/auth.js`
**Issue**: Admin visiting /dashboard/employee gets bounced (infinite loop)
**Impact**: Admins can't preview employee view
**Fix Needed**: Fix role check logic to allow admin access to all pages

### Bug #14 - logActivity() Type Mismatch 🟠
**File**: `src/Services/AuthService.php`
**Issue**: Method signature `int $userId` but Supabase returns string UUIDs
**Impact**: UUID cast to 0, all audit logs have user_id = 0
**Fix Needed**: Change signature to `string $userId`

### Bug #15 - absentToday Counted Twice 🟠
**File**: `src/Controllers/DashboardController.php`
**Issue**: `$absentToday` calculated from loop, then immediately overwritten
**Impact**: Incorrect absent count on dashboard
**Fix Needed**: Remove duplicate calculation or fix logic

---

## 🟡 MEDIUM SEVERITY BUGS (Design Issues)

### Bug #16 - AnnouncementService Not Registered 🟡
**File**: `src/bootstrap.php`
**Issue**: AnnouncementService not in singleton registration list
**Impact**: New instance created on every call (no caching)
**Fix Needed**: Add `$container->singleton(\Services\AnnouncementService::class);`

### Bug #17 - php://input Consumed Twice 🟡
**File**: `src/Core/Request.php`
**Issue**: Constructor reads php://input, getBody() may read again
**Impact**: Second read returns empty string on some servers
**Fix Needed**: Cache body in constructor, return cached value in getBody()

### Bug #18 - Stale Route Cache 🟡
**File**: `src/Core/Router.php`
**Issue**: Cached routes loaded, new routes added, cache never updated
**Impact**: Stale routes matched before new ones
**Fix Needed**: Clear cache on route addition or don't load cache in dev

### Bug #20 - auth.js hasRole() Checks Two Locations 🟡
**File**: `public/assets/js/auth.js`
**Issue**: Checks both `user.user_metadata.role` and `user.role`
**Impact**: Role lookup inconsistent depending on auth path
**Fix Needed**: Standardize role location in user object

### Bug #21 - SupabaseConnection update() Always Returns 1 🟡
**File**: `src/Core/SupabaseConnection.php`
**Issue**: Returns 1 on success regardless of actual rows affected
**Impact**: Can't detect if update matched any records
**Fix Needed**: Return actual affected row count from Supabase response

### Bug #22 - Demo Credentials Wrong Password 🟡
**File**: `src/Views/auth/login.php`
**Issue**: Shows `admin123` but actual password is `Admin123!`
**Impact**: Demo quickfill always fails
**Fix Needed**: Update demo credentials display to match actual password

---

## Priority Fix Order

### Phase 1: Critical Crashes (Must Fix First)
1. Bug #3 - Duplicate Exception Classes
2. Bug #6 - RoleMiddleware Database Calls
3. Bug #4 - base.php $data Access
4. Bug #2 - Login Response Nesting
5. Bug #1 - Form method="POST"

### Phase 2: High Severity Logic Bugs
6. Bug #15 - absentToday Double Count
7. Bug #10 - is_active Field Check
8. Bug #14 - UUID Type Mismatch
9. Bug #13 - checkPageAccess Loop
10. Bug #11 - Double ViewRenderer
11. Bug #9 - Array Conditions
12. Bug #8 - Empty Array Check

### Phase 3: Medium Severity Design Issues
13. Bug #22 - Demo Password
14. Bug #16 - AnnouncementService Registration
15. Bug #20 - hasRole() Dual Check
16. Bug #17 - php://input Double Read
17. Bug #18 - Stale Route Cache
18. Bug #21 - update() Return Value

---

## Files That Need Attention

### Critical Files
1. `src/Core/Controller.php` - Remove duplicate exception classes
2. `src/Core/ErrorHandler.php` - Keep exception classes here
3. `src/Views/layouts/base.php` - Fix $data access after extract()
4. `src/Views/auth/login.php` - Add method="POST", fix demo password
5. `src/Controllers/AuthController.php` - Fix response nesting
6. `src/Middleware/RoleMiddleware.php` - Fix database calls
7. `public/assets/js/auth.js` - Fix response reading, checkPageAccess logic

### High Priority Files
8. `src/Controllers/DashboardController.php` - Fix absentToday calculation
9. `src/Middleware/AuthMiddleware.php` - Fix is_active check
10. `src/Services/AuthService.php` - Fix UUID type signature
11. `src/Core/SupabaseConnection.php` - Fix array conditions, update() return
12. `src/bootstrap.php` - Add AnnouncementService, fix ViewRenderer

### Medium Priority Files
13. `src/Core/Request.php` - Cache php://input
14. `src/Core/Router.php` - Fix route cache logic
15. `src/Core/Container.php` - Remove duplicate ViewRenderer

---

## Estimated Impact

**Already Fixed**: 4 bugs (Asset paths, navigation links, redirects)
**Still Remaining**: 18 bugs
- 🔴 Critical: 6 bugs (will crash or break core functionality)
- 🟠 High: 8 bugs (wrong behavior, data corruption)
- 🟡 Medium: 4 bugs (design issues, edge cases)

**Total Files to Modify**: ~15 files
**Estimated Lines to Change**: ~200-300 lines

---

## Next Steps

1. Create a new bugfix spec for the remaining 18 bugs
2. Prioritize critical crashes first (Phase 1)
3. Test each fix thoroughly
4. Verify no regressions from previous fixes
5. Update documentation

