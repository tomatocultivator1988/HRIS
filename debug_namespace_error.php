<?php
/**
 * Namespace Error Diagnostic Script
 * 
 * This script will help identify which file is causing the namespace declaration error
 * by checking each file in the bootstrap chain for issues.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Namespace Error Diagnostic</h1>";
echo "<pre>";

// Define application root
define('APP_ROOT', __DIR__);

echo "=== DIAGNOSTIC START ===\n\n";
echo "Application Root: " . APP_ROOT . "\n\n";

// List of files to check in order of loading
$filesToCheck = [
    'src/autoload.php',
    'src/Config/helpers.php',
    'src/Core/Container.php',
    'src/Core/Router.php',
    'src/Core/RouteCache.php',
    'src/Core/Request.php',
    'src/Core/Response.php',
    'src/Core/View.php',
    'src/Core/Cache.php',
    'src/Core/DatabaseConnectionPool.php',
    'src/Core/QueryOptimizer.php',
    'src/bootstrap.php',
];

echo "=== CHECKING FILES FOR ISSUES ===\n\n";

foreach ($filesToCheck as $file) {
    $fullPath = APP_ROOT . '/' . $file;
    
    echo "Checking: $file\n";
    
    if (!file_exists($fullPath)) {
        echo "  ❌ FILE NOT FOUND\n\n";
        continue;
    }
    
    // Read file content
    $content = file_get_contents($fullPath);
    
    // Check for BOM
    $bom = substr($content, 0, 3);
    if ($bom === "\xEF\xBB\xBF") {
        echo "  ❌ BOM DETECTED (UTF-8 BOM found)\n";
    } else {
        echo "  ✓ No BOM\n";
    }
    
    // Check for whitespace before <?php
    if (preg_match('/^(\s+)<\?php/', $content, $matches)) {
        echo "  ❌ WHITESPACE BEFORE <?php TAG: " . strlen($matches[1]) . " characters\n";
        echo "     Hex: " . bin2hex($matches[1]) . "\n";
    } else {
        echo "  ✓ No whitespace before <?php\n";
    }
    
    // Check for closing ?> tag
    if (preg_match('/\?>\s*$/', $content)) {
        echo "  ⚠️  CLOSING ?> TAG FOUND (can cause output)\n";
    } else {
        echo "  ✓ No closing ?> tag\n";
    }
    
    // Check first line
    $firstLine = strtok($content, "\n");
    echo "  First line: " . trim($firstLine) . "\n";
    
    // Check if file has namespace declaration
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        echo "  Namespace: " . $matches[1] . "\n";
        
        // Find line number of namespace
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'namespace') !== false) {
                echo "  Namespace on line: " . ($lineNum + 1) . "\n";
                
                // Check what's before namespace
                if ($lineNum > 0) {
                    echo "  Lines before namespace:\n";
                    for ($i = 0; $i < $lineNum && $i < 5; $i++) {
                        $checkLine = $lines[$i];
                        echo "    Line " . ($i + 1) . ": " . trim($checkLine) . "\n";
                        
                        // Check if there's any output before namespace
                        if (trim($checkLine) !== '' && 
                            !preg_match('/^<\?php/', $checkLine) && 
                            !preg_match('/^\/\*/', $checkLine) &&
                            !preg_match('/^\*/', $checkLine) &&
                            !preg_match('/^\/\//', $checkLine)) {
                            echo "    ⚠️  POTENTIAL OUTPUT BEFORE NAMESPACE\n";
                        }
                    }
                }
                break;
            }
        }
    } else {
        echo "  No namespace declaration\n";
    }
    
    echo "\n";
}

echo "\n=== ATTEMPTING TO LOAD FILES SEQUENTIALLY ===\n\n";

// Try loading autoload.php
echo "1. Loading autoload.php...\n";
try {
    require_once APP_ROOT . '/src/autoload.php';
    echo "   ✓ SUCCESS\n\n";
} catch (Throwable $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    echo "=== ERROR FOUND - STOPPING ===\n";
    exit;
}

// Try loading Container
echo "2. Loading Container.php...\n";
try {
    if (!class_exists('Core\Container')) {
        require_once APP_ROOT . '/src/Core/Container.php';
    }
    echo "   ✓ SUCCESS\n\n";
} catch (Throwable $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    echo "=== ERROR FOUND - STOPPING ===\n";
    exit;
}

// Try loading Router
echo "3. Loading Router.php...\n";
try {
    if (!class_exists('Core\Router')) {
        require_once APP_ROOT . '/src/Core/Router.php';
    }
    echo "   ✓ SUCCESS\n\n";
} catch (Throwable $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    echo "=== ERROR FOUND - STOPPING ===\n";
    exit;
}

// Try loading bootstrap
echo "4. Loading bootstrap.php...\n";
try {
    require_once APP_ROOT . '/src/bootstrap.php';
    echo "   ✓ SUCCESS\n\n";
} catch (Throwable $e) {
    echo "   ❌ FAILED: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    echo "=== ERROR FOUND - STOPPING ===\n";
    exit;
}

echo "=== ALL FILES LOADED SUCCESSFULLY ===\n\n";

echo "=== CHECKING AUTOLOAD CHAIN ===\n";
echo "Registered autoloaders:\n";
$autoloaders = spl_autoload_functions();
foreach ($autoloaders as $loader) {
    if (is_array($loader)) {
        echo "  - " . get_class($loader[0]) . "::" . $loader[1] . "\n";
    } else {
        echo "  - " . $loader . "\n";
    }
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "</pre>";
