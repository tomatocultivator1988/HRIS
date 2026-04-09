<?php
/**
 * Update Employee Compensation with Correct Philippine Deductions
 * 
 * This script calculates proper SSS, PhilHealth, Pag-IBIG, and Tax
 * based on 2024 Philippine government rates.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Services\PhilippineDeductionCalculator;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== UPDATE EMPLOYEE DEDUCTIONS ===\n\n";

// Database connection
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
    
    echo "✓ Database connected\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$calculator = new PhilippineDeductionCalculator();

// Get all active employee compensation records
$stmt = $pdo->query("
    SELECT 
        ec.id,
        ec.employee_id,
        ec.base_salary,
        e.first_name,
        e.last_name
    FROM employee_compensation ec
    JOIN employees e ON e.id = ec.employee_id
    WHERE ec.is_active = TRUE
    ORDER BY e.last_name, e.first_name
");

$compensations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($compensations)) {
    echo "No employee compensation records found.\n";
    exit(0);
}

echo "Found " . count($compensations) . " employees\n\n";
echo "Calculating correct deductions...\n\n";

$updateStmt = $pdo->prepare("
    UPDATE employee_compensation
    SET 
        tax_value = :tax,
        sss_employee_share = :sss,
        philhealth_employee_share = :philhealth,
        pagibig_employee_share = :pagibig,
        updated_at = NOW()
    WHERE id = :id
");

foreach ($compensations as $comp) {
    $salary = (float) $comp['base_salary'];
    $name = $comp['first_name'] . ' ' . $comp['last_name'];
    
    // Calculate correct deductions
    $deductions = $calculator->calculateAllDeductions($salary);
    
    // Update database
    $updateStmt->execute([
        'id' => $comp['id'],
        'tax' => $deductions['tax'],
        'sss' => $deductions['sss'],
        'philhealth' => $deductions['philhealth'],
        'pagibig' => $deductions['pagibig']
    ]);
    
    echo "✓ {$name}\n";
    echo "  Salary: ₱" . number_format($salary, 2) . "\n";
    echo "  SSS: ₱" . number_format($deductions['sss'], 2) . "\n";
    echo "  PhilHealth: ₱" . number_format($deductions['philhealth'], 2) . "\n";
    echo "  Pag-IBIG: ₱" . number_format($deductions['pagibig'], 2) . "\n";
    echo "  Tax: ₱" . number_format($deductions['tax'], 2) . "\n";
    echo "  Total Deductions: ₱" . number_format($deductions['total_deductions'], 2) . "\n";
    echo "  Net Pay: ₱" . number_format($deductions['net_pay'], 2) . "\n\n";
}

echo "=== UPDATE COMPLETE ===\n";
echo "All employee deductions have been updated with correct Philippine rates.\n";
