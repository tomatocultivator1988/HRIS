<?php
/**
 * Find the user for Testt Testt employee
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

echo "=== FINDING USER FOR TESTT TESTT EMPLOYEE ===\n\n";

try {
    $supabase = new SupabaseConnection();
    
    // Find Testt Testt employee
    echo "1. FINDING TESTT TESTT EMPLOYEE:\n";
    $employees = $supabase->select('employees', []);
    
    $testtEmployee = null;
    foreach ($employees as $emp) {
        if ($emp['first_name'] === 'Testt' && $emp['last_name'] === 'Testt') {
            $testtEmployee = $emp;
            break;
        }
    }
    
    if ($testtEmployee) {
        echo "   Employee ID: " . $testtEmployee['id'] . "\n";
        echo "   Name: " . $testtEmployee['first_name'] . " " . $testtEmployee['last_name'] . "\n";
        echo "   User ID: " . $testtEmployee['user_id'] . "\n";
        echo "   Force Password Change: " . ($testtEmployee['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
        echo "   Password Changed At: " . ($testtEmployee['password_changed_at'] ?? 'NULL') . "\n\n";
        
        // Find the user
        echo "2. FINDING CORRESPONDING USER:\n";
        $users = $supabase->select('users', ['id' => $testtEmployee['user_id']]);
        
        if (!empty($users)) {
            $user = $users[0];
            echo "   User ID: " . $user['id'] . "\n";
            echo "   Email: " . $user['email'] . "\n";
            echo "   Role: " . $user['role'] . "\n";
            echo "   Created: " . $user['created_at'] . "\n\n";
            
            echo "3. ANALYSIS:\n";
            echo "   The user email is: " . $user['email'] . "\n";
            echo "   Force password change: " . ($testtEmployee['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
            
            if ($testtEmployee['force_password_change'] === false) {
                echo "\n4. ISSUE FOUND:\n";
                echo "   ❌ force_password_change = FALSE (should be TRUE for new employees)\n";
                echo "   ❌ This is why the user is NOT redirected to change password page\n\n";
                
                echo "5. FIX:\n";
                echo "   Run: php tests/fix_force_password_defaults.php\n";
                echo "   Or manually update in Supabase:\n";
                echo "   UPDATE employees SET force_password_change = TRUE WHERE id = '" . $testtEmployee['id'] . "';\n";
            } else {
                echo "   ✅ force_password_change = TRUE (correct)\n";
            }
            
        } else {
            echo "   ❌ No user found with ID: " . $testtEmployee['user_id'] . "\n";
        }
        
    } else {
        echo "   ❌ No employee found with name 'Testt Testt'\n";
        echo "   Available employees:\n";
        foreach ($employees as $emp) {
            echo "   - " . $emp['first_name'] . " " . $emp['last_name'] . "\n";
        }
    }
    
    echo "\n=== ALL USERS IN DATABASE ===\n";
    $allUsers = $supabase->select('users', []);
    foreach ($allUsers as $user) {
        echo "   - " . $user['email'] . " (role: " . $user['role'] . ", id: " . $user['id'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}