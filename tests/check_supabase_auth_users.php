<?php
/**
 * Check Supabase Auth Users and Employee Mapping
 */

require_once __DIR__ . '/../src/bootstrap.php';

echo "=== CHECKING SUPABASE AUTH USERS ===\n\n";

try {
    // Load Supabase config
    $config = require __DIR__ . '/../config/supabase.php';
    
    $supabaseUrl = $config['url'];
    $supabaseServiceKey = $config['service_key']; // Use service key for admin access
    
    // Check Supabase Auth users
    echo "1. SUPABASE AUTH USERS:\n";
    $authUsersUrl = $supabaseUrl . '/auth/v1/admin/users';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'apikey: ' . $supabaseServiceKey,
                'Authorization: Bearer ' . $supabaseServiceKey,
                'Content-Type: application/json'
            ]
        ]
    ]);
    
    $authUsersResult = file_get_contents($authUsersUrl, false, $context);
    
    if ($authUsersResult === false) {
        echo "   ❌ Failed to fetch auth users\n";
        echo "   This might be because we don't have admin access to auth users\n\n";
    } else {
        $authUsers = json_decode($authUsersResult, true);
        
        if (isset($authUsers['users']) && !empty($authUsers['users'])) {
            echo "   Found " . count($authUsers['users']) . " auth users:\n";
            foreach ($authUsers['users'] as $user) {
                echo "   - " . $user['email'] . " (id: " . $user['id'] . ")\n";
            }
        } else {
            echo "   No auth users found or access denied\n";
            echo "   Response: " . $authUsersResult . "\n";
        }
    }
    
    echo "\n2. EMPLOYEES TABLE (with supabase_user_id):\n";
    $employeesUrl = $supabaseUrl . '/rest/v1/employees?select=id,first_name,last_name,supabase_user_id,force_password_change,password_changed_at';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'apikey: ' . $supabaseServiceKey,
                'Authorization: Bearer ' . $supabaseServiceKey,
                'Content-Type: application/json'
            ]
        ]
    ]);
    
    $employeesResult = file_get_contents($employeesUrl, false, $context);
    $employees = json_decode($employeesResult, true);
    
    if (!empty($employees)) {
        echo "   Found " . count($employees) . " employees:\n";
        foreach ($employees as $emp) {
            echo "   - " . $emp['first_name'] . " " . $emp['last_name'] . 
                 " (supabase_user_id: " . ($emp['supabase_user_id'] ?? 'NULL') . 
                 ", force_password_change: " . ($emp['force_password_change'] ? 'TRUE' : 'FALSE') . ")\n";
        }
    } else {
        echo "   No employees found\n";
        echo "   Response: " . $employeesResult . "\n";
    }
    
    echo "\n3. ANALYSIS:\n";
    echo "   The system uses Supabase Auth for authentication\n";
    echo "   Employee records should have supabase_user_id that matches auth user id\n";
    echo "   If test@gmail.com exists in auth but employee has no supabase_user_id, that's the issue\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}