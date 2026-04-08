<?php

/**
 * Cache Test - Verify caching functionality
 * 
 * This test verifies that the caching system works correctly.
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\Cache;
use Core\Container;

echo "Testing Cache functionality...\n\n";

$passed = 0;
$failed = 0;

// Test 1: Set and Get
echo "Test 1: Set and Get... ";
try {
    $cache = Cache::getInstance();
    $cache->set('test_key', 'test_value', 60);
    $value = $cache->get('test_key');
    
    if ($value === 'test_value') {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m (Expected 'test_value', got '$value')\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Test 2: Has
echo "Test 2: Has... ";
try {
    $cache = Cache::getInstance();
    $cache->set('test_key2', 'value', 60);
    
    if ($cache->has('test_key2') && !$cache->has('nonexistent')) {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Test 3: Delete
echo "Test 3: Delete... ";
try {
    $cache = Cache::getInstance();
    $cache->set('test_key3', 'value', 60);
    $cache->delete('test_key3');
    
    if (!$cache->has('test_key3')) {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Test 4: Remember
echo "Test 4: Remember... ";
try {
    $cache = Cache::getInstance();
    $rememberKey = 'test_remember_' . uniqid();
    $callCount = 0;
    
    $callback = function() use (&$callCount) {
        $callCount++;
        return 'generated_value';
    };
    
    $value1 = $cache->remember($rememberKey, $callback, 60);
    $value2 = $cache->remember($rememberKey, $callback, 60);
    
    if ($value1 === 'generated_value' && $value2 === 'generated_value' && $callCount === 1) {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m (Callback called $callCount times, expected 1)\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Test 5: Array values
echo "Test 5: Array values... ";
try {
    $cache = Cache::getInstance();
    $array = ['name' => 'John', 'age' => 30];
    $cache->set('test_array', $array, 60);
    $retrieved = $cache->get('test_array');
    
    if ($retrieved === $array) {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Test 6: Clear
echo "Test 6: Clear... ";
try {
    $cache = Cache::getInstance();
    $cache->set('clear_key1', 'value1', 60);
    $cache->set('clear_key2', 'value2', 60);
    
    // Verify they exist
    $has1Before = $cache->has('clear_key1');
    $has2Before = $cache->has('clear_key2');
    
    $cache->clear();
    
    $has1After = $cache->has('clear_key1');
    $has2After = $cache->has('clear_key2');
    
    if ($has1Before && $has2Before && !$has1After && !$has2After) {
        echo "\033[32m✓ PASSED\033[0m\n";
        $passed++;
    } else {
        echo "\033[31m✗ FAILED\033[0m (Before: $has1Before,$has2Before After: $has1After,$has2After)\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "\033[31m✗ FAILED\033[0m ({$e->getMessage()})\n";
    $failed++;
}

// Summary
echo "\n" . str_repeat('=', 50) . "\n";
echo "Test Results:\n";
echo "  Passed: \033[32m$passed\033[0m\n";
echo "  Failed: " . ($failed > 0 ? "\033[31m$failed\033[0m" : "$failed") . "\n";
echo "  Total:  " . ($passed + $failed) . "\n";
echo str_repeat('=', 50) . "\n";

if ($failed > 0) {
    exit(1);
}

echo "\n\033[32m✓ All cache tests passed!\033[0m\n";
