<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();

try {
    $positions = $db->select('position_salaries', []);
    
    echo "✅ Position Salaries Table Exists!\n\n";
    echo "Total positions found: " . count($positions) . "\n\n";
    
    if (count($positions) > 0) {
        echo "Positions in database:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-30s %-20s %-15s\n", "Position", "Payroll Type", "Base Salary");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($positions as $pos) {
            printf(
                "%-30s %-20s ₱%s\n",
                $pos['position'] ?? 'N/A',
                $pos['payroll_type'] ?? 'N/A',
                number_format($pos['base_salary'] ?? 0, 2)
            );
        }
        
        echo "\n✅ Migration completed successfully!\n";
        echo "You can now go to 'Manage Salaries' page to edit these positions.\n";
    } else {
        echo "⚠️  No positions found. The table exists but is empty.\n";
        echo "This might happen if you have no employees with positions set.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nThe position_salaries table might not exist yet.\n";
    echo "Please run the migration SQL in Supabase dashboard.\n";
}
