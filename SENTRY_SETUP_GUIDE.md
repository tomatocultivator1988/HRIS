# 🐛 Sentry Error Tracking Setup Guide

## What is Sentry?

Sentry is a **FREE** error tracking service that automatically captures and reports errors in your application. It's like having a 24/7 monitoring system that tells you when something breaks in production.

### Free Tier Includes:
- ✅ 5,000 errors per month
- ✅ 7-day error retention
- ✅ Email alerts
- ✅ Error grouping and deduplication
- ✅ Stack traces
- ✅ User context
- ✅ Performance monitoring (20% sample rate)

---

## Setup Instructions (5 minutes)

### Step 1: Sign Up for Sentry (FREE)

1. Go to https://sentry.io
2. Click "Get Started" or "Sign Up"
3. Create a free account (no credit card required!)
4. Choose "PHP" as your platform

### Step 2: Get Your DSN

After creating your project, Sentry will show you a DSN (Data Source Name). It looks like:

```
https://abc123def456@o123456.ingest.sentry.io/789012
```

Copy this DSN - you'll need it in the next step.

### Step 3: Add DSN to .env File

Open your `.env` file and add:

```env
# Sentry Error Tracking (FREE tier - 5,000 errors/month)
SENTRY_DSN=https://your_dsn_here@o123456.ingest.sentry.io/789012
```

Replace `your_dsn_here` with your actual DSN from Step 2.

### Step 4: Install Sentry SDK (Optional but Recommended)

#### Option A: Using Composer (Recommended)

```bash
composer require sentry/sentry
```

#### Option B: Manual Download (No Composer)

1. Download from: https://github.com/getsentry/sentry-php/releases/latest
2. Extract to `vendor/sentry/sentry/`
3. Add to your autoloader

#### Option C: Skip Installation (Graceful Degradation)

If you don't install the SDK, the system will still work! It will just log errors locally without sending them to Sentry. You can install it later.

### Step 5: Test It!

Restart your PHP server and check the logs:

```bash
# Look for this message in your error logs:
"Sentry error tracking initialized successfully"
```

Or trigger a test error:

```php
// Add this to any controller temporarily
throw new Exception('Test error for Sentry');
```

Then check your Sentry dashboard - you should see the error appear within seconds!

---

## What Gets Tracked?

### Automatically Tracked:
- ✅ All exceptions and errors
- ✅ Stack traces
- ✅ Request context (URL, method, headers)
- ✅ User context (if logged in)
- ✅ Server environment
- ✅ Performance metrics (20% sample rate)

### Automatically Filtered (Privacy):
- ❌ Passwords
- ❌ API keys
- ❌ Tokens
- ❌ Credit card numbers
- ❌ Social security numbers
- ❌ Any field with "password", "secret", "token" in the name

---

## How to Use

### Basic Usage (Automatic)

Sentry is already integrated! Just use your existing error handling:

```php
try {
    // Your code
} catch (Exception $e) {
    // This automatically sends to Sentry
    throw $e;
}
```

### Manual Error Capture

```php
// Capture an exception manually
SentryIntegration::captureException($exception, [
    'extra_context' => 'Some additional info'
]);

// Capture a message
SentryIntegration::captureMessage('Something went wrong', 'error', [
    'user_id' => $userId
]);

// Add breadcrumbs for debugging
SentryIntegration::addBreadcrumb('User clicked submit button', 'ui', [
    'form_id' => 'payroll_form'
]);

// Set user context
SentryIntegration::setUser($userId, $userEmail);

// Set custom tags
SentryIntegration::setTags([
    'environment' => 'production',
    'version' => '1.0.0'
]);
```

---

## Sentry Dashboard Features

### 1. Error Grouping
Sentry automatically groups similar errors together, so 100 identical errors show as 1 issue.

### 2. Email Alerts
Get notified immediately when new errors occur.

### 3. Error Trends
See graphs of error frequency over time.

### 4. Stack Traces
Full stack traces with file names and line numbers.

### 5. User Impact
See which users are affected by errors.

### 6. Performance Monitoring
Track slow operations and bottlenecks.

---

## Configuration Options

Add these to your `.env` file:

```env
# Sentry Configuration
SENTRY_DSN=your_dsn_here
APP_ENV=production  # or 'development', 'staging'

# Optional: Adjust sample rates (0.0 to 1.0)
SENTRY_TRACES_SAMPLE_RATE=0.2  # 20% of requests
SENTRY_PROFILES_SAMPLE_RATE=0.2  # 20% of requests
```

---

## Troubleshooting

### "Sentry SDK not installed"

This is OK! The system will work without Sentry. Install the SDK when you're ready:

```bash
composer require sentry/sentry
```

### "SENTRY_DSN not configured"

Add your DSN to the `.env` file (see Step 3 above).

### Errors not appearing in Sentry

1. Check your DSN is correct
2. Check your internet connection
3. Check Sentry dashboard filters (might be filtering out errors)
4. Look for "Failed to send exception to Sentry" in your error logs

### Too many errors (hitting free tier limit)

1. Fix the most common errors first
2. Add filters to ignore non-critical errors
3. Upgrade to paid tier ($26/month for 50,000 errors)

---

## Cost Breakdown

| Tier | Errors/Month | Cost | Best For |
|------|--------------|------|----------|
| Free | 5,000 | $0 | Small apps, testing |
| Team | 50,000 | $26/mo | Growing apps |
| Business | 100,000+ | $80+/mo | Large apps |

**Recommendation:** Start with FREE tier. You can always upgrade later if needed.

---

## Privacy & Security

### What Sentry Does:
- ✅ Captures error messages and stack traces
- ✅ Captures request URLs and methods
- ✅ Captures server environment info

### What Sentry Does NOT Capture:
- ❌ Passwords (automatically filtered)
- ❌ API keys (automatically filtered)
- ❌ Credit card numbers (automatically filtered)
- ❌ Personal data (unless you explicitly send it)

### Data Location:
- Sentry stores data in the US (or EU if you choose EU region)
- Data is encrypted in transit and at rest
- 7-day retention on free tier

---

## Alternative: Self-Hosted Sentry

If you want complete control, you can self-host Sentry:

1. Install Docker
2. Run: `docker run -d --name sentry sentry`
3. Use your own server's URL as the DSN

**Note:** Self-hosting requires more setup and maintenance.

---

## Next Steps

1. ✅ Sign up for Sentry (FREE)
2. ✅ Add DSN to `.env`
3. ✅ Install SDK (optional)
4. ✅ Test with a sample error
5. ✅ Configure email alerts
6. ✅ Monitor your dashboard

---

## Support

- Sentry Docs: https://docs.sentry.io/platforms/php/
- Sentry Support: https://sentry.io/support/
- Community: https://discord.gg/sentry

---

**🎉 That's it! You now have FREE error tracking for your HRIS system!**

Sentry will automatically catch and report errors, so you'll know about problems before your users complain.
