<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();

echo "=== Testing Position-Based Salary Logic ===\n\n";

// Test 1: Check employees with positions
$employees = $db->select('employees', ['is_active' => true]);
echo "Test 1: Employees with positions\n";
echo str_repeat("-", 60) . "\n";

$withPosition = 0;
$withoutPosition = 0;

foreach ($employees as $emp) {
    $position = trim($emp['position'] ?? '');
    if ($position !== '') {
        $withPosition++;
    } else {
        $withoutPosition++;
        echo "⚠️  Employee {$emp['first_name']} {$emp['last_name']} has NO position\n";
    }
}

echo "\nSummary:\n";
echo "  With position: $withPosition\n";
echo "  Without position: $withoutPosition\n\n";

// Test 2: Check unique positions
echo "Test 2: Unique positions from employees\n";
echo str_repeat("-", 60) . "\n";

$positions = [];
foreach ($employees as $emp) {
    $position = trim($emp['position'] ?? '');
    if ($position !== '') {
        if (!isset($positions[$position])) {
            $positions[$position] = 0;
        }
        $positions[$position]++;
    }
}

foreach ($positions as $pos => $count) {
    echo "  $pos: $count employee(s)\n";
}

echo "\nTotal unique positions: " . count($positions) . "\n\n";

// Test 3: Check position_salaries table
echo "Test 3: Position salary records\n";
echo str_repeat("-", 60) . "\n";

try {
    $salaries = $db->select('position_salaries', ['is_active' => true]);
    echo "Position salary records found: " . count($salaries) . "\n\n";
    
    if (count($salaries) > 0) {
        foreach ($salaries as $sal) {
            $hasEmployees = isset($positions[$sal['position']]);
            $status = $hasEmployees ? "✅ Has employees" : "⚠️  No employees";
            echo "  {$sal['position']}: ₱" . number_format($sal['base_salary'], 2) . " - $status\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "The position_salaries table might not exist yet.\n";
}

echo "\n=== Test Complete ===\n";
