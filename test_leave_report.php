<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$startDate = '2026-03-08';
$endDate = '2026-06-07';

require_once 'config/supabase.php';
require_once 'src/Core/SupabaseConnection.php';
require_once 'src/Services/ReportService.php';

try {
    $service = new \Services\ReportService();
    echo "Generating leave report from $startDate to $endDate..." . PHP_EOL;
    
    $report = $service->generateLeaveReport($startDate, $endDate, []);
    
    echo 'SUCCESS: Report generated' . PHP_EOL;
    echo 'Records: ' . count($report['records']) . PHP_EOL;
    echo 'Summary: ' . json_encode($report['summary'], JSON_PRETTY_PRINT) . PHP_EOL;
    
    if (count($report['records']) > 0) {
        echo PHP_EOL . 'First record:' . PHP_EOL;
        print_r($report['records'][0]);
    }
    
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo 'Trace: ' . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}
