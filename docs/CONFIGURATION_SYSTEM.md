# Configuration Management System

## Overview

The MVC framework includes a comprehensive configuration management system that provides centralized configuration handling, environment-specific settings, and secure configuration loading with Supabase integration.

## Features

- **Singleton Pattern**: Ensures single instance of configuration manager
- **Environment Detection**: Automatic environment detection (development, production, testing)
- **Environment Variables**: Support for `.env` file and environment variable overrides
- **Configuration Files**: Support for multiple configuration files with environment-specific overrides
- **Supabase Integration**: Built-in Supabase configuration management
- **Helper Functions**: Global helper functions for easy configuration access
- **Validation**: Configuration validation for required keys
- **Caching**: Configuration caching for improved performance

## Configuration Files

### Main Configuration Files

- `config/app.php` - Application settings
- `config/database.php` - Database connection settings
- `config/supabase.php` - Supabase-specific configuration (legacy)

### Environment-Specific Overrides

- `config/app.development.php` - Development-specific app settings
- `config/app.production.php` - Production-specific app settings
- `config/database.development.php` - Development database settings
- `config/database.production.php` - Production database settings

## Environment Variables

The system supports the following environment variables in `.env` file:

### Application Settings
```env
APP_ENV=development
APP_NAME="HRIS System"
APP_DEBUG=true
APP_URL=http://localhost
APP_KEY=your-secret-key-here
APP_TIMEZONE=UTC
```

### Database Configuration
```env
DB_CONNECTION=supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key
```

### Table Names
```env
TABLE_EMPLOYEES=employees
TABLE_ADMINS=admins
TABLE_ATTENDANCE=attendance
TABLE_LEAVE_TYPES=leave_types
TABLE_LEAVE_REQUESTS=leave_requests
TABLE_LEAVE_CREDITS=leave_credits
TABLE_ANNOUNCEMENTS=announcements
TABLE_WORK_CALENDAR=work_calendar
TABLE_USER_SESSIONS=user_sessions
TABLE_AUDIT_LOG=system_audit_log
```

### Security Settings
```env
JWT_SECRET=your-jwt-secret
JWT_TTL=3600
SESSION_TIMEOUT=3600
CSRF_TOKEN_EXPIRY=1800
```

### API Configuration
```env
API_RATE_LIMIT=100
API_TIMEOUT=30
SUPABASE_TIMEOUT=30
SUPABASE_SSL_VERIFY=true
SUPABASE_RETRY_ATTEMPTS=3
SUPABASE_RETRY_DELAY=1000
```

## Usage

### Basic Configuration Access

```php
use Config\ConfigManager;

// Get configuration manager instance
$config = ConfigManager::getInstance();

// Get configuration value with dot notation
$appName = $config->get('app.name');
$dbHost = $config->get('database.connections.mysql.host');

// Get with default value
$debugMode = $config->get('app.debug', false);

// Check if configuration exists
if ($config->has('app.key')) {
    // Configuration exists
}
```

### Using Helper Functions

```php
// Include helper functions
require_once 'src/Config/helpers.php';

// Get configuration values
$appName = config('app.name');
$dbConnection = config('database.default');

// Get environment variables
$appEnv = env('APP_ENV', 'production');
$debugMode = env('APP_DEBUG', false);

// Environment checks
if (is_development()) {
    // Development-specific code
}

if (is_production()) {
    // Production-specific code
}

// Supabase configuration
$supabaseUrl = supabase_config('url');
$employeeTable = table_name('employees');
```

### Container Integration

```php
use Core\Container;

// Get container instance
$container = Container::getInstance();
$container->registerDefaultBindings();

// Resolve configuration manager
$config = $container->resolve('ConfigManager');

// Resolve Supabase connection
$supabase = $container->resolve('SupabaseConnection');
```

### Model Integration

```php
use Core\Model;
use Core\SupabaseConnection;

class Employee extends Model
{
    protected string $table = 'employees';
    protected array $fillable = ['employee_id', 'first_name', 'last_name'];
    
    public function __construct(SupabaseConnection $db)
    {
        parent::__construct($db);
    }
}

// Usage with container
$container = Container::getInstance();
$employee = new Employee($container->resolve('SupabaseConnection'));
```

## Configuration Structure

### Application Configuration (`config/app.php`)

