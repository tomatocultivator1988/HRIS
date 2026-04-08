<?php

namespace Core;

/**
 * DatabaseConnectionPool - Manages database connection pooling
 * 
 * Provides connection reuse and pooling for improved performance.
 * Supports lazy loading and connection lifecycle management.
 */
class DatabaseConnectionPool
{
    private static ?DatabaseConnectionPool $instance = null;
    private array $connections = [];
    private array $activeConnections = [];
    private int $maxConnections;
    private int $minConnections;
    private int $connectionTimeout;
    private int $idleTimeout;
    private array $config;
    
    private function __construct()
    {
        $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
        $this->config = $dbConfig['connections'][$dbConfig['default']] ?? [];
        
        $poolConfig = $dbConfig['pool'] ?? [];
        $this->maxConnections = $poolConfig['max_connections'] ?? 10;
        $this->minConnections = $poolConfig['min_connections'] ?? 1;
        $this->connectionTimeout = $poolConfig['connection_timeout'] ?? 30;
        $this->idleTimeout = $poolConfig['idle_timeout'] ?? 300;
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
     * Get a connection from the pool (lazy loading)
     *
     * @return DatabaseConnection Connection instance
     * @throws \Exception If unable to get connection
     */
    public function getConnection(): DatabaseConnection
    {
        // Clean up idle connections first
        $this->cleanupIdleConnections();
        
        // Try to reuse an available connection
        foreach ($this->connections as $id => $conn) {
            if (!isset($this->activeConnections[$id])) {
                // Connection is available, mark as active
                $this->activeConnections[$id] = time();
                $conn['last_used'] = time();
                return $conn['connection'];
            }
        }
        
        // Check if we can create a new connection
        if (count($this->connections) < $this->maxConnections) {
            return $this->createConnection();
        }
        
        // Wait for a connection to become available
        $startTime = time();
        while (time() - $startTime < $this->connectionTimeout) {
            foreach ($this->connections as $id => $conn) {
                if (!isset($this->activeConnections[$id])) {
                    $this->activeConnections[$id] = time();
                    $conn['last_used'] = time();
                    return $conn['connection'];
                }
            }
            usleep(100000); // Wait 100ms
        }
        
        throw new \Exception('Connection pool timeout: unable to get connection');
    }
    
    /**
     * Release a connection back to the pool
     *
     * @param DatabaseConnection $connection Connection to release
     */
    public function releaseConnection(DatabaseConnection $connection): void
    {
        foreach ($this->connections as $id => $conn) {
            if ($conn['connection'] === $connection) {
                unset($this->activeConnections[$id]);
                $this->connections[$id]['last_used'] = time();
                return;
            }
        }
    }
    
    /**
     * Create a new connection (lazy initialization)
     *
     * @return DatabaseConnection New connection instance
     */
    private function createConnection(): DatabaseConnection
    {
        $id = uniqid('conn_', true);
        $connection = new DatabaseConnection($this->config);
        
        $this->connections[$id] = [
            'connection' => $connection,
            'created_at' => time(),
            'last_used' => time()
        ];
        
        $this->activeConnections[$id] = time();
        
        return $connection;
    }
    
    /**
     * Clean up idle connections
     */
    private function cleanupIdleConnections(): void
    {
        $now = time();
        $minToKeep = $this->minConnections;
        $activeCount = count($this->connections);
        
        foreach ($this->connections as $id => $conn) {
            // Don't clean up active connections
            if (isset($this->activeConnections[$id])) {
                continue;
            }
            
            // Keep minimum connections
            if ($activeCount <= $minToKeep) {
                break;
            }
            
            // Remove idle connections
            $idleTime = $now - $conn['last_used'];
            if ($idleTime > $this->idleTimeout) {
                $conn['connection']->close();
                unset($this->connections[$id]);
                $activeCount--;
            }
        }
    }
    
    /**
     * Close all connections
     */
    public function closeAll(): void
    {
        foreach ($this->connections as $conn) {
            $conn['connection']->close();
        }
        
        $this->connections = [];
        $this->activeConnections = [];
    }
    
    /**
     * Get pool statistics
     *
     * @return array Pool statistics
     */
    public function getStats(): array
    {
        return [
            'total_connections' => count($this->connections),
            'active_connections' => count($this->activeConnections),
            'idle_connections' => count($this->connections) - count($this->activeConnections),
            'max_connections' => $this->maxConnections,
            'min_connections' => $this->minConnections
        ];
    }
}

/**
 * DatabaseConnection - Represents a single database connection
 * 
 * Wraps the actual database connection and provides query optimization.
 */
class DatabaseConnection
{
    private array $config;
    private $connection = null;
    private array $preparedStatements = [];
    private bool $isConnected = false;
    
    public function __construct(array $config)
    {
        $this->config = $config;
        // Lazy connection - don't connect until first query
    }
    
    /**
     * Execute a query with prepared statement (lazy connection)
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Query result
     */
    public function query(string $query, array $params = []): array
    {
        // Connect on first query (lazy loading)
        if (!$this->isConnected) {
            $this->connect();
        }
        
        // For Supabase, we use the REST API
        // This is a simplified implementation
        return $this->executeSupabaseQuery($query, $params);
    }
    
    /**
     * Prepare a statement for reuse (query optimization)
     *
     * @param string $query SQL query
     * @return string Statement ID
     */
    public function prepare(string $query): string
    {
        $stmtId = md5($query);
        
        if (!isset($this->preparedStatements[$stmtId])) {
            $this->preparedStatements[$stmtId] = [
                'query' => $query,
                'executions' => 0
            ];
        }
        
        return $stmtId;
    }
    
    /**
     * Execute a prepared statement
     *
     * @param string $stmtId Statement ID
     * @param array $params Query parameters
     * @return array Query result
     */
    public function executePrepared(string $stmtId, array $params = []): array
    {
        if (!isset($this->preparedStatements[$stmtId])) {
            throw new \Exception('Prepared statement not found');
        }
        
        $stmt = $this->preparedStatements[$stmtId];
        $stmt['executions']++;
        
        return $this->query($stmt['query'], $params);
    }
    
    /**
     * Connect to database (lazy initialization)
     */
    private function connect(): void
    {
        // For Supabase, we don't need a persistent connection
        // The connection is established per request via HTTP
        $this->isConnected = true;
    }
    
    /**
     * Execute Supabase query via REST API
     */
    private function executeSupabaseQuery(string $query, array $params): array
    {
        // This is a placeholder for Supabase query execution
        // In a real implementation, this would translate SQL to Supabase REST API calls
        return [
            'success' => true,
            'data' => []
        ];
    }
    
    /**
     * Close the connection
     */
    public function close(): void
    {
        $this->isConnected = false;
        $this->preparedStatements = [];
    }
    
    /**
     * Check if connection is active
     */
    public function isConnected(): bool
    {
        return $this->isConnected;
    }
    
    /**
     * Get prepared statement statistics
     */
    public function getPreparedStats(): array
    {
        return array_map(function($stmt) {
            return [
                'executions' => $stmt['executions']
            ];
        }, $this->preparedStatements);
    }
}
