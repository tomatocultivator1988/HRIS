<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();

try {
    $employees = $db->select('employees', ['is_active' => true]);
    
    if (count($employees) > 0) {
        echo "Sample employee record:\n";
        echo str_repeat("-", 80) . "\n";
        print_r($employees[0]);
        
        echo "\n\nUnique positions found:\n";
        echo str_repeat("-", 80) . "\n";
        
        $positions = [];
        foreach ($employees as $emp) {
            $position = trim($emp['position'] ?? '');
            $department = trim($emp['department'] ?? '');
            
            if ($position !== '') {
                if (!isset($positions[$position])) {
                    $positions[$position] = [];
                }
                if ($department !== '' && !in_array($department, $positions[$position])) {
                    $positions[$position][] = $department;
                }
            }
        }
        
        foreach ($positions as $pos => $depts) {
            echo "Position: $pos\n";
            if (count($depts) > 0) {
                echo "  Departments: " . implode(', ', $depts) . "\n";
            }
            echo "\n";
        }
        
        echo "Total unique positions: " . count($positions) . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