```php
return [
    'name' => env('APP_NAME', 'HRIS System'),
    'version' => '2.0.0',
    'environment' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    
    // Security settings
    'key' => env('APP_KEY', 'your-secret-key-here'),
    'cipher' => 'AES-256-CBC',
    
    // JWT settings
    'jwt' => [
        'secret' => env('JWT_SECRET', env('APP_KEY')),
        'ttl' => env('JWT_TTL', 3600),
        'refresh_ttl' => env('JWT_REFRESH_TTL', 86400),
        'algorithm' => 'HS256',
    ],
    
    // Session settings
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'timeout' => env('SESSION_TIMEOUT', 3600),
        'cookie' => env('SESSION_COOKIE', 'hris_session'),
    ],
    
    // API settings
    'api' => [
        'rate_limit' => env('API_RATE_LIMIT', 100),
        'timeout' => env('API_TIMEOUT', 30),
        'version' => 'v1',
        'prefix' => 'api',
    ],
];
```

### Database Configuration (`config/database.php`)

```php
return [
    'default' => env('DB_CONNECTION', 'supabase'),
    
    'connections' => [
        'supabase' => [
            'driver' => 'supabase',
            'url' => env('SUPABASE_URL'),
            'anon_key' => env('SUPABASE_ANON_KEY'),
            'service_key' => env('SUPABASE_SERVICE_KEY'),
            'tables' => [
                'employees' => env('TABLE_EMPLOYEES', 'employees'),
                'admins' => env('TABLE_ADMINS', 'admins'),
                // ... other tables
            ],
        ],
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'hris_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ],
    ],
];
```

## Advanced Features

### Configuration Validation

```php
// Validate required configuration keys
$config->validateRequired([
    'app.name',
    'app.key',
    'database.connections.supabase.url',
    'database.connections.supabase.anon_key'
]);
```

### Configuration Caching

```php
// Cache configuration for performance
$config->cache();

// Load cached configuration
if ($config->loadCache()) {
    // Configuration loaded from cache
}

// Clear configuration cache
$config->clearCache();
```

### Environment-Specific Configuration

Create environment-specific configuration files:

- `config/app.development.php`
- `config/app.production.php`
- `config/database.testing.php`

These files will automatically override the base configuration based on the current environment.

### Custom Configuration Files

Create custom configuration files in the `config/` directory:

```php
// config/services.php
return [
    'email' => [
        'driver' => env('MAIL_DRIVER', 'smtp'),
        'host' => env('MAIL_HOST', 'localhost'),
    ],
    'sms' => [
        'driver' => env('SMS_DRIVER', 'twilio'),
        'api_key' => env('SMS_API_KEY'),
    ],
];
```

Access custom configuration:

```php
$emailDriver = config('services.email.driver');
$smsApiKey = config('services.sms.api_key');
```

## Security Considerations

1. **Environment Variables**: Store sensitive data in environment variables, not in configuration files
2. **File Permissions**: Ensure configuration files have appropriate permissions
3. **Production Settings**: Use secure settings in production (debug=false, secure keys)
4. **Key Management**: Use strong, unique keys for APP_KEY and JWT_SECRET
5. **Database Credentials**: Never commit database credentials to version control

## Troubleshooting

### Common Issues

1. **Configuration Not Loading**
   - Check file permissions
   - Verify file paths
   - Ensure proper PHP syntax in configuration files

2. **Environment Variables Not Working**
   - Check `.env` file exists and is readable
   - Verify environment variable names match exactly
   - Ensure no spaces around `=` in `.env` file

3. **Supabase Connection Issues**
   - Verify SUPABASE_URL, SUPABASE_ANON_KEY, and SUPABASE_SERVICE_KEY are set
   - Check network connectivity
   - Validate Supabase project settings

### Debug Configuration

```php
// Get all configuration
$allConfig = $config->getAll('app');

// Get current environment
$environment = $config->getEnvironment();

// Check if specific configuration exists
if ($config->has('database.connections.supabase.url')) {
    echo "Supabase URL is configured";
}
```

## Testing

Run the configuration system tests:

```bash
php test_config_system.php
php test_model_supabase.php
```

These tests verify:
- Configuration manager functionality
- Environment variable loading
- Supabase integration
- Model base class integration
- Helper functions
- Container integration