<?php
/**
 * Check Latest Employee
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== CHECK LATEST EMPLOYEE ===\n\n";

try {
    $container = \Core\Container::getInstance();
    $db = $container->resolve(\Core\SupabaseConnection::class);
    
    // Get all employees ordered by created_at
    $employees = $db->select('employees', []);
    
    // Sort by created_at descending
    usort($employees, function($a, $b) {
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });
    
    echo "Latest 3 employees:\n\n";
    
    for ($i = 0; $i < min(3, count($employees)); $i++) {
        $emp = $employees[$i];
        echo ($i + 1) . ". " . $emp['first_name'] . " " . $emp['last_name'] . "\n";
        echo "   Email: " . ($emp['work_email'] ?? 'N/A') . "\n";
        echo "   Created: " . $emp['created_at'] . "\n";
        echo "   Force Password: " . ($emp['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
        echo "   Password Changed: " . ($emp['password_changed_at'] ?? 'NULL') . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
