<?php

namespace Core;

/**
 * PerformanceMonitor - Track operation performance
 * 
 * Simple performance monitoring to identify slow operations.
 * Logs warnings for operations taking > 1 second.
 * 
 * ZERO COST - Just timing and logging!
 */
class PerformanceMonitor
{
    private static array $timers = [];
    private static array $measurements = [];
    private static int $slowThreshold = 1000; // milliseconds
    
    /**
     * Start timing an operation
     *
     * @param string $name Operation name
     */
    public static function start(string $name): void
    {
        self::$timers[$name] = microtime(true);
    }
    
    /**
     * End timing and return duration
     *
     * @param string $name Operation name
     * @return float Duration in milliseconds
     */
    public static function end(string $name): float
    {
        if (!isset(self::$timers[$name])) {
            return 0;
        }
        
        $duration = (microtime(true) - self::$timers[$name]) * 1000; // Convert to ms
        unset(self::$timers[$name]);
        
        // Store measurement
        if (!isset(self::$measurements[$name])) {
            self::$measurements[$name] = [
                'count' => 0,
                'total' => 0,
                'min' => PHP_FLOAT_MAX,
                'max' => 0
            ];
        }
        
        self::$measurements[$name]['count']++;
        self::$measurements[$name]['total'] += $duration;
        self::$measurements[$name]['min'] = min(self::$measurements[$name]['min'], $duration);
        self::$measurements[$name]['max'] = max(self::$measurements[$name]['max'], $duration);
        
        // Log slow operations
        if ($duration > self::$slowThreshold) {
            error_log(sprintf(
                "⚠️ SLOW OPERATION: %s took %.2fms (threshold: %dms)",
                $name,
                $duration,
                self::$slowThreshold
            ));
        }
        
        return $duration;
    }
    
    /**
     * Measure a callable and return its result
     *
     * @param string $name Operation name
     * @param callable $callback Function to measure
     * @return mixed Result of callback
     */
    public static function measure(string $name, callable $callback): mixed
    {
        self::start($name);
        $result = $callback();
        self::end($name);
        return $result;
    }
    
    /**
     * Get performance statistics
     *
     * @return array Performance stats
     */
    public static function getStats(): array
    {
        $stats = [];
        
        foreach (self::$measurements as $name => $data) {
            $stats[$name] = [
                'count' => $data['count'],
                'total_ms' => round($data['total'], 2),
                'avg_ms' => round($data['total'] / $data['count'], 2),
                'min_ms' => round($data['min'], 2),
                'max_ms' => round($data['max'], 2)
            ];
        }
        
        // Sort by total time descending
        uasort($stats, function($a, $b) {
            return $b['total_ms'] <=> $a['total_ms'];
        });
        
        return $stats;
    }
    
    /**
     * Set slow operation threshold
     *
     * @param int $milliseconds Threshold in milliseconds
     */
    public static function setSlowThreshold(int $milliseconds): void
    {
        self::$slowThreshold = $milliseconds;
    }
    
    /**
     * Reset all measurements
     */
    public static function reset(): void
    {
        self::$timers = [];
        self::$measurements = [];
    }
}
