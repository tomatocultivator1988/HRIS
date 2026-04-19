# 📊 System Health Dashboard - User Guide

## What Is It?

A visual monitoring dashboard for developers and system administrators to monitor system health, errors, performance, and rate limiting in real-time.

---

## 🔗 How to Access

**URL:** `http://localhost/system/health-dashboard`

**Access Method:** Manual URL entry (no menu link - hidden from regular users)

**Authentication:** None required (but keep the URL secret!)

---

## 📈 What It Shows

### 1. **Overall System Status**
- ✅ Healthy - Everything working fine
- ⚠️ Warning - Some issues detected
- ❌ Unhealthy - Critical problems

### 2. **Error Statistics**
- 🔴 Critical Errors - Severe issues
- 🟠 Errors - Standard errors
- 🟡 Warnings - Minor issues
- Recent error messages with timestamps

### 3. **Performance Metrics**
- Slow requests (>1000ms)
- Recent slow operations
- Response time tracking

### 4. **Rate Limiting Stats**
- Active clients being tracked
- Blocked IPs
- List of currently blocked addresses
- Rate limit configuration

### 5. **Disk Space**
- Total disk space
- Free space available
- Usage percentage
- Warning if >80% used

### 6. **System Information**
- PHP version
- Server software
- Memory limit
- Max execution time
- Timezone
- Current server time

### 7. **Health Checks**
- Database connectivity
- Disk space status
- Logs directory writable

---

## 🎯 Use Cases

### Scenario 1: System is Slow
1. Open dashboard
2. Check "Performance" section
3. See which operations are slow
4. Check "Recent Slow Requests"
5. Identify bottleneck

### Scenario 2: Errors Happening
1. Open dashboard
2. Check "Errors" section
3. See error count
4. Read "Recent Errors"
5. Fix the issue

### Scenario 3: Under Attack
1. Open dashboard
2. Check "Rate Limiting" section
3. See blocked IPs
4. Verify DDoS protection working

### Scenario 4: Disk Full
1. Open dashboard
2. Check "Disk Space" section
3. See usage percentage
4. Clean up if needed

---

## 🔄 Auto-Refresh

The dashboard automatically refreshes every **30 seconds** to show latest data.

**Manual Refresh:** Click the "Refresh" button at the bottom

---

## 📊 Dashboard Sections Explained

### Errors Section 🐛
Shows errors from `logs/app.log`:
- **Critical** - System-breaking errors
- **Errors** - Standard errors
- **Warnings** - Minor issues

**What to do:**
- If Critical > 0: Investigate immediately
- If Errors > 10: Check recent errors
- If Warnings > 50: Review and fix

### Performance Section ⚡
Shows slow operations from logs:
- **Slow Requests** - Operations taking >1000ms
- **Threshold** - Current slow request threshold

**What to do:**
- If Slow Requests > 10: Optimize code
- Check "Recent Slow Requests" for details

### Rate Limiting Section 🚦
Shows rate limiting activity:
- **Active Clients** - IPs being tracked
- **Blocked IPs** - Currently blocked addresses

**What to do:**
- If Blocked IPs > 0: Check if legitimate users
- Review blocked list for patterns

### Disk Space Section 💾
Shows disk usage:
- **Used %** - Percentage of disk used
- **Free** - Available space

**What to do:**
- If Used > 80%: Clean up logs/files
- If Used > 90%: Urgent cleanup needed

---

## 🚨 Alert Thresholds

| Metric | Warning | Critical |
|--------|---------|----------|
| Critical Errors | > 0 | > 5 |
| Errors | > 10 | > 50 |
| Slow Requests | > 10 | > 50 |
| Disk Usage | > 80% | > 90% |
| Blocked IPs | > 5 | > 20 |

---

## 🔧 Troubleshooting

### Dashboard Not Loading
**Problem:** 404 error or blank page

**Solution:**
1. Check if route is registered in `config/routes.php`
2. Check if controller exists: `src/Controllers/SystemHealthController.php`
3. Check if view exists: `src/Views/system/health-dashboard.php`
4. Clear browser cache

