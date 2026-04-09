<?php
/**
 * Test Philippine Deduction Calculator
 * 
 * Run this to see correct deductions for different salary levels
 */

require_once __DIR__ . '/vendor/autoload.php';

use Services\PhilippineDeductionCalculator;

$calculator = new PhilippineDeductionCalculator();

echo "=== PHILIPPINE DEDUCTION CALCULATOR TEST ===\n\n";
echo "Based on 2024 rates (update when 2026 rates are released)\n\n";

$testSalaries = [
    15000,
    20000,
    25000,
    30000,
    40000,
    50000,
    75000,
    100000
];

foreach ($testSalaries as $salary) {
    echo str_repeat('=', 60) . "\n";
    echo $calculator->getDeductionBreakdown($salary);
    echo "\n" . str_repeat('=', 60) . "\n\n";
}

echo "Note: These calculations use 2024 government rates.\n";
echo "Update the PhilippineDeductionCalculator class when 2026 rates are released.\n";
