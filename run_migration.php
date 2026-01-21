<?php
require __DIR__ . '/app/bootstrap.php';

$db = app()->getDatabase();
$sql = file_get_contents(__DIR__ . '/database/migrations/create_marketing_tables.sql');

// Remove comments
$sql = preg_replace('/--.*$/m', '', $sql);

// Split by semicolon followed by newline to avoid splitting inside VALUES
$statements = preg_split('/;\s*\n/', $sql);

$success = 0;
$errors = 0;

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt)) continue;

    try {
        $db->query($stmt);
        $success++;
        echo "OK\n";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        // Ignore duplicate/exists errors
        if (strpos($msg, 'Duplicate') !== false || strpos($msg, 'already exists') !== false) {
            echo "SKIP: Already exists\n";
            $success++;
        } else {
            echo 'Error: ' . $msg . "\n";
            $errors++;
        }
    }
}

echo "\nMigration complete: $success statements executed, $errors errors\n";
