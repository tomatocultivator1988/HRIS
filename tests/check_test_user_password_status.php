<?php
/**
 * Check Password Status for test@gmail.com
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

echo "=== CHECKING PASSWORD STATUS FOR test@gmail.com ===\n\n";

try {
    $supabase = new SupabaseConnection();
    
    // Check users table first
    echo "1. USERS TABLE:\n";
    $users = $supabase->select('users', ['email' => 'test@gmail.com']);
    
    if (!empty($users)) {
        $user = $users[0];
        echo "   User ID: " . $user['id'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Role: " . $user['role'] . "\n";
        echo "   Created: " . $user['created_at'] . "\n\n";
        
        $userId = $user['id'];
        
        // Check employees table
        echo "2. EMPLOYEES TABLE:\n";
        $employees = $supabase->select('employees', ['user_id' => $userId]);
        
        if (!empty($employees)) {
            $employee = $employees[0];
            echo "   Employee ID: " . $employee['id'] . "\n";
            echo "   User ID: " . $employee['user_id'] . "\n";
            echo "   Name: " . $employee['first_name'] . " " . $employee['last_name'] . "\n";
            echo "   Force Password Change: " . ($employee['force_password_change'] ? 'TRUE' : 'FALSE') . "\n";
            echo "   Password Changed At: " . ($employee['password_changed_at'] ?? 'NULL') . "\n";
            echo "   Employee Created: " . $employee['created_at'] . "\n\n";
            
            // Analysis
            echo "3. ANALYSIS:\n";
            if ($employee['force_password_change'] === true) {
                echo "   ✅ force_password_change = TRUE (should redirect to change password)\n";
            } else {
                echo "   ❌ force_password_change = FALSE (will NOT redirect to change password)\n";
            }
            
            if ($employee['password_changed_at'] === null) {
                echo "   ✅ password_changed_at = NULL (never changed password)\n";
            } else {
                echo "   ❌ password_changed_at = " . $employee['password_changed_at'] . " (already changed)\n";
            }
            
            echo "\n4. EXPECTED BEHAVIOR:\n";
            if ($employee['force_password_change'] === true && $employee['password_changed_at'] === null) {
                echo "   ✅ Should redirect to change password page on login\n";
            } else {
                echo "   ❌ Will NOT redirect to change password page\n";
                echo "   ISSUE: force_password_change should be TRUE and password_changed_at should be NULL\n";
            }
            
            echo "\n5. FIX NEEDED:\n";
            if ($employee['force_password_change'] === false) {
                echo "   Run this SQL in Supabase dashboard:\n";
                echo "   UPDATE employees SET force_password_change = TRUE WHERE id = '" . $employee['id'] . "';\n\n";
                
                echo "   Or run the fix script:\n";
                echo "   php tests/fix_force_password_defaults.php\n";
            }
            
        } else {
            echo "   ❌ No employee record found for this user\n";
        }
        
    } else {
        echo "   ❌ User not found with email test@gmail.com\n";
        echo "   Available users:\n";
        $allUsers = $supabase->select('users', []);
        foreach ($allUsers as $u) {
            echo "   - " . $u['email'] . " (role: " . $u['role'] . ")\n";
        }
    }
    
    echo "\n=== CHECKING ALL EMPLOYEES WITH force_password_change = FALSE ===\n";
    $allEmployees = $supabase->select('employees', []);
    $falseEmployees = array_filter($allEmployees, function($emp) {
        return $emp['force_password_change'] === false;
    });
    
    if (!empty($falseEmployees)) {
        echo "Found " . count($falseEmployees) . " employees with force_password_change = FALSE:\n";
        foreach ($falseEmployees as $emp) {
            echo "   - " . $emp['first_name'] . " " . $emp['last_name'] . 
                 " (ID: " . $emp['id'] . 
                 ", password_changed_at: " . ($emp['password_changed_at'] ?? 'NULL') . ")\n";
        }
        
        echo "\nTo fix all of them, run:\n";
        echo "php tests/fix_force_password_defaults.php\n";
    } else {
        echo "No employees found with force_password_change = FALSE\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}