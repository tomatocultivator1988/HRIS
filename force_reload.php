<?php
/**
 * Force reload all PHP files by clearing opcache and route cache
 */

// Clear opcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache cleared<br>";
} else {
    echo "✗ OPcache not enabled<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "✓ Realpath cache cleared<br>";

// Delete route cache if exists
$routeCacheFile = __DIR__ . '/storage/cache/routes.php';
if (file_exists($routeCacheFile)) {
    unlink($routeCacheFile);
    echo "✓ Route cache deleted<br>";
} else {
    echo "✗ No route cache file found<br>";
}

// Force reload routes by requiring the file
require_once __DIR__ . '/config/routes.php';
echo "✓ Routes file reloaded<br>";

echo "<br><strong>All caches cleared! Now restart Apache and test again.</strong><br>";
echo "<br><a href='test_profile_api.php'>→ Test Profile API</a>";
echo "<br><a href='profile'>→ Go to Profile Page</a>";
