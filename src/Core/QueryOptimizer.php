<?php

namespace Core;

/**
 * QueryOptimizer - Provides query optimization and caching
 * 
 * Optimizes database queries through caching, prepared statements,
 * and query analysis.
 */
class QueryOptimizer
{
    private static ?QueryOptimizer $instance = null;
    private Cache $cache;
    private array $queryStats = [];
    private int $slowQueryThreshold;
    private bool $logQueries;
    
    private function __construct()
    {
        $this->cache = Cache::getInstance();
        
        $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
        $queryConfig = $dbConfig['query'] ?? [];
        
        $this->slowQueryThreshold = $queryConfig['slow_query_threshold'] ?? 1000; // ms
        $this->logQueries = $queryConfig['log_queries'] ?? false;
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
     * Execute a query with caching
     *
     * @param string $queryKey Unique query identifier
     * @param callable $queryCallback Callback to execute query
     * @param int|null $ttl Cache TTL in seconds
     * @return mixed Query result
     */
    public function cachedQuery(string $queryKey, callable $queryCallback, ?int $ttl = null)
    {
        $cacheKey = 'query:' . md5($queryKey);
        
        // Try to get from cache
        $result = $this->cache->get($cacheKey);
        if ($result !== null) {
            $this->recordQueryStat($queryKey, 0, true);
            return $result;
        }
        
        // Execute query and measure time
        $startTime = microtime(true);
        $result = $queryCallback();
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
        
        // Cache the result
        if ($result !== null && $result !== false) {
            $this->cache->set($cacheKey, $result, $ttl);
        }
        
        // Record statistics
        $this->recordQueryStat($queryKey, $executionTime, false);
        
        return $result;
    }
    
    /**
     * Invalidate query cache
     *
     * @param string $queryKey Query identifier or pattern
     */
    public function invalidateCache(string $queryKey): void
    {
        $cacheKey = 'query:' . md5($queryKey);
        $this->cache->delete($cacheKey);
    }
    
    /**
     * Invalidate cache by pattern
     *
     * @param string $pattern Pattern to match (e.g., 'employees:*')
     */
    public function invalidateCacheByPattern(string $pattern): void
    {
        // For simple file-based cache, we'll clear all query caches
        // In a more sophisticated implementation, this would use pattern matching
        $this->cache->clear();
    }
    
    /**
     * Optimize a query by analyzing and suggesting improvements
     *
     * @param string $query SQL query
     * @return array Optimization suggestions
     */
    public function analyzeQuery(string $query): array
    {
        $suggestions = [];
        
        // Check for SELECT *
        if (preg_match('/SELECT\s+\*/i', $query)) {
            $suggestions[] = [
                'type' => 'performance',
                'message' => 'Avoid SELECT * - specify only needed columns',
                'severity' => 'medium'
            ];
        }
        
        // Check for missing WHERE clause in UPDATE/DELETE
        if (preg_match('/(UPDATE|DELETE)\s+\w+\s*$/i', $query)) {
            $suggestions[] = [
                'type' => 'safety',
                'message' => 'UPDATE/DELETE without WHERE clause affects all rows',
                'severity' => 'high'
            ];
        }
        
        // Check for LIKE with leading wildcard
        if (preg_match('/LIKE\s+[\'"]%/i', $query)) {
            $suggestions[] = [
                'type' => 'performance',
                'message' => 'LIKE with leading wildcard prevents index usage',
                'severity' => 'medium'
            ];
        }
        
        // Check for OR conditions that might prevent index usage
        if (preg_match('/WHERE.*\sOR\s/i', $query)) {
            $suggestions[] = [
                'type' => 'performance',
                'message' => 'OR conditions may prevent index usage - consider UNION',
                'severity' => 'low'
            ];
        }
        
        // Check for subqueries that could be JOINs
        if (preg_match('/WHERE.*IN\s*\(\s*SELECT/i', $query)) {
            $suggestions[] = [
                'type' => 'performance',
                'message' => 'Consider using JOIN instead of subquery in WHERE IN',
                'severity' => 'medium'
            ];
        }
        
        return $suggestions;
    }
    
    /**
     * Record query statistics
     *
     * @param string $queryKey Query identifier
     * @param float $executionTime Execution time in milliseconds
     * @param bool $fromCache Whether result was from cache
     */
    private function recordQueryStat(string $queryKey, float $executionTime, bool $fromCache): void
    {
        if (!isset($this->queryStats[$queryKey])) {
            $this->queryStats[$queryKey] = [
                'executions' => 0,
                'cache_hits' => 0,
                'total_time' => 0,
                'max_time' => 0,
                'slow_queries' => 0
            ];
        }
        
        $stats = &$this->queryStats[$queryKey];
        $stats['executions']++;
        
        if ($fromCache) {
            $stats['cache_hits']++;
        } else {
            $stats['total_time'] += $executionTime;
            $stats['max_time'] = max($stats['max_time'], $executionTime);
            
            if ($executionTime > $this->slowQueryThreshold) {
                $stats['slow_queries']++;
                
                if ($this->logQueries) {
                    $this->logSlowQuery($queryKey, $executionTime);
                }
            }
        }
    }
    
    /**
     * Log slow query
     *
     * @param string $queryKey Query identifier
     * @param float $executionTime Execution time in milliseconds
     */
    private function logSlowQuery(string $queryKey, float $executionTime): void
    {
        $logFile = dirname(__DIR__, 2) . '/logs/slow_queries.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] Slow query (%dms): %s\n",
            date('Y-m-d H:i:s'),
            round($executionTime),
            $queryKey
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get query statistics
     *
     * @return array Query statistics
     */
    public function getStats(): array
    {
        return $this->queryStats;
    }
    
    /**
     * Get slow queries
     *
     * @return array Slow queries with statistics
     */
    public function getSlowQueries(): array
    {
        $slowQueries = [];
        
        foreach ($this->queryStats as $queryKey => $stats) {
            if ($stats['slow_queries'] > 0) {
                $slowQueries[$queryKey] = $stats;
            }
        }
        
        // Sort by max execution time
        uasort($slowQueries, function($a, $b) {
            return $b['max_time'] <=> $a['max_time'];
        });
        
        return $slowQueries;
    }
    
    /**
     * Get cache hit rate
     *
     * @return float Cache hit rate (0-1)
     */
    public function getCacheHitRate(): float
    {
        $totalExecutions = 0;
        $totalCacheHits = 0;
        
        foreach ($this->queryStats as $stats) {
            $totalExecutions += $stats['executions'];
            $totalCacheHits += $stats['cache_hits'];
        }
        
        if ($totalExecutions === 0) {
            return 0.0;
        }
        
        return $totalCacheHits / $totalExecutions;
    }
}
