<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();

try {
    // Get all unique positions from employees
    $employees = $db->select('employees', ['is_active' => true]);
    
    $positions = [];
    foreach ($employees as $emp) {
        $position = trim($emp['position'] ?? '');
        $department = trim($emp['department'] ?? '');
        
        if ($position !== '' && !isset($positions[$position])) {
            $positions[$position] = [
                'position' => $position,
                'department' => $department,
                'payroll_type' => 'Monthly',
                'base_salary' => 30000.00,
                'daily_rate' => 0.00,
                'hourly_rate' => 0.00,
                'sss_employee_share' => 0.00,
                'philhealth_employee_share' => 0.00,
                'pagibig_employee_share' => 0.00,
                'tax_value' => 0.00,
                'standard_work_hours_per_day' => 8.00,
                'is_active' => true
            ];
        }
    }
    
    echo "Found " . count($positions) . " unique positions from employees:\n\n";
    
    if (count($positions) === 0) {
        echo "⚠️  No positions found in employees table.\n";
        echo "Make sure your employees have positions set.\n";
        exit(0);
    }
    
    $created = 0;
    $skipped = 0;
    
    foreach ($positions as $posData) {
        try {
            // Check if position already exists
            $existing = $db->select('position_salaries', ['position' => $posData['position']]);
            
            if (count($existing) > 0) {
                echo "⏭️  Skipped: {$posData['position']} (already exists)\n";
                $skipped++;
            } else {
                $db->insert('position_salaries', $posData);
                echo "✅ Created: {$posData['position']} - ₱" . number_format($posData['base_salary'], 2) . "\n";
                $created++;
            }
        } catch (\Exception $e) {
            echo "❌ Error creating {$posData['position']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    echo "Summary:\n";
    echo "  Created: $created positions\n";
    echo "  Skipped: $skipped positions (already existed)\n";
    echo "\n✅ Done! You can now go to 'Manage Salaries' to edit these positions.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
