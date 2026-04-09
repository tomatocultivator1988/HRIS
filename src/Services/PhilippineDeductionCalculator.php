<?php

namespace Services;

/**
 * Philippine Deduction Calculator
 * 
 * Calculates SSS, PhilHealth, Pag-IBIG, and Withholding Tax
 * based on Philippine government rates.
 * 
 * Current rates: 2024 (update when 2026 rates are released)
 */
class PhilippineDeductionCalculator
{
    /**
     * Calculate SSS employee contribution
     * Based on Monthly Salary Credit (MSC) table
     * 
     * @param float $monthlySalary
     * @return float Employee share (4.5% of MSC)
     */
    public function calculateSSS(float $monthlySalary): float
    {
        // SSS Contribution Table 2024
        // Format: [max_salary, msc, employee_share]
        $sssTable = [
            [4249.99, 4000, 180.00],
            [4749.99, 4500, 202.50],
            [5249.99, 5000, 225.00],
            [5749.99, 5500, 247.50],
            [6249.99, 6000, 270.00],
            [6749.99, 6500, 292.50],
            [7249.99, 7000, 315.00],
            [7749.99, 7500, 337.50],
            [8249.99, 8000, 360.00],
            [8749.99, 8500, 382.50],
            [9249.99, 9000, 405.00],
            [9749.99, 9500, 427.50],
            [10249.99, 10000, 450.00],
            [10749.99, 10500, 472.50],
            [11249.99, 11000, 495.00],
            [11749.99, 11500, 517.50],
            [12249.99, 12000, 540.00],
            [12749.99, 12500, 562.50],
            [13249.99, 13000, 585.00],
            [13749.99, 13500, 607.50],
            [14249.99, 14000, 630.00],
            [14749.99, 14500, 652.50],
            [15249.99, 15000, 675.00],
            [15749.99, 15500, 697.50],
            [16249.99, 16000, 720.00],
            [16749.99, 16500, 742.50],
            [17249.99, 17000, 765.00],
            [17749.99, 17500, 787.50],
            [18249.99, 18000, 810.00],
            [18749.99, 18500, 832.50],
            [19249.99, 19000, 855.00],
            [19749.99, 19500, 877.50],
            [20249.99, 20000, 900.00],
            [20749.99, 20500, 922.50],
            [21249.99, 21000, 945.00],
            [21749.99, 21500, 967.50],
            [22249.99, 22000, 990.00],
            [22749.99, 22500, 1012.50],
            [23249.99, 23000, 1035.00],
            [23749.99, 23500, 1057.50],
            [24249.99, 24000, 1080.00],
            [24749.99, 24500, 1102.50],
            [25249.99, 25000, 1125.00],
            [25749.99, 25500, 1147.50],
            [26249.99, 26000, 1170.00],
            [26749.99, 26500, 1192.50],
            [27249.99, 27000, 1215.00],
            [27749.99, 27500, 1237.50],
            [28249.99, 28000, 1260.00],
            [28749.99, 28500, 1282.50],
            [29249.99, 29000, 1305.00],
            [29749.99, 29500, 1327.50],
            [PHP_FLOAT_MAX, 30000, 1350.00] // Maximum
        ];

        foreach ($sssTable as [$maxSalary, $msc, $employeeShare]) {
            if ($monthlySalary <= $maxSalary) {
                return $employeeShare;
            }
        }

        return 1350.00; // Maximum contribution
    }

    /**
     * Calculate PhilHealth employee contribution
     * Employee pays 50% of total premium (5% of salary)
     * 
     * @param float $monthlySalary
     * @return float Employee share (2.5% of salary, max ₱2,500)
     */
    public function calculatePhilHealth(float $monthlySalary): float
    {
        if ($monthlySalary <= 10000) {
            return 250.00; // Minimum
        }

        // 5% total premium, employee pays 50% = 2.5%
        $employeeShare = $monthlySalary * 0.025;

        // Maximum employee share is ₱2,500
        return min($employeeShare, 2500.00);
    }

