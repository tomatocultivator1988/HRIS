<?php

require_once 'config/supabase.php';
require_once 'src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();
$leaves = $db->select(TABLE_LEAVE_REQUESTS, []);

echo 'Total leave requests in database: ' . count($leaves) . PHP_EOL;

if (count($leaves) > 0) {
    echo PHP_EOL . 'Sample leave requests:' . PHP_EOL;
    foreach (array_slice($leaves, 0, 10) as $leave) {
        echo '- ID: ' . $leave['id'] . ', Start: ' . $leave['start_date'] . ', End: ' . $leave['end_date'] . ', Status: ' . $leave['status'] . PHP_EOL;
    }
    
    // Find date range
    $dates = array_map(function($l) { return $l['start_date']; }, $leaves);
    sort($dates);
    echo PHP_EOL . 'Earliest leave: ' . $dates[0] . PHP_EOL;
    echo 'Latest leave: ' . end($dates) . PHP_EOL;
    
    // Test the date filtering logic
    echo PHP_EOL . '=== Testing Date Filter Logic ===' . PHP_EOL;
    $startDate = '2026-03-08';
    $endDate = '2026-04-07';
    echo "Report range: $startDate to $endDate" . PHP_EOL . PHP_EOL;
    
    $filtered = array_filter($leaves, function($request) use ($startDate, $endDate) {
        $reqStartDate = $request['start_date'] ?? '';
        $reqEndDate = $request['end_date'] ?? '';
        
        // Extract just the date part if it's a timestamp
        if (strlen($reqStartDate) > 10) {
            $reqStartDate = substr($reqStartDate, 0, 10);
        }
        if (strlen($reqEndDate) > 10) {
            $reqEndDate = substr($reqEndDate, 0, 10);
        }
        
        // Check if leave request overlaps with report date range
        $overlaps = $reqStartDate <= $endDate && $reqEndDate >= $startDate;
        
        if ($overlaps) {
            echo "MATCH: Leave $reqStartDate to $reqEndDate overlaps with report range" . PHP_EOL;
        }
        
        return $overlaps;
    });
    
    echo PHP_EOL . 'Filtered results: ' . count($filtered) . ' records' . PHP_EOL;
} else {
    echo PHP_EOL . 'No leave requests found in database!' . PHP_EOL;
}
