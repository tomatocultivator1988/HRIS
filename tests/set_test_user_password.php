<?php
/**
 * Script to set force_password_change flag for test user
 * Usage: php tests/set_test_user_password.php [email]
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

echo "=== Set Test User Force Password Change ===\n\n";

// Get email from command line argument or use default
$email = $argv[1] ?? 'last@gmail.com';

try {
    $supabase = new SupabaseConnection();
    
    // Step 1: Find user by work_email
    echo "Step 1: Finding employee with work_email: $email\n";
    
    $employees = $supabase->select('employees', ['work_email' => $email]);
    
    if (empty($employees)) {
        echo "❌ Employee not found with work_email: $email\n";
        echo "\nTrying to list all employees...\n\n";
        
        $allEmployees = $supabase->select('employees', []);
        if (!empty($allEmployees)) {
            echo "Available employees:\n";
            foreach ($allEmployees as $emp) {
                $workEmail = $emp['work_email'] ?? 'N/A';
                $name = ($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '');
                echo "  - $workEmail ($name)\n";
            }
        }
        exit(1);
    }
    
    $employee = $employees[0];
    echo "✅ Employee found: {$employee['first_name']} {$employee['last_name']}\n";
    echo "   ID: {$employee['id']}\n";
    echo "   Work Email: {$employee['work_email']}\n";
    echo "   Current force_password_change: " . (($employee['force_password_change'] ?? false) ? 'TRUE' : 'FALSE') . "\n\n";
    
    // Step 2: Update force_password_change flag
    echo "Step 2: Setting force_password_change = TRUE\n";
    
    $updated = $supabase->update('employees', [
        'force_password_change' => true,
        'updated_at' => date('Y-m-d H:i:s')
    ], ['id' => $employee['id']]);
    
    if ($updated === 0) {
        echo "❌ Failed to update\n";
        exit(1);
    }
    
    echo "✅ Successfully set force_password_change = TRUE\n\n";
    
    // Step 3: Verify update
    echo "Step 3: Verifying update\n";
    
    $verifyEmployees = $supabase->select('employees', ['work_email' => $email]);
    
    if (!empty($verifyEmployees)) {
        $updated = $verifyEmployees[0];
        echo "✅ Verified:\n";
        echo "   force_password_change: " . (($updated['force_password_change'] ?? false) ? 'TRUE' : 'FALSE') . "\n";
        echo "   updated_at: {$updated['updated_at']}\n\n";
    }
    
    echo "=== Test Instructions ===\n";
    echo "1. Go to: http://localhost/HRIS/login\n";
    echo "2. Login with:\n";
    echo "   Email: $email\n";
    echo "   Password: [your password]\n";
    echo "3. You should be redirected to: http://localhost/HRIS/password/change\n";
    echo "4. Change your password\n";
    echo "5. After successful change, you'll be redirected to dashboard\n\n";
    
    echo "✅ Setup complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
