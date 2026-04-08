<?php
/**
 * Check User Login Issue
 * Debug script to check if user exists in Supabase Auth and database
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Services\AuthService;
use Models\User;
use Core\Container;

echo "=== User Login Debug Script ===\n\n";

$email = 'last@gmail.com';
$password = 'first09123456789';

echo "Testing login for: $email\n\n";

try {
    $container = Container::getInstance();
    $authService = $container->resolve(AuthService::class);
    $userModel = $container->resolve(User::class);
    
    echo "Step 1: Checking if user exists in database...\n";
    $dbUser = $userModel->findByEmail($email);
    
    if ($dbUser) {
        echo "✅ User found in database:\n";
        echo "   - ID: " . ($dbUser['id'] ?? 'N/A') . "\n";
        echo "   - Role: " . ($dbUser['role'] ?? 'N/A') . "\n";
        echo "   - Name: " . ($dbUser['first_name'] ?? '') . " " . ($dbUser['last_name'] ?? '') . "\n";
        echo "   - Active: " . ($dbUser['is_active'] ? 'Yes' : 'No') . "\n";
        echo "   - Supabase User ID: " . ($dbUser['supabase_user_id'] ?? 'N/A') . "\n\n";
    } else {
        echo "❌ User NOT found in database (employees or admins table)\n";
        echo "   This is the problem! User exists in Supabase Auth but not in database.\n\n";
    }
    
    echo "Step 2: Attempting authentication with Supabase...\n";
    $authResult = $authService->authenticate($email, $password);
    
    if ($authResult['success']) {
        echo "✅ Authentication successful!\n";
        echo "   - Access Token: " . substr($authResult['access_token'], 0, 20) . "...\n";
        echo "   - User ID: " . $authResult['user']['id'] . "\n";
        echo "   - Role: " . $authResult['user']['role'] . "\n";
        echo "   - Name: " . $authResult['user']['name'] . "\n\n";
    } else {
        echo "❌ Authentication failed!\n";
        echo "   - Error: " . $authResult['message'] . "\n\n";
    }
    
    echo "=== Diagnosis ===\n";
    if (!$dbUser) {
        echo "PROBLEM: User exists in Supabase Auth but NOT in database.\n\n";
        echo "SOLUTION: You need to create an employee record in the database.\n";
        echo "You can do this by:\n";
        echo "1. Using the admin panel to create employee\n";
        echo "2. Running SQL to insert employee record\n";
        echo "3. Using the API endpoint POST /api/employees\n\n";
        
        echo "Sample SQL to create employee record:\n";
        echo "INSERT INTO employees (\n";
        echo "  employee_id, supabase_user_id, first_name, last_name,\n";
        echo "  work_email, department, position, employment_status,\n";
        echo "  date_hired, is_active\n";
        echo ") VALUES (\n";
        echo "  'EMP001', 'SUPABASE_USER_ID_HERE', 'First', 'Last',\n";
        echo "  'last@gmail.com', 'IT', 'Developer', 'Regular',\n";
        echo "  CURRENT_DATE, true\n";
        echo ");\n\n";
        
        echo "Note: You need to get the Supabase User ID from Supabase Auth dashboard.\n";
    } else if (!$authResult['success']) {
        echo "PROBLEM: User exists in database but authentication failed.\n";
        echo "Possible causes:\n";
        echo "1. Wrong password\n";
        echo "2. User not active in Supabase Auth\n";
        echo "3. Supabase configuration issue\n";
    } else {
        echo "✅ Everything looks good! Login should work.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== End of Debug Script ===\n";
