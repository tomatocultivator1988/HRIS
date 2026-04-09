<?php
/**
 * Standalone Philippine Deduction Calculator Test
 * No dependencies required - just run it!
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
            'gross' => $monthlySalary,
            'sss' => $sss,
            'philhealth' => $philhealth,
            'pagibig' => $pagibig,
            'tax' => $tax,
            'total_deductions' => $sss + $philhealth + $pagibig + $tax,
            'net_pay' => $monthlySalary - ($sss + $philhealth + $pagibig + $tax)
        ];
    }
}

// Run tests
$calculator = new PhilippineDeductionCalculator();

echo "=== PHILIPPINE DEDUCTION CALCULATOR ===\n\n";
echo "Based on 2024 rates (update when 2026 rates are released)\n\n";

$testSalaries = [15000, 20000, 25000, 30000, 40000, 50000, 75000, 100000];

foreach ($testSalaries as $salary) {
    $result = $calculator->calculateAll($salary);
    
    echo str_repeat('=', 60) . "\n";
    echo "Gross Salary:       ₱" . number_format($result['gross'], 2) . "\n";
    echo "SSS:                ₱" . number_format($result['sss'], 2) . "\n";
    echo "PhilHealth:         ₱" . number_format($result['philhealth'], 2) . "\n";
    echo "Pag-IBIG:           ₱" . number_format($result['pagibig'], 2) . "\n";
    echo "Withholding Tax:    ₱" . number_format($result['tax'], 2) . "\n";
    echo "Total Deductions:   ₱" . number_format($result['total_deductions'], 2) . "\n";
    echo "Net Pay:            ₱" . number_format($result['net_pay'], 2) . "\n";
    echo str_repeat('=', 60) . "\n\n";
}

echo "Note: These calculations use 2024 government rates.\n";
