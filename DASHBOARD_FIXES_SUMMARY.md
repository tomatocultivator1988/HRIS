# Dashboard and Login Fixes Summary

## Issues Found

### 1. Charts Not Loading on Dashboard
**Problems:**
- Chart.js library not included (CDN missing)
- Chart element IDs mismatch (admin.php vs charts.js)
- No JavaScript code to load metrics via API
- Metrics passed as empty array from controller

**Solutions Needed:**
1. Add Chart.js CDN to base layout
2. Fix chart element IDs to match
3. Add JavaScript to load metrics via API call
4. Update dashboard to load metrics dynamically

### 2. No Loading Screen on Login
**Problem:**
- Login button shows "Signing In..." text but no visual spinner/loading indicator

**Solution:**
- The loading spinner HTML exists but might not be styled properly
- Need to verify CSS for `.loading-spinner` class

## Quick Fixes

### Fix 1: Add Chart.js CDN to Base Layout
Add to `src/Views/layouts/base.php` in `<head>`:
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

### Fix 2: Update Admin Dashboard View
Change chart IDs in `src/Views/dashboard/admin.php`:
```html
<!-- Before -->
<div id="departmentChart"></div>
<div id="attendanceChart"></div>

<!-- After -->
<div><canvas id="department-chart"></canvas></div>
<div><canvas id="attendance-trend-chart"></canvas></div>
```

### Fix 3: Add JavaScript to Load Metrics
Add to admin dashboard view:
```javascript
<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Load metrics from API
        const response = await fetch(window.AppConfig.apiUrl('dashboard/metrics'), {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('hris_token')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update metric cards
            document.querySelector('[data-metric="totalEmployees"]').textContent = data.data.metrics.totalEmployees;
            document.querySelector('[data-metric="presentToday"]').textContent = data.data.metrics.presentToday;
            document.querySelector('[data-metric="lateToday"]').textContent = data.data.metrics.lateToday;
            document.querySelector('[data-metric="absentToday"]').textContent = data.data.metrics.absentToday;
            
            // Update charts
            if (data.data.charts) {
                window.DashboardCharts.updateDepartmentChart(data.data.charts.departments);
                window.DashboardCharts.updateAttendanceTrend(data.data.charts.attendanceTrend);
            }
        }
    } catch (error) {
        console.error('Failed to load dashboard metrics:', error);
    }
});
</script>
```

### Fix 4: Verify Loading Spinner CSS
Check if `public/assets/css/custom.css` has:
```css
.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
```

## Implementation Priority

1. **HIGH**: Add Chart.js CDN (required for charts to work)
2. **HIGH**: Fix chart element IDs and add canvas tags
3. **HIGH**: Add JavaScript to load metrics via API
4. **MEDIUM**: Verify/add loading spinner CSS
5. **LOW**: Add loading skeleton for dashboard metrics

## Testing Checklist

After fixes:
- [ ] Login shows loading spinner when clicking "Sign In"
- [ ] Dashboard loads without errors
- [ ] Metric cards show actual numbers (not 0)
- [ ] Department chart displays pie chart
- [ ] Attendance trend chart displays line chart
- [ ] No console errors
- [ ] Charts are responsive

## Files to Modify

1. `src/Views/layouts/base.php` - Add Chart.js CDN
2. `src/Views/dashboard/admin.php` - Fix chart IDs, add data attributes, add metrics loading script
3. `public/assets/css/custom.css` - Verify loading spinner styles
4. `config/security.php` - Add cdn.jsdelivr.net to CSP for Chart.js

## Notes

- The dashboard controller already has `getDashboardMetrics()` and `getDashboardCharts()` methods
- The `/api/dashboard/metrics` endpoint exists and works
- Just need to connect the frontend to load data dynamically
