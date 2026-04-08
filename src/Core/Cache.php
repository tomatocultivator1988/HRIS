<?php

namespace Core;

/**
 * Cache Class - Provides caching functionality for performance optimization
 * 
 * Supports multiple cache drivers (file, memory) and provides a simple interface
 * for storing and retrieving cached data.
 */
class Cache
{
    private static ?Cache $instance = null;
    private string $driver;
    private string $cachePath;
    private array $memoryCache = [];
    private int $defaultTtl;
    
    private function __construct()
    {
        $container = Container::getInstance();
        
        // Get config from container instances (it's stored as array, not object)
        $instances = $container->getInstances();
        $config = $instances['app.config'] ?? $this->getDefaultConfig();
        
        $this->driver = $config['cache']['default'] ?? 'file';
        $this->cachePath = $config['cache']['path'] ?? dirname(__DIR__, 2) . '/storage/cache';
        $this->defaultTtl = $config['cache']['ttl'] ?? 3600;
        
        // Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'cache' => [
                'default' => 'file',
                'ttl' => 3600,
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
     * Store an item in the cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (null = default)
     * @return bool Success status
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTtl;
        $expiresAt = time() + $ttl;
        
        $cacheData = [
            'value' => $value,
            'expires_at' => $expiresAt
        ];
        
        // Store in memory cache
        $this->memoryCache[$key] = $cacheData;
        
        // Store in persistent cache
        if ($this->driver === 'file') {
            return $this->setFile($key, $cacheData);
        }
        
        return true;
    }
    
    /**
     * Retrieve an item from the cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null)
    {
        // Check memory cache first
        if (isset($this->memoryCache[$key])) {
            $cacheData = $this->memoryCache[$key];
            if ($cacheData['expires_at'] > time()) {
                return $cacheData['value'];
            }
            unset($this->memoryCache[$key]);
        }
        
        // Check persistent cache
        if ($this->driver === 'file') {
            $cacheData = $this->getFile($key);
            if ($cacheData !== null) {
                if ($cacheData['expires_at'] > time()) {
                    // Store in memory cache for faster subsequent access
                    $this->memoryCache[$key] = $cacheData;
                    return $cacheData['value'];
                }
                // Expired, delete it
                $this->delete($key);
            }
        }
        
        return $default;
    }
    
    /**
     * Check if an item exists in the cache
     *
     * @param string $key Cache key
     * @return bool True if exists and not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Remove an item from the cache
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool
    {
        unset($this->memoryCache[$key]);
        
        if ($this->driver === 'file') {
            return $this->deleteFile($key);
        }
        
        return true;
    }
    
    /**
     * Clear all cached items
     *
     * @return bool Success status
     */
    public function clear(): bool
    {
        $this->memoryCache = [];
        
        if ($this->driver === 'file') {
            return $this->clearFiles();
        }
        
        return true;
    }
    
    /**
     * Get or set a cached value using a callback
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int|null $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Store a value in file cache
     */
    private function setFile(string $key, array $cacheData): bool
    {
        $filePath = $this->getCacheFilePath($key);
        $fileDir = dirname($filePath);
        
        if (!is_dir($fileDir)) {
            mkdir($fileDir, 0755, true);
        }
        
        $serialized = serialize($cacheData);
        return file_put_contents($filePath, $serialized, LOCK_EX) !== false;
    }
    
    /**
     * Retrieve a value from file cache
     */
    private function getFile(string $key): ?array
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $contents = file_get_contents($filePath);
        if ($contents === false) {
            return null;
        }
        
        $cacheData = unserialize($contents);
        if (!is_array($cacheData) || !isset($cacheData['expires_at'])) {
            return null;
        }
        
        return $cacheData;
    }
    
    /**
     * Delete a file from cache
     */
    private function deleteFile(string $key): bool
    {
        $filePath = $this->getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Clear all cache files
     */
    private function clearFiles(): bool
    {
        if (!is_dir($this->cachePath)) {
            return true;
        }
        
        // Recursively delete all cache files
        $this->deleteDirectory($this->cachePath, false);
        
        return true;
    }
    
    /**
     * Recursively delete directory contents
     *
     * @param string $dir Directory path
     * @param bool $deleteDir Whether to delete the directory itself
     */
    private function deleteDirectory(string $dir, bool $deleteDir = true): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path, true);
            } else {
                unlink($path);
            }
        }
        
        if ($deleteDir) {
            rmdir($dir);
        }
    }
    
    /**
     * Get cache file path for a key
     */
    private function getCacheFilePath(string $key): string
    {
        $hash = md5($key);
        $prefix = substr($hash, 0, 2);
        return $this->cachePath . '/' . $prefix . '/' . $hash . '.cache';
    }
    
    /**
     * Clean up expired cache entries
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        
        if ($this->driver === 'file' && is_dir($this->cachePath)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->cachePath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'cache') {
                    $contents = file_get_contents($file->getPathname());
                    $cacheData = unserialize($contents);
                    
                    if (is_array($cacheData) && isset($cacheData['expires_at'])) {
                        if ($cacheData['expires_at'] <= time()) {
                            unlink($file->getPathname());
                            $cleaned++;
                        }
                    }
                }
            }
        }
        
        return $cleaned;
    }
}
