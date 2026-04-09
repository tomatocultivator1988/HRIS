<?php
/**
 * Update Employee Compensation with Correct Philippine Deductions
 * Supabase version
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

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found\n");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');

$supabaseUrl = $_ENV['SUPABASE_URL'];
$supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'];

echo "=== UPDATE EMPLOYEE DEDUCTIONS (SUPABASE) ===\n\n";
echo "✓ Connected to Supabase\n";
echo "  URL: {$supabaseUrl}\n\n";

$calculator = new PhilippineDeductionCalculator();

// Fetch employee compensation with employee details
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$supabaseUrl}/rest/v1/employee_compensation?is_active=eq.true&select=*,employees(first_name,last_name)");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$supabaseKey}",
    "Authorization: Bearer {$supabaseKey}",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("✗ Failed to fetch employee data (HTTP {$httpCode})\n");
}

$compensations = json_decode($response, true);

if (empty($compensations)) {
    echo "No employee compensation records found.\n";
    exit(0);
}

echo "Found " . count($compensations) . " employees\n\n";
echo "WARNING: This will update deduction values in Supabase!\n";
echo "Press Ctrl+C to cancel, or press Enter to continue...\n";
fgets(STDIN);

echo "\nUpdating deductions...\n\n";

$updated = 0;
foreach ($compensations as $comp) {
    $salary = (float) $comp['base_salary'];
    $employee = $comp['employees'];
    $name = ($employee['first_name'] ?? 'Unknown') . ' ' . ($employee['last_name'] ?? '');
    
    // Calculate correct deductions
    $deductions = $calculator->calculateAll($salary);
    
    // Update via Supabase API
    $updateData = [
        'tax_value' => $deductions['tax'],
        'sss_employee_share' => $deductions['sss'],
        'philhealth_employee_share' => $deductions['philhealth'],
        'pagibig_employee_share' => $deductions['pagibig'],
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "{$supabaseUrl}/rest/v1/employee_compensation?id=eq.{$comp['id']}");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$supabaseKey}",
        "Authorization: Bearer {$supabaseKey}",
        "Content-Type: application/json",
        "Prefer: return=minimal"
    ]);
    
    $updateResponse = curl_exec($ch);
    $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($updateHttpCode === 204 || $updateHttpCode === 200) {
        echo "✓ {$name}\n";
        echo "  Salary: ₱" . number_format($salary, 2) . "\n";
        echo "  OLD → NEW\n";
        echo "  SSS:        ₱" . number_format($comp['sss_employee_share'], 2) . " → ₱" . number_format($deductions['sss'], 2) . "\n";
        echo "  PhilHealth: ₱" . number_format($comp['philhealth_employee_share'], 2) . " → ₱" . number_format($deductions['philhealth'], 2) . "\n";
        echo "  Pag-IBIG:   ₱" . number_format($comp['pagibig_employee_share'], 2) . " → ₱" . number_format($deductions['pagibig'], 2) . "\n";
        echo "  Tax:        ₱" . number_format($comp['tax_value'], 2) . " → ₱" . number_format($deductions['tax'], 2) . "\n";
        echo "  Net Pay:    ₱" . number_format($deductions['net_pay'], 2) . "\n\n";
        $updated++;
    } else {
        echo "✗ Failed to update {$name} (HTTP {$updateHttpCode})\n\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo "UPDATE COMPLETE\n";
echo str_repeat('=', 60) . "\n";
echo "Updated {$updated} out of " . count($compensations) . " employees\n";
echo "All employee deductions updated with correct Philippine rates.\n";
