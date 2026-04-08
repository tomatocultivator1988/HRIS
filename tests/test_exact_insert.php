<?php
/**
 * Test exact insert with the data from the log
 */

require_once __DIR__ . '/../src/autoload.php';

$config = require __DIR__ . '/../config/supabase.php';
$db = new \Core\SupabaseConnection($config);

echo "=== Testing Exact Insert ===\n\n";

$data = [
    "employee_id" => "4c789067-4356-43e7-bd96-c7f7a3bcc87a",
    "date" => "2026-04-07",
    "time_in" => "2026-04-07 06:29:19",
    "status" => "Present",
    "work_hours" => null,
    "remarks" => null,
    "created_at" => "2026-04-07 04:29:22",
    "updated_at" => "2026-04-07 04:29:22"
];

echo "Data to insert:\n";
print_r($data);
echo "\n";

// First delete any existing record
$existing = $db->select('attendance', [
    'employee_id' => $data['employee_id'],
    'date' => $data['date']
]);

if (!empty($existing)) {
    echo "Deleting existing record...\n";
    foreach ($existing as $record) {
        $db->delete('attendance', ['id' => $record['id']]);
    }
}

echo "Inserting...\n";
$result = $db->insert('attendance', $data);

echo "Result:\n";
print_r($result);

if (empty($result)) {
    echo "\n❌ Insert returned empty!\n";
} else {
    echo "\n✅ Insert successful!\n";
}

echo "\n=== Test Complete ===\n";
