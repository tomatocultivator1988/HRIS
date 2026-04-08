<?php
/**
 * Script to list all users (from users table)
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

echo "=== List All Users ===\n\n";

try {
    $supabase = new SupabaseConnection();
    
    // Try users table
    echo "Checking 'users' table:\n";
    $users = $supabase->select('users', []);
    
    if (empty($users)) {
        echo "No users found in 'users' table.\n\n";
    } else {
        echo "Found " . count($users) . " user(s) in 'users' table:\n\n";
        
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "Email: " . ($user['email'] ?? 'N/A') . "\n";
            echo "Role: " . ($user['role'] ?? 'N/A') . "\n";
            echo "Force Password Change: " . (($user['force_password_change'] ?? false) ? 'TRUE' : 'FALSE') . "\n";
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