### No Data Showing
**Problem:** Dashboard loads but shows zeros

**Solution:**
1. Check if log files exist: `logs/app.log`
2. Check if logs directory is writable
3. Generate some activity (use the system)
4. Refresh the dashboard

### Errors Not Showing
**Problem:** You know there are errors but dashboard shows 0

**Solution:**
1. Check `logs/app.log` manually
2. Verify error logging is enabled
3. Check file permissions on logs directory
4. Trigger a test error

---

## 📝 Log Files Location

All logs are in the `logs/` directory:

```
logs/
├── app.log          # Main application log
├── audit.log        # User activity audit
├── debug.log        # Debug information
└── rate_limit.json  # Rate limiting data
```

**View logs manually:**
```bash
# View last 50 lines
tail -n 50 logs/app.log

# Follow log in real-time
tail -f logs/app.log

# Search for errors
grep "ERROR" logs/app.log
```

---

## 🎨 Dashboard Features

### Visual Indicators
- ✅ Green = Good
- ⚠️ Yellow = Warning
- ❌ Red = Critical

### Hover Effects
- Cards lift on hover
- Shows interactivity

### Auto-Refresh
- Updates every 30 seconds
- Shows "Last updated" timestamp

### Responsive Design
- Works on desktop
- Works on tablet
- Works on mobile

---

## 🔐 Security Notes

### Important:
- ⚠️ **No authentication required** - Keep URL secret!
- ⚠️ **Shows sensitive data** - Don't share publicly
- ⚠️ **For developers only** - Not for end users

### Recommendations:
1. Don't add link to main navigation
2. Don't share URL with non-technical users
3. Consider adding authentication later
4. Use only on internal network

---

## 🚀 Advanced Usage

### Integrate with Monitoring Tools

**UptimeRobot:**
1. Add `/health` endpoint to UptimeRobot
2. Get alerts if system goes down
3. Check dashboard for details

**Sentry:**
1. Set up Sentry (see SENTRY_SETUP_GUIDE.md)
2. Get email alerts for errors
3. Use dashboard for quick overview

**Custom Monitoring:**
```bash
# Check health via curl
curl http://localhost/health

# Get metrics as JSON
curl http://localhost/api/system/metrics
```

---

## 📊 Metrics API

**Endpoint:** `/api/system/metrics`

**Returns:** JSON with all metrics

**Example:**
```json
{
  "system": {
    "php_version": "8.2.12",
    "disk_free": "5.5 GB",
    "disk_used_percent": 45.2
  },
  "errors": {
    "total": 12,
    "critical": 0,
    "errors": 5,
    "warnings": 7
  },
  "performance": {
    "slow_requests": 3
  },
  "rate_limiting": {
    "active_clients": 15,
    "blocked_ips": 2
  },
  "health": {
    "status": "healthy"
  }
}
```

**Use case:** Build custom monitoring scripts

---

## 🎯 Best Practices

### Daily Monitoring
1. Check dashboard once per day
2. Look for critical errors
3. Monitor disk space
4. Review blocked IPs

### Weekly Review
1. Analyze error trends
2. Identify slow operations
3. Optimize bottlenecks
4. Clean up logs if needed

### Monthly Maintenance
1. Review all metrics
2. Archive old logs
3. Update thresholds
4. Document issues

---

## 🔗 Related Tools

- **Health Check API:** `/health`
- **Sentry Dashboard:** https://sentry.io
- **Log Files:** `logs/app.log`
- **Admin Dashboard:** `/dashboard/admin`

---

## 📞 Support

**Issues?**
1. Check logs: `logs/app.log`
2. Check health API: `/health`
3. Review this guide
4. Check GitHub issues

---

## ✅ Quick Checklist

Before using the dashboard:
- [ ] Dashboard accessible at `/system/health-dashboard`
- [ ] Logs directory exists and is writable
- [ ] Log files contain data
- [ ] System is generating activity
- [ ] Dashboard shows real data

---

**Dashboard URL:** `http://localhost/system/health-dashboard`

**Remember:** Keep this URL secret - it's for developers only! 🔒
