<?php
/**
 * Router.php Specific Diagnostic
 * 
 * This script focuses on diagnosing the Router.php namespace error
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Router.php Namespace Error Diagnostic</h1>";
echo "<pre>";

$routerPath = __DIR__ . '/src/Core/Router.php';

echo "=== ROUTER.PHP ANALYSIS ===\n\n";
echo "File: $routerPath\n\n";

if (!file_exists($routerPath)) {
    echo "❌ FILE NOT FOUND!\n";
    exit;
}

// Read the file
$content = file_get_contents($routerPath);
$lines = explode("\n", $content);

echo "Total lines: " . count($lines) . "\n";
echo "File size: " . strlen($content) . " bytes\n\n";

// Check first 10 bytes in hex
echo "First 10 bytes (hex): " . bin2hex(substr($content, 0, 10)) . "\n";
echo "First 10 bytes (raw): " . var_export(substr($content, 0, 10), true) . "\n\n";

// Check for BOM
$bom = substr($content, 0, 3);
if ($bom === "\xEF\xBB\xBF") {
    echo "❌ UTF-8 BOM DETECTED!\n\n";
} else {
    echo "✓ No BOM\n\n";
}

// Show first 10 lines with line numbers
echo "=== FIRST 10 LINES ===\n";
for ($i = 0; $i < 10 && $i < count($lines); $i++) {
    $lineNum = $i + 1;
    $line = $lines[$i];
    $hex = bin2hex($line);
    
    echo "Line $lineNum: " . var_export($line, true) . "\n";
    echo "  Hex: $hex\n";
    
    // Check for issues
    if ($lineNum === 1 && !preg_match('/^<\?php/', $line)) {
        echo "  ❌ Line 1 should start with <?php\n";
    }
    if ($lineNum === 3 && !preg_match('/^namespace/', $line)) {
        echo "  ⚠️  Line 3 should be namespace declaration\n";
    }
    echo "\n";
}

echo "\n=== CHECKING FOR OUTPUT BEFORE NAMESPACE ===\n";

// Find namespace line
$namespaceLineNum = null;
foreach ($lines as $idx => $line) {
    if (preg_match('/^namespace\s+/', $line)) {
        $namespaceLineNum = $idx + 1;
        break;
    }
}

if ($namespaceLineNum) {
    echo "Namespace found on line: $namespaceLineNum\n\n";
    
    echo "Content before namespace:\n";
    for ($i = 0; $i < $namespaceLineNum - 1; $i++) {
        $line = $lines[$i];
        $trimmed = trim($line);
        
        echo "Line " . ($i + 1) . ": ";
        
        if ($trimmed === '') {
            echo "(empty line)\n";
        } elseif (preg_match('/^<\?php/', $trimmed)) {
            echo "<?php tag ✓\n";
        } elseif (preg_match('/^\/\*/', $trimmed) || preg_match('/^\*/', $trimmed) || preg_match('/^\/\//', $trimmed)) {
            echo "Comment ✓\n";
        } else {
            echo "❌ POTENTIAL ISSUE: " . var_export($line, true) . "\n";
        }
    }
} else {
    echo "❌ NAMESPACE DECLARATION NOT FOUND!\n";
}

echo "\n=== ATTEMPTING TO LOAD ROUTER.PHP ===\n";

// First, we need to load dependencies
echo "Loading dependencies...\n";

try {
    // Load autoload first
    if (file_exists(__DIR__ . '/src/autoload.php')) {
        require_once __DIR__ . '/src/autoload.php';
        echo "✓ Autoload loaded\n";
    }
    
    // Try to load Router
    echo "Attempting to load Router.php...\n";
    require_once $routerPath;
    echo "✓ Router.php loaded successfully!\n";
    
    echo "\n✓✓✓ NO ERROR DETECTED - Router.php loads fine in isolation!\n";
    echo "This suggests the error occurs due to something loaded BEFORE Router.php\n";
    
} catch (Throwable $e) {
    echo "\n❌ ERROR CAUGHT:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "</pre>";
