# ⚡ Quick Deploy to Render.com

## 🚀 5-Minute Deployment

### 1️⃣ Push to GitHub (2 min)
```bash
git init
git add .
git commit -m "Ready for deployment"
git remote add origin https://github.com/YOUR_USERNAME/hris-mvp.git
git push -u origin main
```

### 2️⃣ Deploy on Render (3 min)
1. Go to https://render.com
2. Sign up with GitHub
3. Click "New +" → "Web Service"
4. Connect your `hris-mvp` repo
5. Settings:
   - **Runtime**: Docker
   - **Instance**: Free
6. Add Environment Variables:
   ```
   SUPABASE_URL = https://xxxxx.supabase.co
   SUPABASE_ANON_KEY = eyJxxx...
   SUPABASE_SERVICE_ROLE_KEY = eyJxxx...
   APP_ENV = production
   ```
7. Click "Create Web Service"
8. Wait 5-10 minutes ⏳

### 3️⃣ Done! 🎉
Your app is live at: `https://hris-mvp.onrender.com`

---

## 📝 After Deployment

Update base URL in `public/assets/js/config.js`:
```javascript
baseUrl: 'https://hris-mvp.onrender.com'
```

Then push:
```bash
git add .
git commit -m "Update production URL"
git push
```

---

## ⚠️ Remember
- Free tier sleeps after 15 min (wakes in 30 sec)
- 750 hours/month free
- Auto-deploys on every push

---

## 🆘 Issues?
Check full guide: `DEPLOYMENT_GUIDE.md`
