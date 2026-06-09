<?php
/**
 * Clear PHP OpCache
 * Access via: http://localhost:8080/clear-cache.php
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OpCache has been cleared successfully!<br>";
} else {
    echo "❌ OpCache is not enabled.<br>";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC Cache has been cleared successfully!<br>";
}

echo "<br>PHP Version: " . phpversion() . "<br>";
echo "Current Time: " . date('Y-m-d H:i:s') . "<br>";
echo "<br><a href='/'>← Back to App</a>";
