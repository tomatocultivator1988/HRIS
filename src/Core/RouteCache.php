<?php

namespace Core;

/**
 * RouteCache - Provides route caching for production environments
 * 
 * Caches compiled routes to improve routing performance by avoiding
 * repeated route file parsing and regex compilation.
 */
class RouteCache
{
    private static ?RouteCache $instance = null;
    private string $cacheFile;
    private bool $enabled;
    private ?array $cachedRoutes = null;
    
    private function __construct()
    {
        $container = Container::getInstance();
        
        // Get config from container instances (it's stored as array, not object)
        $instances = $container->getInstances();
        $config = $instances['app.config'] ?? $this->getDefaultConfig();
        
        $environment = $config['environment'] ?? 'production';
        
        // Enable caching only in production
        $this->enabled = $environment === 'production';
        
        $cachePath = $config['cache']['path'] ?? dirname(__DIR__, 2) . '/storage/cache';
        $this->cacheFile = $cachePath . '/routes.php';
        
        // Ensure cache directory exists
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
    }
    
    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'environment' => 'development',
            'cache' => [
                'path' => dirname(__DIR__, 2) . '/storage/cache'
            ]
        ];
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cache routes for production use
     *
     * @param array $routes Routes to cache
     * @return bool Success status
     */
    public function cache(array $routes): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $cacheContent = "<?php\n\n";
        $cacheContent .= "// Route cache generated at " . date('Y-m-d H:i:s') . "\n";
        $cacheContent .= "// Do not modify this file manually\n\n";
        $cacheContent .= "return " . var_export($routes, true) . ";\n";
        
        $result = file_put_contents($this->cacheFile, $cacheContent, LOCK_EX);
        
        if ($result !== false) {
            $this->cachedRoutes = $routes;
            return true;
        }
        
        return false;
    }
    
    /**
     * Load cached routes
     *
     * @return array|null Cached routes or null if not cached
     */
    public function load(): ?array
    {
        if (!$this->enabled) {
            return null;
        }
        
        // Return in-memory cache if available
        if ($this->cachedRoutes !== null) {
            return $this->cachedRoutes;
        }
        
        // Load from file cache
        if (file_exists($this->cacheFile)) {
            $this->cachedRoutes = require $this->cacheFile;
            return $this->cachedRoutes;
        }
        
        return null;
    }
    
    /**
     * Check if routes are cached
     *
     * @return bool True if cached
     */
    public function isCached(): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        return file_exists($this->cacheFile);
    }
    
    /**
     * Clear route cache
     *
     * @return bool Success status
     */
    public function clear(): bool
    {
        $this->cachedRoutes = null;
        
        if (file_exists($this->cacheFile)) {
            return unlink($this->cacheFile);
        }
        
        return true;
    }
    
    /**
     * Get cache file path
     *
     * @return string Cache file path
     */
    public function getCacheFile(): string
    {
        return $this->cacheFile;
    }
    
    /**
     * Check if caching is enabled
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * Warm up the route cache
     * 
     * Loads routes from config and caches them
     *
     * @param Router $router Router instance
     * @return bool Success status
     */
    public function warmUp(Router $router): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $routes = $router->getRoutes();
        return $this->cache($routes);
    }
}
