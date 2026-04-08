# 🏢 HRIS MVP - Human Resource Information System

A modern, full-featured HRIS system built with PHP and Supabase.

## ✨ Features

### 👥 Employee Management
- Complete employee profiles
- Department and position tracking
- Employee search and filtering
- Active/inactive status management

### ⏰ Attendance Tracking
- Time-in/time-out recording
- Automatic absence detection
- Work hours calculation
- Late/present/absent status tracking
- Attendance history and reports

### 🏖️ Leave Management
- Leave request submission
- Multi-level approval workflow
- Leave balance tracking
- Leave type management (Sick, Vacation, etc.)
- Leave credits system

### 📊 Reports & Analytics
- Attendance reports with charts
- Leave analytics
- Employee statistics
- Productivity metrics
- Exportable data

### 🔐 Security
- Role-based access control (Admin/Employee)
- Secure authentication with JWT
- Password management
- Force password change on first login
- Session management

## 🛠️ Tech Stack

- **Backend**: PHP 8.2 (Custom MVC Framework)
- **Database**: Supabase (PostgreSQL)
- **Frontend**: Vanilla JavaScript, Tailwind CSS
- **Server**: Apache with mod_rewrite
- **Authentication**: JWT tokens

## 📋 Requirements

- PHP 8.0 or higher
- Apache with mod_rewrite enabled
- Supabase account
- Git

## 🚀 Quick Start

### Local Development

1. **Clone the repository**:
```bash
git clone https://github.com/YOUR_USERNAME/hris-mvp.git
cd hris-mvp
```

2. **Configure environment**:
```bash
cp .env.example .env
# Edit .env with your Supabase credentials
```

3. **Set up Supabase**:
   - Create a Supabase project
   - Run the SQL migrations in `docs/migrations/`
   - Copy your Supabase URL and keys to `.env`

4. **Configure Apache**:
   - Point document root to `public/` folder
   - Ensure mod_rewrite is enabled
   - Restart Apache

5. **Access the app**:
   - Open http://localhost/HRIS
   - Login with demo credentials:
     - Admin: `admin@company.com` / `Admin123!`
     - Employee: `employee@company.com` / `emp123`

### Deploy to Render.com

See [QUICK_DEPLOY.md](QUICK_DEPLOY.md) for 5-minute deployment guide.

Full deployment guide: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)

## 📁 Project Structure

```
HRIS/
├── public/              # Public web root
│   ├── assets/         # CSS, JS, images
│   ├── index.php       # Entry point
│   └── .htaccess       # Apache rewrite rules
├── src/
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Database models
│   ├── Services/       # Business logic
│   ├── Views/          # HTML templates
│   └── Core/           # Framework core
├── config/             # Configuration files
├── docs/               # Documentation & migrations
├── Dockerfile          # Docker configuration
└── render.yaml         # Render deployment config
```

## 🔧 Configuration

### Supabase Setup

1. Create tables using migrations in `docs/migrations/`
2. Set up Row Level Security (RLS) policies
3. Create test users with `docs/create-test-users.sql`

### Environment Variables

Required environment variables:
- `SUPABASE_URL` - Your Supabase project URL
- `SUPABASE_ANON_KEY` - Supabase anonymous key
- `SUPABASE_SERVICE_ROLE_KEY` - Supabase service role key
- `APP_ENV` - Application environment (development/production)
- `APP_DEBUG` - Debug mode (true/false)

## 📖 Documentation

- [Deployment Guide](DEPLOYMENT_GUIDE.md) - Full deployment instructions
- [Quick Deploy](QUICK_DEPLOY.md) - 5-minute deployment
- [Database Schema](docs/database-schema.sql) - Database structure
- [API Documentation](docs/API.md) - API endpoints (if available)

## 🧪 Testing

Demo credentials for testing:

**Admin Account:**
- Email: `admin@company.com`
- Password: `Admin123!`

**Employee Account:**
- Email: `employee@company.com`
- Password: `emp123`

## 🐛 Troubleshooting

### Common Issues

**404 Errors:**
- Ensure mod_rewrite is enabled
- Check .htaccess file exists in public/
- Verify Apache configuration

**Database Connection:**
- Verify Supabase credentials
- Check if Supabase project is active
- Test connection from Supabase dashboard

**Login Issues:**
- Clear browser cache and localStorage
- Check if test users exist in database
- Verify JWT token generation

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 👨‍💻 Author

Built with ❤️ for modern HR management

## 🙏 Acknowledgments

- Supabase for the backend infrastructure
- Tailwind CSS for the UI framework
- Chart.js for data visualization

## 📞 Support

For issues and questions:
- Check the documentation
- Review troubleshooting guide
- Open an issue on GitHub

---

**Made with PHP & Supabase** 🚀
