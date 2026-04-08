<?php
/**
 * Check force_password_change flag for a user
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Core\SupabaseConnection;

$email = $argv[1] ?? 'last@gmail.com';

echo "=== Check Force Password Change Flag ===\n\n";
echo "Checking employee: $email\n\n";

try {
    $supabase = new SupabaseConnection();
    
    $employees = $supabase->select('employees', ['work_email' => $email]);
    
    if (empty($employees)) {
        echo "❌ Employee not found\n";
        exit(1);
    }
    
    $employee = $employees[0];
    
    echo "Employee Details:\n";
    echo "  ID: {$employee['id']}\n";
    echo "  Name: {$employee['first_name']} {$employee['last_name']}\n";
    echo "  Work Email: {$employee['work_email']}\n";
    echo "  Force Password Change: " . (($employee['force_password_change'] ?? false) ? 'TRUE ❌' : 'FALSE ✅') . "\n";
    echo "  Password Changed At: " . ($employee['password_changed_at'] ?? 'Never') . "\n";
    echo "  Updated At: {$employee['updated_at']}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
