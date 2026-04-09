<?php
/**
 * Script to remove full-screen loading overlays from admin pages
 */

$files = [
    'src/Views/employees/index.php',
    'src/Views/attendance/index.php',
    'src/Views/leave/index.php',
    'src/Views/reports/index.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⏭️  Skipped: $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove the loading screen HTML
    $content = preg_replace(
        '/<div id="page-loading".*?<\/div>\s*<\/div>/s',
        '',
        $content
    );
    
    // Remove loading screen JavaScript references
    $content = preg_replace(
        '/const loadingScreen = document\.getElementById\([\'"]page-loading[\'"]\);?\s*/s',
        '',
        $content
    );
    
    $content = preg_replace(
        '/loadingScreen\.style\.display\s*=\s*[\'"]none[\'"];?\s*/s',
        '',
        $content
    );
    
    $content = preg_replace(
        '/loadingScreen\.classList\.add\([\'"]hidden[\'"]\);?\s*/s',
        '',
        $content
    );
    
    if ($content === $original) {
        echo "⚠️  No changes: $file\n";
        continue;
    }
    
    file_put_contents($file, $content);
    echo "✅ Updated: $file\n";
}

echo "\n✅ Done! All loading screens removed.\n";
