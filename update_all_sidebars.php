<?php
/**
 * Script to update all admin pages to use the standard sidebar
 */

$pages = [
    'src/Views/employees/index.php' => 'employees',
    'src/Views/attendance/index.php' => 'attendance',
    'src/Views/leave/index.php' => 'leave',
    'src/Views/reports/index.php' => 'reports',
    'src/Views/payroll/simple.php' => 'payroll',
    'src/Views/payroll/manage.php' => 'payroll',
];

foreach ($pages as $file => $pageName) {
    if (!file_exists($file)) {
        echo "⏭️  Skipped: $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Check if it already uses the standard sidebar
    if (strpos($content, 'admin_sidebar.php') !== false) {
        echo "✅ Already updated: $file\n";
        continue;
    }
    
    // Find the sidebar section and replace it
    $pattern = '/<aside class="w-64 bg-slate-800.*?<\/aside>/s';
    $replacement = '<?php $currentPage = \'' . $pageName . '\'; include __DIR__ . \'/../layouts/admin_sidebar.php\'; ?>';
    
    $newContent = preg_replace($pattern, $replacement, $content);
    
    if ($newContent === $content) {
        echo "⚠️  No changes: $file (pattern not found)\n";
        continue;
    }
    
    file_put_contents($file, $newContent);
    echo "✅ Updated: $file\n";
}

echo "\n✅ Done! All sidebars updated.\n";
