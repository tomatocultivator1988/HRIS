<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/src/Core/SupabaseConnection.php';

$db = new \Core\SupabaseConnection();

// Read the migration file
$migrationFile = $argv[1] ?? 'docs/migrations/create_position_salaries_table.sql';

if (!file_exists($migrationFile)) {
    die("Migration file not found: $migrationFile\n");
}

$sql = file_get_contents($migrationFile);

try {
    echo "Running migration: $migrationFile\n";
    $db->query($sql);
    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
