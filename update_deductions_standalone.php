<?php
/**
 * Update Employee Compensation with Correct Philippine Deductions
 * Standalone version - no dependencies required
 */

class PhilippineDeductionCalculator
{
    public function calculateSSS(float $monthlySalary): float
    {
        $sssTable = [
            [4249.99, 180.00], [4749.99, 202.50], [5249.99, 225.00],
            [5749.99, 247.50], [6249.99, 270.00], [6749.99, 292.50],
            [7249.99, 315.00], [7749.99, 337.50], [8249.99, 360.00],
            [8749.99, 382.50], [9249.99, 405.00], [9749.99, 427.50],
            [10249.99, 450.00], [10749.99, 472.50], [11249.99, 495.00],
            [11749.99, 517.50], [12249.99, 540.00], [12749.99, 562.50],
            [13249.99, 585.00], [13749.99, 607.50], [14249.99, 630.00],
            [14749.99, 652.50], [15249.99, 675.00], [15749.99, 697.50],
            [16249.99, 720.00], [16749.99, 742.50], [17249.99, 765.00],
            [17749.99, 787.50], [18249.99, 810.00], [18749.99, 832.50],
            [19249.99, 855.00], [19749.99, 877.50], [20249.99, 900.00],
            [20749.99, 922.50], [21249.99, 945.00], [21749.99, 967.50],
            [22249.99, 990.00], [22749.99, 1012.50], [23249.99, 1035.00],
            [23749.99, 1057.50], [24249.99, 1080.00], [24749.99, 1102.50],
            [25249.99, 1125.00], [25749.99, 1147.50], [26249.99, 1170.00],
            [26749.99, 1192.50], [27249.99, 1215.00], [27749.99, 1237.50],
            [28249.99, 1260.00], [28749.99, 1282.50], [29249.99, 1305.00],
            [29749.99, 1327.50], [PHP_FLOAT_MAX, 1350.00]
        ];

        foreach ($sssTable as [$maxSalary, $employeeShare]) {
            if ($monthlySalary <= $maxSalary) {
                return $employeeShare;
            }
        }
        return 1350.00;
    }

    public function calculatePhilHealth(float $monthlySalary): float
    {
        if ($monthlySalary <= 10000) {
            return 250.00;
        }
        return min($monthlySalary * 0.025, 2500.00);
    }

    public function calculatePagIBIG(float $monthlySalary): float
    {
        if ($monthlySalary <= 1500) {
            return max($monthlySalary * 0.01, 15.00);
        }
        return min($monthlySalary * 0.02, 100.00);
    }

    public function calculateWithholdingTax(float $monthlySalary, float $sss, float $philhealth, float $pagibig): float
    {
        $taxableIncome = $monthlySalary - $sss - $philhealth - $pagibig;

        if ($taxableIncome <= 20833) {
            return 0;
        } elseif ($taxableIncome <= 33332) {
            return ($taxableIncome - 20833) * 0.15;
        } elseif ($taxableIncome <= 66666) {
            return 1875 + (($taxableIncome - 33333) * 0.20);
        } elseif ($taxableIncome <= 166666) {
            return 8541.80 + (($taxableIncome - 66667) * 0.25);
        } elseif ($taxableIncome <= 666666) {
            return 33541.80 + (($taxableIncome - 166667) * 0.30);
        } else {
            return 183541.80 + (($taxableIncome - 666667) * 0.35);
        }
    }

    public function calculateAll(float $monthlySalary): array
    {
        $sss = $this->calculateSSS($monthlySalary);
        $philhealth = $this->calculatePhilHealth($monthlySalary);
        $pagibig = $this->calculatePagIBIG($monthlySalary);
        $tax = $this->calculateWithholdingTax($monthlySalary, $sss, $philhealth, $pagibig);

        return [
            'sss' => round($sss, 2),
            'philhealth' => round($philhealth, 2),
            'pagibig' => round($pagibig, 2),
            'tax' => round($tax, 2),
            'total_deductions' => round($sss + $philhealth + $pagibig + $tax, 2),
            'net_pay' => round($monthlySalary - ($sss + $philhealth + $pagibig + $tax), 2)
        ];
    }
}

// Load .env file manually
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found at $path\n");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

echo "=== UPDATE EMPLOYEE DEDUCTIONS ===\n\n";

// Load environment variables
loadEnv(__DIR__ . '/.env');

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
    
    echo "✓ Database connected\n";
    echo "  Host: " . ($_ENV['DB_HOST'] ?? 'localhost') . "\n";
    echo "  Database: " . ($_ENV['DB_NAME'] ?? 'hris') . "\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your .env file settings:\n";
    echo "  DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD\n";
    exit(1);
}

$calculator = new PhilippineDeductionCalculator();

// Get all active employee compensation records
try {
    $stmt = $pdo->query("
        SELECT 
            ec.id,
            ec.employee_id,
            ec.base_salary,
            ec.tax_value as old_tax,
            ec.sss_employee_share as old_sss,
            ec.philhealth_employee_share as old_philhealth,
            ec.pagibig_employee_share as old_pagibig,
            e.first_name,
            e.last_name
        FROM employee_compensation ec
        JOIN employees e ON e.id = ec.employee_id
        WHERE ec.is_active = TRUE
        ORDER BY e.last_name, e.first_name
    ");
    
    $compensations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "✗ Error reading employee data: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($compensations)) {
    echo "No employee compensation records found.\n";
    exit(0);
}

echo "Found " . count($compensations) . " employees\n\n";
echo "WARNING: This will update deduction values in the database!\n";
echo "Press Ctrl+C to cancel, or press Enter to continue...\n";
fgets(STDIN);

echo "\nCalculating and updating correct deductions...\n\n";

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

$updated = 0;
foreach ($compensations as $comp) {
    $salary = (float) $comp['base_salary'];
    $name = $comp['first_name'] . ' ' . $comp['last_name'];
    
    // Calculate correct deductions
    $deductions = $calculator->calculateAll($salary);
    
    // Update database
    try {
        $updateStmt->execute([
            'id' => $comp['id'],
            'tax' => $deductions['tax'],
            'sss' => $deductions['sss'],
            'philhealth' => $deductions['philhealth'],
            'pagibig' => $deductions['pagibig']
        ]);
        
        echo "✓ {$name}\n";
        echo "  Salary: ₱" . number_format($salary, 2) . "\n";
        echo "  OLD → NEW\n";
        echo "  SSS:        ₱" . number_format($comp['old_sss'], 2) . " → ₱" . number_format($deductions['sss'], 2) . "\n";
        echo "  PhilHealth: ₱" . number_format($comp['old_philhealth'], 2) . " → ₱" . number_format($deductions['philhealth'], 2) . "\n";
        echo "  Pag-IBIG:   ₱" . number_format($comp['old_pagibig'], 2) . " → ₱" . number_format($deductions['pagibig'], 2) . "\n";
        echo "  Tax:        ₱" . number_format($comp['old_tax'], 2) . " → ₱" . number_format($deductions['tax'], 2) . "\n";
        echo "  Net Pay:    ₱" . number_format($deductions['net_pay'], 2) . "\n\n";
        
        $updated++;
    } catch (PDOException $e) {
        echo "✗ Failed to update {$name}: " . $e->getMessage() . "\n\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo "UPDATE COMPLETE\n";
echo str_repeat('=', 60) . "\n";
echo "Updated {$updated} out of " . count($compensations) . " employees\n";
echo "All employee deductions have been updated with correct Philippine rates.\n\n";
echo "Note: Existing payroll runs are NOT affected.\n";
echo "You need to regenerate payroll to use the new deduction values.\n";
