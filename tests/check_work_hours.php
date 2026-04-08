<?php
/**
 * Check work hours calculation
 */

require_once __DIR__ . '/../src/autoload.php';

$config = require __DIR__ . '/../config/supabase.php';
$db = new \Core\SupabaseConnection($config);

echo "=== Checking Work Hours ===\n\n";

$today = date('Y-m-d');

// Get today's attendance records
$records = $db->select('attendance', ['date' => $today]);

if (empty($records)) {
    echo "No attendance records found for today\n";
    exit(0);
}

echo "Found " . count($records) . " attendance record(s) for today:\n\n";

foreach ($records as $record) {
    echo "Record ID: {$record['id']}\n";
    echo "Employee ID: {$record['employee_id']}\n";
    echo "Date: {$record['date']}\n";
    echo "Time In: {$record['time_in']}\n";
    echo "Time Out: " . ($record['time_out'] ?? 'NULL') . "\n";
    echo "Work Hours (DB): " . ($record['work_hours'] ?? 'NULL') . "\n";
    
    // Calculate work hours manually
    if (!empty($record['time_in']) && !empty($record['time_out'])) {
        $timeIn = strtotime($record['time_in']);
        $timeOut = strtotime($record['time_out']);
        $diffSeconds = $timeOut - $timeIn;
        $workHours = round($diffSeconds / 3600, 2);
        
        echo "Calculated Work Hours: $workHours hours ($diffSeconds seconds)\n";
    }
    
    echo "Status: {$record['status']}\n";
    echo "---\n\n";
}

echo "=== Check Complete ===\n";
