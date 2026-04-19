<?php

namespace Core;

/**
 * Memory-Based Rate Limiter
 * 
 * Fast, in-memory rate limiting using PHP arrays.
 * Much faster than file-based storage (no disk I/O).
 * 
 * Trade-off: Data resets on PHP process restart, but that's acceptable
 * for rate limiting as it provides temporary protection.
 */
class MemoryRateLimiter
{
    /**
     * Storage for request timestamps by client identifier
     * Format: ['client_id' => ['requests' => [timestamps], 'blocked_until' => timestamp]]
     */
    private static array $storage = [];
    
    /**
     * Default configuration
     */
    private static array $config = [
        'window_size' => 60,        // 1 minute window
        'requests_per_window' => 100, // 100 requests per minute
        'burst_limit' => 200,       // Max 200 requests in burst
        'block_duration' => 300     // Block for 5 minutes if burst exceeded
    ];
    
    /**
     * Check if a request is allowed for the given client
     *
     * @param string $clientId Client identifier (usually IP address)
     * @param int|null $limit Custom limit (optional, uses default if null)
     * @return array Result with 'allowed', 'remaining', 'reset', etc.
     */
    public static function check(string $clientId, ?int $limit = null): array
    {
        $now = time();
        $limit = $limit ?? self::$config['requests_per_window'];
        $windowSize = self::$config['window_size'];
        $burstLimit = self::$config['burst_limit'];
        
        // Initialize client data if not exists
        if (!isset(self::$storage[$clientId])) {
            self::$storage[$clientId] = [
                'requests' => [],
                'blocked_until' => 0
            ];
        }
        
        $clientData = &self::$storage[$clientId];
        
        // Check if client is currently blocked
        if ($clientData['blocked_until'] > $now) {
            $retryAfter = $clientData['blocked_until'] - $now;
            return [
                'allowed' => false,
                'reason' => 'blocked',
                'retry_after' => $retryAfter,
                'limit' => $limit,
                'remaining' => 0,
                'reset' => $clientData['blocked_until']
            ];
        }
        
        // Clean up old requests outside the window
        $clientData['requests'] = array_filter(
            $clientData['requests'],
            fn($timestamp) => $timestamp > ($now - $windowSize)
        );
        
        // Reindex array after filtering
        $clientData['requests'] = array_values($clientData['requests']);
        
        $requestCount = count($clientData['requests']);
        
        // Check burst limit (prevents rapid-fire attacks)
        if ($requestCount >= $burstLimit) {
            $clientData['blocked_until'] = $now + self::$config['block_duration'];
            
            return [
                'allowed' => false,
                'reason' => 'burst_limit_exceeded',
                'retry_after' => self::$config['block_duration'],
                'limit' => $limit,
                'remaining' => 0,
                'reset' => $clientData['blocked_until']
            ];
        }
        
        // Check normal rate limit
        if ($requestCount >= $limit) {
            // Calculate when the oldest request will expire
            $oldestRequest = min($clientData['requests']);
            $retryAfter = ($oldestRequest + $windowSize) - $now;
            
            return [
                'allowed' => false,
                'reason' => 'rate_limit_exceeded',
                'retry_after' => max(1, $retryAfter),
                'limit' => $limit,
                'remaining' => 0,
                'reset' => $oldestRequest + $windowSize
            ];
        }
        
        // Request is allowed - record it
        $clientData['requests'][] = $now;
        
        return [
            'allowed' => true,
            'limit' => $limit,
            'remaining' => $limit - count($clientData['requests']),
            'reset' => $now + $windowSize
        ];
    }
    
    /**
     * Reset rate limit for a specific client
     * Useful for testing or manual intervention
     *
     * @param string $clientId Client identifier
     * @return void
     */
    public static function reset(string $clientId): void
    {
        unset(self::$storage[$clientId]);
    }
    
    /**
     * Reset all rate limits
     * Useful for testing
     *
     * @return void
     */
    public static function resetAll(): void
    {
        self::$storage = [];
    }
    
    /**
     * Get current statistics for a client
     *
     * @param string $clientId Client identifier
     * @return array Statistics
     */
    public static function getStats(string $clientId): array
    {
        if (!isset(self::$storage[$clientId])) {
            return [
                'requests' => 0,
                'blocked' => false,
                'blocked_until' => null
            ];
        }
        
        $clientData = self::$storage[$clientId];
        $now = time();
        
        // Clean old requests
        $recentRequests = array_filter(
            $clientData['requests'],
            fn($timestamp) => $timestamp > ($now - self::$config['window_size'])
        );
        
        return [
            'requests' => count($recentRequests),
            'blocked' => $clientData['blocked_until'] > $now,
            'blocked_until' => $clientData['blocked_until'] > $now ? $clientData['blocked_until'] : null
        ];
    }
    
    /**
     * Clean up old data to prevent memory bloat
     * Should be called periodically (e.g., every 100 requests)
     *
     * @return int Number of clients cleaned up
     */
    public static function cleanup(): int
    {
        $now = time();
        $cutoff = $now - self::$config['window_size'];
        $cleaned = 0;
        
        foreach (self::$storage as $clientId => $clientData) {
            // Remove old requests
            $recentRequests = array_filter(
                $clientData['requests'],
                fn($timestamp) => $timestamp > $cutoff
            );
            
            // Remove client if no recent requests and not blocked
            if (empty($recentRequests) && $clientData['blocked_until'] < $now) {
                unset(self::$storage[$clientId]);
                $cleaned++;
            } else {
                self::$storage[$clientId]['requests'] = array_values($recentRequests);
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Configure rate limiter
     *
     * @param array $config Configuration options
     * @return void
     */
    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }
    
    /**
     * Get current configuration
     *
     * @return array Configuration
     */
    public static function getConfig(): array
    {
        return self::$config;
    }
    
    /**
     * Get total number of tracked clients
     *
     * @return int Number of clients
     */
    public static function getClientCount(): int
    {
        return count(self::$storage);
    }
}
