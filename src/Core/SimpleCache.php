<?php

namespace Core;

/**
 * SimpleCache - In-memory request-scoped cache
 * 
 * Provides simple caching for the duration of a single request.
 * Reduces redundant database queries within the same request.
 * 
 * ZERO COST - No infrastructure needed!
 */
class SimpleCache
{
    private static array $cache = [];
    private static array $timestamps = [];
    private static int $defaultTTL = 300; // 5 minutes
    private static array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0
    ];
    
    /**
     * Remember a value - execute callback only if not cached
     *
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @param int|null $ttl Time to live in seconds
     * @return mixed Cached or fresh value
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? self::$defaultTTL;
        
        // Check if cached and not expired
        if (isset(self::$cache[$key]) && 
            isset(self::$timestamps[$key]) && 
            (time() - self::$timestamps[$key]) < $ttl) {
            self::$stats['hits']++;
            return self::$cache[$key];
        }
        
        // Cache miss - execute callback
        self::$stats['misses']++;
        $result = $callback();
        
        // Store in cache
        self::set($key, $result, $ttl);
        
        return $result;
    }
    
    /**
     * Set a value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds
     */
    public static function set(string $key, mixed $value, ?int $ttl = null): void
    {
        self::$cache[$key] = $value;
        self::$timestamps[$key] = time();
        self::$stats['sets']++;
    }
    
    /**
     * Get a value from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key]) && 
            isset(self::$timestamps[$key]) && 
            (time() - self::$timestamps[$key]) < self::$defaultTTL) {
            self::$stats['hits']++;
            return self::$cache[$key];
        }
        
        self::$stats['misses']++;
        return $default;
    }
    
    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool True if exists and not expired
     */
    public static function has(string $key): bool
    {
        return isset(self::$cache[$key]) && 
               isset(self::$timestamps[$key]) && 
               (time() - self::$timestamps[$key]) < self::$defaultTTL;
    }
    
    /**
     * Remove a key from cache
     *
     * @param string $key Cache key
     */
    public static function forget(string $key): void
    {
        unset(self::$cache[$key], self::$timestamps[$key]);
    }
    
    /**
     * Clear all cache
     */
    public static function flush(): void
    {
        self::$cache = [];
        self::$timestamps = [];
    }
    
    /**
     * Get cache statistics
     *
     * @return array Cache hit/miss stats
     */
    public static function getStats(): array
    {
        $total = self::$stats['hits'] + self::$stats['misses'];
        $hitRate = $total > 0 ? round((self::$stats['hits'] / $total) * 100, 2) : 0;
        
        return [
            'hits' => self::$stats['hits'],
            'misses' => self::$stats['misses'],
            'sets' => self::$stats['sets'],
            'hit_rate' => $hitRate . '%',
            'cached_items' => count(self::$cache)
        ];
    }
}
