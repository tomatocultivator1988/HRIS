<?php
// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!\n";
} else {
    echo "OPcache is not enabled.\n";
}

// Also clear realpath cache
clearstatcache(true);
echo "Realpath cache cleared!\n";

echo "\nNow restart Apache in XAMPP Control Panel.\n";