    /**
     * Calculate Pag-IBIG employee contribution
     * 1% for salary ≤ ₱1,500
     * 2% for salary > ₱1,500 (max ₱100)
     * 
     * @param float $monthlySalary
     * @return float Employee share (max ₱100)
     */
    public function calculatePagIBIG(float $monthlySalary): float
    {
        if ($monthlySalary <= 1500) {
            return max($monthlySalary * 0.01, 15.00); // Minimum ₱15
        }

        // 2% of salary, maximum ₱100
        return min($monthlySalary * 0.02, 100.00);
    }

    /**
     * Calculate Withholding Tax based on TRAIN Law
     * Uses graduated tax rates on taxable income
     * 
     * @param float $monthlySalary
     * @param float $sss SSS contribution (tax-exempt)
     * @param float $philhealth PhilHealth contribution (tax-exempt)
     * @param float $pagibig Pag-IBIG contribution (tax-exempt)
     * @return float Monthly withholding tax
     */
    public function calculateWithholdingTax(
        float $monthlySalary,
        float $sss,
        float $philhealth,
        float $pagibig
    ): float {
        // Taxable income = Gross - mandatory contributions
        $taxableIncome = $monthlySalary - $sss - $philhealth - $pagibig;

        // TRAIN Law Tax Table (Monthly) - 2024
        if ($taxableIncome <= 20833) {
            return 0; // 0%
        } elseif ($taxableIncome <= 33332) {
            return ($taxableIncome - 20833) * 0.15; // 15%
        } elseif ($taxableIncome <= 66666) {
            return 1875 + (($taxableIncome - 33333) * 0.20); // ₱1,875 + 20%
        } elseif ($taxableIncome <= 166666) {
            return 8541.80 + (($taxableIncome - 66667) * 0.25); // ₱8,541.80 + 25%
        } elseif ($taxableIncome <= 666666) {
            return 33541.80 + (($taxableIncome - 166667) * 0.30); // ₱33,541.80 + 30%
        } else {
            return 183541.80 + (($taxableIncome - 666667) * 0.35); // ₱183,541.80 + 35%
        }
    }

    /**
     * Calculate all deductions at once
     * 
     * @param float $monthlySalary Gross monthly salary
     * @return array ['sss' => float, 'philhealth' => float, 'pagibig' => float, 'tax' => float, 'total' => float, 'net_pay' => float]
     */
    public function calculateAllDeductions(float $monthlySalary): array
    {
        $sss = $this->calculateSSS($monthlySalary);
        $philhealth = $this->calculatePhilHealth($monthlySalary);
        $pagibig = $this->calculatePagIBIG($monthlySalary);
        $tax = $this->calculateWithholdingTax($monthlySalary, $sss, $philhealth, $pagibig);

        $totalDeductions = $sss + $philhealth + $pagibig + $tax;
        $netPay = $monthlySalary - $totalDeductions;

        return [
            'gross_salary' => round($monthlySalary, 2),
            'sss' => round($sss, 2),
            'philhealth' => round($philhealth, 2),
            'pagibig' => round($pagibig, 2),
            'tax' => round($tax, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($netPay, 2)
        ];
    }

    /**
     * Get deduction breakdown as formatted string
     * 
     * @param float $monthlySalary
     * @return string Formatted breakdown
     */
    public function getDeductionBreakdown(float $monthlySalary): string
    {
        $deductions = $this->calculateAllDeductions($monthlySalary);

        return sprintf(
            "Gross Salary: ₱%s\n" .
            "SSS: ₱%s\n" .
            "PhilHealth: ₱%s\n" .
            "Pag-IBIG: ₱%s\n" .
            "Withholding Tax: ₱%s\n" .
            "Total Deductions: ₱%s\n" .
            "Net Pay: ₱%s",
            number_format($deductions['gross_salary'], 2),
            number_format($deductions['sss'], 2),
            number_format($deductions['philhealth'], 2),
            number_format($deductions['pagibig'], 2),
            number_format($deductions['tax'], 2),
            number_format($deductions['total_deductions'], 2),
            number_format($deductions['net_pay'], 2)
        );
    }
}
