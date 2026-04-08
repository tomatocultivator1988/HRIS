<?php

namespace Core\Traits;

use Core\Cache;
use Core\QueryOptimizer;

/**
 * Cacheable Trait - Provides caching functionality to controllers and services
 * 
 * This trait adds caching methods that can be used in controllers and services
 * to cache expensive operations and database queries.
 */
trait Cacheable
{
    /**
     * Cache instance
     */
    private ?Cache $cache = null;
    
    /**
     * Query optimizer instance
     */
    private ?QueryOptimizer $queryOptimizer = null;
    
    /**
     * Get cache instance
     *
     * @return Cache Cache instance
     */
    protected function cache(): Cache
    {
        if ($this->cache === null) {
            $this->cache = Cache::getInstance();
        }
        
        return $this->cache;
    }
    
    /**
     * Get query optimizer instance
     *
     * @return QueryOptimizer Query optimizer instance
     */
    protected function queryOptimizer(): QueryOptimizer
    {
        if ($this->queryOptimizer === null) {
            $this->queryOptimizer = QueryOptimizer::getInstance();
        }
        
        return $this->queryOptimizer;
    }
    
    /**
     * Cache a value with automatic key generation
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value
     * @param int|null $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null)
    {
        return $this->cache()->remember($key, $callback, $ttl);
    }
    
    /**
     * Cache a database query
     *
     * @param string $queryKey Query identifier
     * @param callable $queryCallback Query callback
     * @param int|null $ttl Cache TTL in seconds
     * @return mixed Query result
     */
    protected function cachedQuery(string $queryKey, callable $queryCallback, ?int $ttl = null)
    {
        return $this->queryOptimizer()->cachedQuery($queryKey, $queryCallback, $ttl);
    }
    
    /**
     * Invalidate cache by key
     *
     * @param string $key Cache key
     */
    protected function invalidateCache(string $key): void
    {
        $this->cache()->delete($key);
    }
    
    /**
     * Invalidate query cache
     *
     * @param string $queryKey Query identifier
     */
    protected function invalidateQueryCache(string $queryKey): void
    {
        $this->queryOptimizer()->invalidateCache($queryKey);
    }
    
    /**
     * Generate cache key from parameters
     *
     * @param string $prefix Key prefix
     * @param array $params Parameters to include in key
     * @return string Generated cache key
     */
    protected function cacheKey(string $prefix, array $params = []): string
    {
        if (empty($params)) {
            return $prefix;
        }
        
        $paramString = json_encode($params);
        return $prefix . ':' . md5($paramString);
    }
}
