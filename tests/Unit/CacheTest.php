<?php

use PHPUnit\Framework\TestCase;
use Core\Cache;
use Core\Container;

/**
 * CacheTest - Unit tests for Cache class
 */
class CacheTest extends TestCase
{
    private Cache $cache;
    private string $testCachePath;
    
    protected function setUp(): void
    {
        // Set up test cache path
        $this->testCachePath = sys_get_temp_dir() . '/hris_test_cache_' . uniqid();
        mkdir($this->testCachePath, 0755, true);
        
        // Mock config
        $config = [
            'cache' => [
                'default' => 'file',
                'ttl' => 3600,
                'path' => $this->testCachePath
            ]
        ];
        
        $container = Container::getInstance();
        $container->instance('app.config', $config);
        
        $this->cache = Cache::getInstance();
    }
    
    protected function tearDown(): void
    {
        // Clean up test cache
        $this->cache->clear();
        
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
    
    public function testSetAndGet(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->assertTrue($this->cache->set($key, $value));
        $this->assertEquals($value, $this->cache->get($key));
    }
    
    public function testGetWithDefault(): void
    {
        $default = 'default_value';
        $this->assertEquals($default, $this->cache->get('nonexistent', $default));
    }
    
    public function testHas(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->assertFalse($this->cache->has($key));
        $this->cache->set($key, $value);
        $this->assertTrue($this->cache->has($key));
    }
    
    public function testDelete(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->cache->set($key, $value);
        $this->assertTrue($this->cache->has($key));
        
        $this->cache->delete($key);
        $this->assertFalse($this->cache->has($key));
    }
    
    public function testRemember(): void
    {
        $key = 'test_key';
        $callCount = 0;
        
        $callback = function() use (&$callCount) {
            $callCount++;
            return 'generated_value';
        };
        
        // First call should execute callback
        $value1 = $this->cache->remember($key, $callback);
        $this->assertEquals('generated_value', $value1);
        $this->assertEquals(1, $callCount);
        
        // Second call should use cached value
        $value2 = $this->cache->remember($key, $callback);
        $this->assertEquals('generated_value', $value2);
        $this->assertEquals(1, $callCount); // Callback not called again
    }
    
    public function testCacheExpiration(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        
        // Set with 1 second TTL
        $this->cache->set($key, $value, 1);
        $this->assertTrue($this->cache->has($key));
        
        // Wait for expiration
        sleep(2);
        
        $this->assertFalse($this->cache->has($key));
    }
    
    public function testCacheArrayValues(): void
    {
        $key = 'test_array';
        $value = ['name' => 'John', 'age' => 30];
        
        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }
    
    public function testCacheObjectValues(): void
    {
        $key = 'test_object';
        $value = (object)['name' => 'John', 'age' => 30];
        
        $this->cache->set($key, $value);
        $cached = $this->cache->get($key);
        
        $this->assertEquals($value->name, $cached->name);
        $this->assertEquals($value->age, $cached->age);
    }
    
    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');
        
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3'));
        
        $this->cache->clear();
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }
}
