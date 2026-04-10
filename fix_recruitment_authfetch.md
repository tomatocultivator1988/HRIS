# Quick Fix for Recruitment AuthFetch Issues

## Problem
The recruitment page still has 4 places using `window.AuthManager.authFetch()` which causes "Session Expired" errors. These need to be changed to plain `fetch()` with manual token handling (like the employees page).

## Locations to Fix

### 1. Applicant Form Submission (line ~1028)
**Change from:**
```javascript
const response = await window.AuthManager.authFetch(url, {
    method: method,
    body: JSON.stringify({...})
});
```

**Change to:**
```javascript
const token = localStorage.getItem('hris_token');
if (!token) {
    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
    return;
}

const response = await fetch(url, {
    method: method,
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({...})
});
```

### 2. View Applicant Details (line ~1072)
**Change from:**
```javascript
const response = await window.AuthManager.authFetch(apiUrl);
```

**Change to:**
```javascript
const token = localStorage.getItem('hris_token');
if (!token) {
    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
    return;
}

const response = await fetch(apiUrl, {
    method: 'GET',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    }
});
```

### 3. Save Evaluation (line ~1294)
**Change from:**
```javascript
const response = await window.AuthManager.authFetch(apiUrl, {
    method: 'POST',
    body: JSON.stringify({...})
});
```

**Change to:**
```javascript
const token = localStorage.getItem('hris_token');
if (!token) {
    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
    return;
}

const response = await fetch(apiUrl, {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({...})
});
```

### 4. Hire Applicant (line ~1411)
**Change from:**
```javascript
const response = await window.AuthManager.authFetch(apiUrl, {
    method: 'POST',
    body: JSON.stringify({...})
});
```

**Change to:**
```javascript
const token = localStorage.getItem('hris_token');
if (!token) {
    window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
    return;
}

const response = await fetch(apiUrl, {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({...})
});
```

## Pattern to Follow
For EVERY `authFetch` call:
1. Add token check at the beginning of the function
2. Replace `window.AuthManager.authFetch(url, options)` with `fetch(url, options)`
3. Add headers object with Authorization and Content-Type
4. Keep everything else the same

## Why This Fix Works
- The employees page uses this pattern and works perfectly
- Plain `fetch()` doesn't have the buggy token refresh logic
- Manual token handling is more reliable
- Matches the existing working code pattern
