<?php

/**
 * Configuration Helper Functions
 * 
 * Global helper functions for accessing configuration throughout the application
 */

use Config\ConfigManager;

if (!function_exists('config')) {
    /**
     * Get configuration value using dot notation
     * 
     * @param string|null $key Configuration key (null returns all config)
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    function config($key = null, $default = null)
    {
        $configManager = ConfigManager::getInstance();
        
        if ($key === null) {
            return $configManager;
        }
        
        return $configManager->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable with type conversion
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value
     * @return mixed Environment variable value or default
     */
    function env($key, $default = null)
    {
        return ConfigManager::getInstance()->env($key, $default);
    }
}

if (!function_exists('app_env')) {
    /**
     * Get current application environment
     * 
     * @return string Current environment
     */
    function app_env()
    {
        return ConfigManager::getInstance()->getEnvironment();
    }
}

if (!function_exists('is_development')) {
    /**
     * Check if running in development environment
     * 
     * @return bool True if development environment
     */
    function is_development()
    {
        return ConfigManager::getInstance()->isDevelopment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Check if running in production environment
     * 
     * @return bool True if production environment
     */
    function is_production()
    {
        return ConfigManager::getInstance()->isProduction();
    }
}

if (!function_exists('is_testing')) {
    /**
     * Check if running in testing environment
     * 
     * @return bool True if testing environment
     */
    function is_testing()
    {
        return ConfigManager::getInstance()->isTesting();
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get storage directory path
     * 
     * @param string $path Additional path to append
     * @return string Full storage path
     */
    function storage_path($path = '')
    {
        $basePath = dirname(__DIR__, 2) . '/storage';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get config directory path
     * 
     * @param string $path Additional path to append
     * @return string Full config path
     */
    function config_path($path = '')
    {
        $basePath = dirname(__DIR__, 2) . '/config';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get application base path
     * 
     * @param string $path Additional path to append
     * @return string Full base path
     */
    function base_path($path = '')
    {
        $basePath = dirname(__DIR__, 2);
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('public_path')) {
    /**
     * Get public directory path
     * 
     * @param string $path Additional path to append
     * @return string Full public path
     */
    function public_path($path = '')
    {
        $basePath = dirname(__DIR__, 2) . '/public';
        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }
}

if (!function_exists('cache_config')) {
    /**
     * Cache current configuration for performance
     * 
     * @param string|null $cacheFile Custom cache file path
     */
    function cache_config($cacheFile = null)
    {
        ConfigManager::getInstance()->cache($cacheFile);
    }
}

if (!function_exists('clear_config_cache')) {
    /**
     * Clear configuration cache
     * 
     * @param string|null $cacheFile Custom cache file path
     */
    function clear_config_cache($cacheFile = null)
    {
        ConfigManager::getInstance()->clearCache($cacheFile);
    }
}

if (!function_exists('validate_config')) {
    /**
     * Validate required configuration keys
     * 
     * @param array $requiredKeys Array of required configuration keys
     * @throws RuntimeException If required keys are missing
     */
    function validate_config(array $requiredKeys)
    {
        ConfigManager::getInstance()->validateRequired($requiredKeys);
    }
}

if (!function_exists('database_config')) {
    /**
     * Get database configuration with environment overrides
     * 
     * @return array Database configuration
     */
    function database_config()
    {
        return ConfigManager::getInstance()->getDatabaseConfig();
    }
}

if (!function_exists('app_config')) {
    /**
     * Get application configuration with environment overrides
     * 
     * @return array Application configuration
     */
    function app_config()
    {
        return ConfigManager::getInstance()->getAppConfig();
    }
}

if (!function_exists('supabase_config')) {
    /**
     * Get Supabase configuration with environment overrides
     * 
     * @param string|null $key Specific configuration key
     * @param mixed $default Default value
     * @return mixed Supabase configuration
     */
    function supabase_config($key = null, $default = null)
    {
        $config = ConfigManager::getInstance()->getSupabaseConfig();
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
}

if (!function_exists('table_name')) {
    /**
     * Get table name from Supabase configuration
     * 
     * @param string $table Table identifier
     * @return string Actual table name
     */
    function table_name($table)
    {
        $tables = supabase_config('tables', []);
        return $tables[$table] ?? $table;
    }
}

if (!function_exists('supabase_url')) {
    /**
     * Get Supabase URL
     * 
     * @return string Supabase URL
     */
    function supabase_url()
    {
        return supabase_config('url', '');
    }
}

if (!function_exists('supabase_anon_key')) {
    /**
     * Get Supabase anonymous key
     * 
     * @return string Supabase anonymous key
     */
    function supabase_anon_key()
    {
        return supabase_config('anon_key', '');
    }
}

if (!function_exists('supabase_service_key')) {
    /**
     * Get Supabase service key
     * 
     * @return string Supabase service key
     */
    function supabase_service_key()
    {
        return supabase_config('service_key', '');
    }
}

if (!function_exists('base_url')) {
    /**
     * Get base URL with optional path
     * 
     * @param string $path Optional path to append
     * @return string Full URL with base path
     */
    function base_url(string $path = ''): string
    {
        // Get base path from config or environment
        $basePath = config('app.base_path', env('APP_BASE_PATH', ''));
        
        // Ensure base path starts with / and doesn't end with /
        if (!empty($basePath)) {
            $basePath = '/' . trim($basePath, '/');
        }
        
        // Ensure path starts with /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        return $basePath . $path;
    }
}