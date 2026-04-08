# Performance Optimization Guide

This document describes the performance optimization features implemented in the MVC architecture conversion.

## Overview

The system includes several performance optimization features:

1. **Route Caching** - Caches compiled routes for production
2. **Query Optimization** - Optimizes database queries with caching and prepared statements
3. **Database Connection Pooling** - Reuses database connections for better performance
4. **HTTP Caching Headers** - Implements proper caching headers for client-side caching
5. **Lazy Loading** - Loads resources only when needed

## Route Caching

### What is Route Caching?

Route caching compiles all application routes into a single cached file, eliminating the need to parse route configuration files on every request.

### When to Use

- **Production environments only** - Route caching is automatically enabled when `APP_ENV=production`
- After deploying new code with route changes
- When you notice slow routing performance

### How to Cache Routes

```bash
# Cache all routes
php bin/cache-routes.php

# Clear route cache
php bin/clear-cache.php routes
```

### Implementation

Routes are cached in `storage/cache/routes.php`. The Router class automatically loads cached routes when available.

```php
// Router automatically uses cached routes in production
$router = new Router(); // Loads from cache if available
```

## Query Optimization

### Query Caching

Cache expensive database queries to reduce database load:

```php
use Core\Traits\Cacheable;

class EmployeeService
{
    use Cacheable;
    
    public function getActiveEmployees()
    {
        return $this->cachedQuery(
            'employees:active',
            function() {
                return DatabaseHelper::select('employees', ['is_active' => true]);
            },
            3600 // Cache for 1 hour
        );
    }
}
```

### Invalidating Query Cache

```php
// Invalidate specific query cache
$this->invalidateQueryCache('employees:active');

// Invalidate all query caches
$cache = Cache::getInstance();
$cache->clear();
```

### Query Analysis

The QueryOptimizer analyzes queries and provides optimization suggestions:

```php
$optimizer = QueryOptimizer::getInstance();
$suggestions = $optimizer->analyzeQuery($sqlQuery);

foreach ($suggestions as $suggestion) {
    echo "{$suggestion['type']}: {$suggestion['message']}\n";
}
```

### Slow Query Logging

Queries exceeding the threshold (default: 1000ms) are automatically logged to `logs/slow_queries.log`.

Configure the threshold in `config/database.php`:

```php
'query' => [
    'slow_query_threshold' => 1000, // milliseconds
    'log_queries' => true,
],
```

## Database Connection Pooling

### What is Connection Pooling?

Connection pooling reuses database connections instead of creating new ones for each request, reducing connection overhead.

### Configuration

Configure pool settings in `config/database.php`:

```php
'pool' => [
    'max_connections' => 10,      // Maximum connections
    'min_connections' => 1,       // Minimum connections to keep
    'connection_timeout' => 30,   // Timeout in seconds
    'idle_timeout' => 300,        // Idle connection timeout
],
```

### Usage

The connection pool is used automatically by the Model layer:

```php
// Get connection from pool (lazy loading)
$pool = DatabaseConnectionPool::getInstance();
$connection = $pool->getConnection();

// Use connection
$result = $connection->query($sql, $params);

// Release back to pool
$pool->releaseConnection($connection);
```

### Pool Statistics

Monitor pool performance:

```php
$pool = DatabaseConnectionPool::getInstance();
$stats = $pool->getStats();

echo "Total connections: {$stats['total_connections']}\n";
echo "Active connections: {$stats['active_connections']}\n";
echo "Idle connections: {$stats['idle_connections']}\n";
```

## HTTP Caching Headers

### Cache Control

Set appropriate cache headers for responses:

```php
// Cache for 1 hour (public)
return $response->json($data)
    ->cache(3600, true);

// Cache for 30 minutes (private)
return $response->json($data)
    ->cache(1800, false);

// Disable caching
return $response->json($data)
    ->noCache();
```

### ETag Support

Use ETags for conditional requests:

```php
// Set ETag (auto-generated from content)
$response->json($data)->etag();

// Set custom ETag
$response->json($data)->etag('custom-etag-value');

// Check if not modified
if ($response->isNotModified($request)) {
    return $response->notModified();
}
```

### Last-Modified Header

```php
// Set Last-Modified header
$response->json($data)
    ->lastModified(time());

// Or use a date string
$response->json($data)
    ->lastModified('2024-01-01 12:00:00');
```

### Example: Efficient Resource Endpoint

```php
public function show(Request $request): Response
{
    $id = $request->getRouteParameter('id');
    $employee = $this->employeeService->findById($id);
    
    $response = new Response();
    $response->json($employee)
        ->etag()
        ->lastModified($employee['updated_at'])
        ->cache(3600);
    
    // Return 304 if not modified
    if ($response->isNotModified($request)) {
        return $response->notModified();
    }
    
    return $response;
}
```

## Lazy Loading

### What is Lazy Loading?

