<?php
/**
 * Fix Force Password Change Defaults
 * Sets force_password_change = TRUE for all employees who haven't changed their password yet
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== FIX FORCE PASSWORD CHANGE DEFAULTS ===\n\n";

try {
    $container = \Core\Container::getInstance();
    $db = $container->resolve(\Core\SupabaseConnection::class);
    
    // Get all employees
    $employees = $db->select('employees', []);
    
    echo "Found " . count($employees) . " employees\n\n";
    
    $fixed = 0;
    $skipped = 0;
    
    foreach ($employees as $employee) {
        $name = $employee['first_name'] . ' ' . $employee['last_name'];
        $forceChange = $employee['force_password_change'] ?? false;
        $passwordChangedAt = $employee['password_changed_at'] ?? null;
        
        echo "Processing: $name\n";
        echo "  Current force_password_change: " . ($forceChange ? 'TRUE' : 'FALSE') . "\n";
        echo "  password_changed_at: " . ($passwordChangedAt ?? 'NULL') . "\n";
        
        // Logic: If password_changed_at is NULL, they haven't changed password yet
        // So force_password_change should be TRUE
        if ($passwordChangedAt === null && $forceChange === false) {
            echo "  → Fixing: Setting force_password_change = TRUE\n";
            
            $updateResult = $db->update('employees', 
                ['force_password_change' => true],
                ['id' => $employee['id']]
            );
            
            if ($updateResult > 0) {
                echo "  ✅ Updated successfully\n";
                $fixed++;
            } else {
                echo "  ❌ Update failed\n";
            }
        } else {
            echo "  ✓ No change needed\n";
            $skipped++;
        }
        
        echo "\n";
    }
    
    echo "=== SUMMARY ===\n";
    echo "Fixed: $fixed employees\n";
    echo "Skipped: $skipped employees\n";
    echo "\n";
    
    // Verify the test user
    echo "=== VERIFYING TEST USER ===\n";
    $testUser = $db->select('employees', ['supabase_user_id' => 'b3cf3158-be4b-4cba-8a36-865adaf2b9ce']);
    
    if (!empty($testUser)) {
        $user = $testUser[0];
        echo "Test Test:\n";
        echo "  force_password_change: " . ($user['force_password_change'] ? 'TRUE ✅' : 'FALSE ❌') . "\n";
        echo "  password_changed_at: " . ($user['password_changed_at'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
