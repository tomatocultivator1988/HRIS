<?php

namespace Config;

/**
 * Configuration Manager
 * 
 * Centralized configuration management system with environment variable support
 * and secure configuration loading for the MVC framework.
 */
class ConfigManager
{
    private static ?ConfigManager $instance = null;
    private array $config = [];
    private array $loadedFiles = [];
    private string $environment;
    private string $configPath;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->configPath = dirname(__DIR__, 2) . '/config';
        $this->environment = $this->determineEnvironment();
        $this->loadEnvironmentFile();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): ConfigManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Determine current environment
     */
    private function determineEnvironment(): string
    {
        // Check environment variable first
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null;
        
        if ($env) {
            return $env;
        }

        // Check for .env file indicator
        if (file_exists(dirname(__DIR__, 2) . '/.env')) {
            return 'development';
        }

        // Default to production for security
        return 'production';
    }

    /**
     * Load environment file (.env)
     */
    private function loadEnvironmentFile(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Set environment variable if not already set
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Get configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        // Load config file if not already loaded
        $configFile = $keys[0];
        if (!isset($this->loadedFiles[$configFile])) {
            $this->loadConfigFile($configFile);
        }

        // Navigate through nested keys
        foreach ($keys as $segment) {
            if (is_array($config) && array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * Set configuration value
     * 
     * @param string $key Configuration key (dot notation supported)
     * @param mixed $value Configuration value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        // Navigate to the target location
        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config[array_shift($keys)] = $value;
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Load configuration file
     * 
     * @param string $filename Configuration file name (without .php extension)
     */
    private function loadConfigFile(string $filename): void
    {
        $configFile = $this->configPath . '/' . $filename . '.php';
        
        if (!file_exists($configFile)) {
            throw new \RuntimeException("Configuration file not found: {$configFile}");
        }

        // Load the configuration file
        $config = require $configFile;
        
        if (!is_array($config)) {
            throw new \RuntimeException("Configuration file must return an array: {$configFile}");
        }

        // Merge with existing configuration
        $this->config[$filename] = $config;
        $this->loadedFiles[$filename] = true;

        // Load environment-specific overrides if they exist
        $envConfigFile = $this->configPath . '/' . $filename . '.' . $this->environment . '.php';
        if (file_exists($envConfigFile)) {
            $envConfig = require $envConfigFile;
            if (is_array($envConfig)) {
                $this->config[$filename] = array_merge_recursive($this->config[$filename], $envConfig);
            }
        }
    }

    /**
     * Get all configuration for a specific file
     * 
     * @param string $filename Configuration file name
     * @return array Configuration array
     */
    public function getAll(string $filename): array
    {
        if (!isset($this->loadedFiles[$filename])) {
            $this->loadConfigFile($filename);
        }

        return $this->config[$filename] ?? [];
    }

    /**
     * Get current environment
     * 
     * @return string Current environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if running in development environment
     * 
     * @return bool True if development environment
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Check if running in production environment
     * 
     * @return bool True if production environment
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Check if running in testing environment
     * 
     * @return bool True if testing environment
     */
    public function isTesting(): bool
    {
        return $this->environment === 'testing';
    }

    /**
     * Get environment variable with fallback
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value
     * @return mixed Environment variable value or default
     */
    public function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        // Convert string boolean values
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }
        }

        return $value;
    }

    /**
     * Cache configuration for performance
     * 
     * @param string $cacheFile Cache file path
     */
    public function cache(string $cacheFile = null): void
    {
        if ($cacheFile === null) {
            $cacheFile = dirname(__DIR__, 2) . '/storage/cache/config.php';
        }

        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheContent = "<?php\n\nreturn " . var_export($this->config, true) . ";\n";
        file_put_contents($cacheFile, $cacheContent, LOCK_EX);
    }

    /**
     * Load cached configuration
     * 
     * @param string $cacheFile Cache file path
     * @return bool True if cache loaded successfully
     */
    public function loadCache(string $cacheFile = null): bool
    {
        if ($cacheFile === null) {
            $cacheFile = dirname(__DIR__, 2) . '/storage/cache/config.php';
        }

        if (!file_exists($cacheFile)) {
            return false;
        }

        $cachedConfig = require $cacheFile;
        if (is_array($cachedConfig)) {
            $this->config = $cachedConfig;
            return true;
        }

        return false;
    }

    /**
     * Clear configuration cache
     * 
     * @param string $cacheFile Cache file path
     */
    public function clearCache(string $cacheFile = null): void
    {
        if ($cacheFile === null) {
            $cacheFile = dirname(__DIR__, 2) . '/storage/cache/config.php';
        }

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Validate required configuration keys
     * 
     * @param array $requiredKeys Array of required configuration keys
     * @throws \RuntimeException If required keys are missing
     */
    public function validateRequired(array $requiredKeys): void
    {
        $missing = [];

        foreach ($requiredKeys as $key) {
            if (!$this->has($key)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \RuntimeException(
                'Missing required configuration keys: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Get database configuration with environment variable support
     * 
     * @return array Database configuration
     */
    public function getDatabaseConfig(): array
    {
        $config = $this->get('database', []);
        
        // Set default connection if not specified
        if (!isset($config['default'])) {
            $config['default'] = $this->env('DB_CONNECTION', 'supabase');
        }
        
        // Ensure connections array exists
        if (!isset($config['connections'])) {
            $config['connections'] = [];
        }
        
        // Override with environment variables if available
        if (isset($config['connections']['mysql'])) {
            $mysql = &$config['connections']['mysql'];
            $mysql['host'] = $this->env('DB_HOST', $mysql['host'] ?? 'localhost');
            $mysql['port'] = $this->env('DB_PORT', $mysql['port'] ?? '3306');
            $mysql['database'] = $this->env('DB_DATABASE', $mysql['database'] ?? 'hris_db');
            $mysql['username'] = $this->env('DB_USERNAME', $mysql['username'] ?? 'root');
            $mysql['password'] = $this->env('DB_PASSWORD', $mysql['password'] ?? '');
        }
        
        // Override Supabase configuration with environment variables
        if (!isset($config['connections']['supabase'])) {
            $config['connections']['supabase'] = [];
        }
        
        $supabase = &$config['connections']['supabase'];
        $supabase['driver'] = 'supabase';
        $supabase['url'] = $this->env('SUPABASE_URL', $supabase['url'] ?? '');
        $supabase['anon_key'] = $this->env('SUPABASE_ANON_KEY', $supabase['anon_key'] ?? '');
        $supabase['service_key'] = $this->env('SUPABASE_SERVICE_KEY', $supabase['service_key'] ?? '');
        
        // Add table names
        if (!isset($supabase['tables'])) {
            $supabase['tables'] = [];
        }
        
        $supabase['tables'] = array_merge($supabase['tables'], [
            'employees' => $this->env('TABLE_EMPLOYEES', 'employees'),
            'admins' => $this->env('TABLE_ADMINS', 'admins'),
            'attendance' => $this->env('TABLE_ATTENDANCE', 'attendance'),
            'leave_types' => $this->env('TABLE_LEAVE_TYPES', 'leave_types'),
            'leave_requests' => $this->env('TABLE_LEAVE_REQUESTS', 'leave_requests'),
            'leave_credits' => $this->env('TABLE_LEAVE_CREDITS', 'leave_credits'),
            'announcements' => $this->env('TABLE_ANNOUNCEMENTS', 'announcements'),
            'work_calendar' => $this->env('TABLE_WORK_CALENDAR', 'work_calendar'),
            'user_sessions' => $this->env('TABLE_USER_SESSIONS', 'user_sessions'),
            'audit_log' => $this->env('TABLE_AUDIT_LOG', 'system_audit_log'),
        ]);

        return $config;
    }

    /**
     * Get Supabase configuration with environment variable support
     * 
     * @return array Supabase configuration
     */
    public function getSupabaseConfig(): array
    {
        $dbConfig = $this->getDatabaseConfig();
        $defaultConnection = $dbConfig['default'] ?? 'supabase';
        
        // Get Supabase configuration from database connections
        if (isset($dbConfig['connections']['supabase'])) {
            $config = $dbConfig['connections']['supabase'];
        } else {
            // Fallback to direct supabase config if exists
            $config = $this->get('supabase', []);
        }
        
        // Override with environment variables if available
        $config['url'] = $this->env('SUPABASE_URL', $config['url'] ?? '');
        $config['anon_key'] = $this->env('SUPABASE_ANON_KEY', $config['anon_key'] ?? '');
        $config['service_key'] = $this->env('SUPABASE_SERVICE_KEY', $config['service_key'] ?? '');
        
        // API URLs
        $config['api_url'] = $config['url'] . '/rest/v1/';
        $config['auth_url'] = $config['url'] . '/auth/v1/';
        
        // Session Configuration
        $config['session_timeout'] = $this->env('SESSION_TIMEOUT', 3600);
        $config['jwt_expiry'] = $this->env('JWT_TTL', 3600);
        
        // Security Configuration
        $config['api_rate_limit'] = $this->env('API_RATE_LIMIT', 100);
        $config['csrf_token_expiry'] = $this->env('CSRF_TOKEN_EXPIRY', 1800);
        
        // Connection settings
        $config['timeout'] = $this->env('SUPABASE_TIMEOUT', 30);
        $config['ssl_verify'] = $this->env('SUPABASE_SSL_VERIFY', true);
        $config['retry_attempts'] = $this->env('SUPABASE_RETRY_ATTEMPTS', 3);
        $config['retry_delay'] = $this->env('SUPABASE_RETRY_DELAY', 1000);
        
        // Table names
        $config['tables'] = array_merge($config['tables'] ?? [], [
            'employees' => $this->env('TABLE_EMPLOYEES', 'employees'),
            'admins' => $this->env('TABLE_ADMINS', 'admins'),
            'attendance' => $this->env('TABLE_ATTENDANCE', 'attendance'),
            'leave_types' => $this->env('TABLE_LEAVE_TYPES', 'leave_types'),
            'leave_requests' => $this->env('TABLE_LEAVE_REQUESTS', 'leave_requests'),
            'leave_credits' => $this->env('TABLE_LEAVE_CREDITS', 'leave_credits'),
            'announcements' => $this->env('TABLE_ANNOUNCEMENTS', 'announcements'),
            'work_calendar' => $this->env('TABLE_WORK_CALENDAR', 'work_calendar'),
            'user_sessions' => $this->env('TABLE_USER_SESSIONS', 'user_sessions'),
            'audit_log' => $this->env('TABLE_AUDIT_LOG', 'system_audit_log'),
        ]);
        
        return $config;
    }

    /**
     * Get application configuration with environment variable support
     * 
     * @return array Application configuration
     */
    public function getAppConfig(): array
    {
        $config = $this->get('app', []);
        
        // Override with environment variables if available
        $config['name'] = $this->env('APP_NAME', $config['name'] ?? 'HRIS System');
        $config['version'] = $config['version'] ?? '2.0.0';
        $config['environment'] = $this->getEnvironment();
        $config['debug'] = $this->env('APP_DEBUG', $config['debug'] ?? false);
        $config['url'] = $this->env('APP_URL', $config['url'] ?? 'http://localhost');
        $config['key'] = $this->env('APP_KEY', $config['key'] ?? null);
        $config['timezone'] = $this->env('APP_TIMEZONE', $config['timezone'] ?? 'UTC');

        return $config;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}