Lazy loading defers initialization of resources until they are actually needed, reducing memory usage and startup time.

### Database Connections

Database connections are lazy-loaded - they're only established when the first query is executed:

```php
// Connection not established yet
$connection = new DatabaseConnection($config);

// Connection established on first query
$result = $connection->query($sql); // Connects here
```

### Service Dependencies

Services can use lazy loading for expensive dependencies:

```php
class ReportService
{
    private ?ExpensiveService $expensiveService = null;
    
    private function getExpensiveService(): ExpensiveService
    {
        if ($this->expensiveService === null) {
            $this->expensiveService = new ExpensiveService();
        }
        return $this->expensiveService;
    }
}
```

## Performance Monitoring

### Performance Middleware

Add performance headers to responses:

```php
// In Router::dispatch()
$response = PerformanceMiddleware::addPerformanceHeaders($response, $request);
```

Response headers include:
- `X-Execution-Time` - Request execution time in milliseconds
- `X-Memory-Usage` - Memory used by request
- `X-Peak-Memory` - Peak memory usage

### Slow Request Logging

Requests exceeding 1 second are automatically logged to `logs/slow_requests.log`.

### Query Statistics

View query performance statistics:

```php
$optimizer = QueryOptimizer::getInstance();

// Get all query stats
$stats = $optimizer->getStats();

// Get slow queries
$slowQueries = $optimizer->getSlowQueries();

// Get cache hit rate
$hitRate = $optimizer->getCacheHitRate();
echo "Cache hit rate: " . ($hitRate * 100) . "%\n";
```

## Best Practices

### 1. Cache Frequently Accessed Data

```php
// Good: Cache dashboard metrics
public function getMetrics()
{
    return $this->remember('dashboard:metrics', function() {
        return $this->calculateMetrics();
    }, 300); // 5 minutes
}
```

### 2. Use Appropriate Cache TTL

- **Static data**: Long TTL (hours/days)
- **Dynamic data**: Short TTL (minutes)
- **Real-time data**: No caching or very short TTL (seconds)

### 3. Invalidate Cache on Updates

```php
public function updateEmployee($id, $data)
{
    $result = $this->employeeModel->update($id, $data);
    
    // Invalidate related caches
    $this->invalidateCache("employee:{$id}");
    $this->invalidateQueryCache('employees:active');
    
    return $result;
}
```

### 4. Use HTTP Caching for Static Resources

```php
// Cache static resources for 1 year
return $response->json($data)
    ->cache(31536000, true)
    ->etag();
```

### 5. Monitor Performance

- Review slow query logs regularly
- Monitor cache hit rates
- Check slow request logs
- Use performance headers in development

## Cache Management Commands

```bash
# Cache routes for production
php bin/cache-routes.php

# Clear all caches
php bin/clear-cache.php all

# Clear specific cache
php bin/clear-cache.php routes
php bin/clear-cache.php query
php bin/clear-cache.php config
```

## Configuration

### Application Config (`config/app.php`)

```php
'cache' => [
    'default' => 'file',
    'ttl' => 3600,
    'prefix' => 'hris_',
    'path' => storage_path('cache'),
],
```

### Database Config (`config/database.php`)

```php
'pool' => [
    'max_connections' => 10,
    'min_connections' => 1,
    'connection_timeout' => 30,
    'idle_timeout' => 300,
],

'query' => [
    'slow_query_threshold' => 1000,
    'log_queries' => false,
],
```

## Troubleshooting

### Routes Not Caching

- Check `APP_ENV` is set to `production`
- Verify `storage/cache` directory is writable
- Run `php bin/cache-routes.php` manually

### Cache Not Working

- Check `storage/cache` directory exists and is writable
- Verify cache configuration in `config/app.php`
- Clear cache and try again: `php bin/clear-cache.php all`

### Slow Queries

- Review `logs/slow_queries.log`
- Use query analyzer: `$optimizer->analyzeQuery($sql)`
- Add appropriate indexes to database tables
- Consider caching query results

### Connection Pool Issues

- Check pool configuration in `config/database.php`
- Monitor pool statistics: `$pool->getStats()`
- Increase `max_connections` if needed
- Reduce `idle_timeout` to free connections faster

## Performance Checklist

- [ ] Route caching enabled in production
- [ ] Frequently accessed queries are cached
- [ ] Appropriate cache TTL values set
- [ ] Cache invalidation on data updates
- [ ] HTTP caching headers configured
- [ ] ETag support for conditional requests
- [ ] Database connection pooling configured
- [ ] Slow query logging enabled
- [ ] Performance monitoring in place
- [ ] Regular cache cleanup scheduled

## Related Requirements

This implementation satisfies the following requirements:

- **Requirement 11.1**: Implement caching mechanisms for frequently accessed data
- **Requirement 11.2**: Provide database query optimization and connection pooling
- **Requirement 11.4**: Support lazy loading for expensive operations
