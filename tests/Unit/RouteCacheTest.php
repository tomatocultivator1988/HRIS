<?php

use PHPUnit\Framework\TestCase;
use Core\RouteCache;
use Core\Router;
use Core\Container;

/**
 * RouteCacheTest - Unit tests for RouteCache class
 */
class RouteCacheTest extends TestCase
{
    private RouteCache $routeCache;
    private string $testCachePath;
    
    protected function setUp(): void
    {
        // Set up test cache path
        $this->testCachePath = sys_get_temp_dir() . '/hris_test_route_cache_' . uniqid();
        mkdir($this->testCachePath, 0755, true);
        
        // Mock config for production environment
        $config = [
            'environment' => 'production',
            'cache' => [
                'path' => $this->testCachePath
            ]
        ];
        
        $container = Container::getInstance();
        $container->instance('app.config', $config);
        
        $this->routeCache = RouteCache::getInstance();
    }
    
    protected function tearDown(): void
    {
        $this->routeCache->clear();
        
        if (is_dir($this->testCachePath)) {
            $this->removeDirectory($this->testCachePath);
        }
    }
    
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    public function testCacheRoutes(): void
    {
        $routes = [
            [
                'method' => 'GET',
                'pattern' => '/test',
                'handler' => 'TestController@index',
                'middleware' => []
            ]
        ];
        
        $this->assertTrue($this->routeCache->cache($routes));
        $this->assertTrue($this->routeCache->isCached());
    }
    
    public function testLoadCachedRoutes(): void
    {
        $routes = [
            [
                'method' => 'GET',
                'pattern' => '/test',
                'handler' => 'TestController@index',
                'middleware' => []
            ]
        ];
        
        $this->routeCache->cache($routes);
        $loaded = $this->routeCache->load();
        
        $this->assertIsArray($loaded);
        $this->assertEquals($routes, $loaded);
    }
    
    public function testClearCache(): void
    {
        $routes = [
            [
                'method' => 'GET',
                'pattern' => '/test',
                'handler' => 'TestController@index',
                'middleware' => []
            ]
        ];
        
        $this->routeCache->cache($routes);
        $this->assertTrue($this->routeCache->isCached());
        
        $this->routeCache->clear();
        $this->assertFalse($this->routeCache->isCached());
    }
    
    public function testIsEnabled(): void
    {
        // Should be enabled in production
        $this->assertTrue($this->routeCache->isEnabled());
    }
    
    public function testCacheFileLocation(): void
    {
        $cacheFile = $this->routeCache->getCacheFile();
        $this->assertStringContainsString('routes.php', $cacheFile);
    }
}
