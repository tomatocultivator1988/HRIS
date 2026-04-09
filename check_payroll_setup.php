<?php
/**
 * Payroll Setup Diagnostic Script
 * Run this to check if payroll tables exist and see existing data
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== PAYROLL SETUP DIAGNOSTIC ===\n\n";

// Check database connection
try {
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_PORT'] ?? '5432',
        $_ENV['DB_NAME'] ?? 'hris'
    );
    
    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USER'] ?? 'postgres',
        $_ENV['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✓ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if payroll tables exist
$tables = [
    'payroll_periods',
    'employee_compensation',
    'payroll_runs',
    'payroll_line_items',
    'payroll_adjustments'
];

echo "Checking payroll tables:\n";
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "  ✓ $table exists ($count rows)\n";
    } catch (PDOException $e) {
        echo "  ✗ $table does NOT exist or is inaccessible\n";
        echo "     Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Check existing payroll periods
try {
    $stmt = $pdo->query("SELECT code, start_date, end_date, pay_date, status FROM payroll_periods ORDER BY start_date DESC LIMIT 10");
    $periods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($periods)) {
        echo "No payroll periods found. You can create your first one!\n\n";
    } else {
        echo "Existing payroll periods:\n";
        foreach ($periods as $period) {
            echo sprintf(
                "  - %s: %s to %s (pay: %s) [%s]\n",
                $period['code'],
                $period['start_date'],
                $period['end_date'],
                $period['pay_date'],
                $period['status']
            );
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "Could not query payroll_periods: " . $e->getMessage() . "\n\n";
}

// Check employee compensation
try {
    $stmt = $pdo->query("
        SELECT 
            ec.employee_id,
            e.first_name,
            e.last_name,
            ec.payroll_type,
            ec.base_salary,
            ec.is_active
        FROM employee_compensation ec
        JOIN employees e ON e.id = ec.employee_id
        WHERE ec.is_active = TRUE
        LIMIT 5
    ");
    $compensations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($compensations)) {
        echo "⚠ WARNING: No employee compensation records found!\n";
        echo "   Run this to create default compensation for all employees:\n";
        echo "   psql -U your_user -d your_database -f docs/migrations/seed_payroll_defaults.sql\n\n";
    } else {
        echo "Employee compensation (sample):\n";
        foreach ($compensations as $comp) {
            echo sprintf(
                "  - %s %s: %s (₱%.2f)\n",
                $comp['first_name'],
                $comp['last_name'],
                $comp['payroll_type'],
                $comp['base_salary']
            );
        }
        echo "\n";
    }
} catch (PDOException $e) {
    echo "Could not query employee_compensation: " . $e->getMessage() . "\n\n";
}

// Check active employees
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE is_active = TRUE");
    $activeEmployees = $stmt->fetchColumn();
    echo "Active employees: $activeEmployees\n\n";
} catch (PDOException $e) {
    echo "Could not count employees: " . $e->getMessage() . "\n\n";
}

echo "=== DIAGNOSTIC COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. If tables don't exist, run: psql -U your_user -d your_database -f docs/migrations/create_payroll_tables.sql\n";
echo "2. If no compensation records, run: psql -U your_user -d your_database -f docs/migrations/seed_payroll_defaults.sql\n";
echo "3. Try creating a payroll period at: http://localhost/HRIS/payroll/simple\n";
