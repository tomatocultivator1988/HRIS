# 🚀 HRIS MVP - Render.com Deployment Guide

## Prerequisites
- GitHub account
- Render.com account (free)
- Supabase project with credentials

---

## 📋 Step-by-Step Deployment

### Step 1: Push Code to GitHub

1. **Initialize Git** (if not already done):
```bash
git init
git add .
git commit -m "Initial commit - Ready for deployment"
```

2. **Create GitHub Repository**:
   - Go to https://github.com/new
   - Name: `hris-mvp` (or any name you want)
   - Make it **Private** (recommended for business apps)
   - Don't initialize with README (we already have code)
   - Click "Create repository"

3. **Push to GitHub**:
```bash
git remote add origin https://github.com/YOUR_USERNAME/hris-mvp.git
git branch -M main
git push -u origin main
```

---

### Step 2: Create Render.com Account

1. Go to https://render.com
2. Click **"Get Started"**
3. Sign up with **GitHub** (easiest option)
4. Authorize Render to access your GitHub repositories

---

### Step 3: Deploy on Render

1. **In Render Dashboard**:
   - Click **"New +"** button (top right)
   - Select **"Web Service"**

2. **Connect Repository**:
   - Find your `hris-mvp` repository
   - Click **"Connect"**

3. **Configure Service**:
   ```
   Name: hris-mvp (or your preferred name)
   Region: Singapore (closest to Philippines)
   Branch: main
   Runtime: Docker
   Instance Type: Free
   ```

4. **Add Environment Variables**:
   Click **"Advanced"** → **"Add Environment Variable"**
   
   Add these variables:
   ```
   SUPABASE_URL = https://your-project.supabase.co
   SUPABASE_ANON_KEY = your_anon_key_here
   SUPABASE_SERVICE_ROLE_KEY = your_service_role_key_here
   APP_ENV = production
   APP_DEBUG = false
   ```

   **Where to get Supabase keys:**
   - Go to your Supabase project
   - Settings → API
   - Copy URL, anon key, and service_role key

5. **Create Web Service**:
   - Click **"Create Web Service"**
   - Wait 5-10 minutes for initial deployment

---

### Step 4: Verify Deployment

1. **Check Build Logs**:
   - Watch the logs in Render dashboard
   - Should see "Build successful" and "Service is live"

2. **Access Your App**:
   - Your app URL: `https://hris-mvp.onrender.com`
   - Click the URL to open your app
   - Try logging in with demo credentials

3. **Test Functionality**:
   - Login as admin: `admin@company.com` / `Admin123!`
   - Check if all pages load
   - Test attendance, leave, reports

---

## 🔧 Configuration Updates Needed

### Update Base URL in config.js

After deployment, you need to update the base URL:

1. **Edit `public/assets/js/config.js`**:
```javascript
const AppConfig = {
    baseUrl: 'https://hris-mvp.onrender.com', // Change this to your Render URL
    apiBaseUrl: 'https://hris-mvp.onrender.com/api',
    // ... rest of config
};
```

2. **Commit and push**:
```bash
git add public/assets/js/config.js
git commit -m "Update base URL for production"
git push
```

3. **Render will auto-deploy** the changes

---

## ⚠️ Important Notes

### Free Tier Limitations:
- **Sleeps after 15 minutes** of inactivity
- **Wakes up in ~30 seconds** when accessed
- **750 hours/month** free (enough for 24/7 if you upgrade to paid)

### To Keep App Awake (Optional):
Use a free uptime monitoring service:
- **UptimeRobot** (https://uptimerobot.com) - Free
- **Cron-job.org** (https://cron-job.org) - Free
- Ping your app every 10 minutes

### Custom Domain (Optional):
1. In Render dashboard → Settings → Custom Domain
2. Add your domain
3. Update DNS records as instructed
4. Free SSL certificate included

---

## 🐛 Troubleshooting

### Build Failed:
- Check build logs in Render dashboard
- Verify Dockerfile syntax
- Ensure all files are committed to GitHub

### App Not Loading:
- Check if service is "Live" in Render
- Verify environment variables are set correctly
- Check logs for PHP errors

### Database Connection Issues:
- Verify Supabase credentials
- Check if Supabase project is active
- Test Supabase connection from local first

### 500 Internal Server Error:
- Check Render logs for PHP errors
- Verify file permissions
- Check .htaccess configuration

---

## 📊 Monitoring

### View Logs:
1. Go to Render dashboard
2. Click your service
3. Click "Logs" tab
4. See real-time logs

### Metrics:
- CPU usage
- Memory usage
- Request count
- Response times

---

## 🔄 Updating Your App

### Automatic Deployment:
Every time you push to GitHub, Render automatically deploys:

```bash
# Make changes to your code
git add .
git commit -m "Your update message"
git push

# Render will automatically detect and deploy
```

### Manual Deployment:
1. Go to Render dashboard
2. Click your service
3. Click "Manual Deploy" → "Deploy latest commit"

---

## 💰 Upgrading to Paid (Optional)

If you need better performance:

**Starter Plan ($7/month)**:
- No sleep
- 512 MB RAM
- 0.5 CPU
- Better for production

**Standard Plan ($25/month)**:
- 2 GB RAM
- 1 CPU
- Best for production

---

## 🎯 Next Steps After Deployment

1. ✅ Test all features thoroughly
2. ✅ Set up uptime monitoring
3. ✅ Configure custom domain (optional)
4. ✅ Set up backups for Supabase
5. ✅ Monitor logs regularly
6. ✅ Update documentation with production URL

---

## 📞 Support

**Render Support:**
- Docs: https://render.com/docs
- Community: https://community.render.com

**HRIS MVP Issues:**
- Check logs first
- Review Supabase connection
- Verify environment variables

---

## ✅ Deployment Checklist

- [ ] Code pushed to GitHub
- [ ] Render account created
- [ ] Web service created
- [ ] Environment variables added
- [ ] Build successful
- [ ] App accessible via URL
- [ ] Login working
- [ ] Database connection working
- [ ] All features tested
- [ ] Base URL updated in config
- [ ] Uptime monitoring set up (optional)
- [ ] Custom domain configured (optional)

---

**Congratulations! Your HRIS MVP is now live! 🎉**
