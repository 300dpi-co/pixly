<?php
/**
 * One-time cleanup script for AI processing queue
 * Run this once then delete the file
 *
 * Usage: php cleanup-queue.php
 * Or visit: https://yoursite.com/cleanup-queue.php
 */

require_once __DIR__ . '/app/Core/Bootstrap.php';

$db = app()->getDatabase();

// Clear the entire queue
$db->execute("DELETE FROM ai_processing_queue");

echo "AI processing queue cleared.\n";
echo "You can now delete this file.\n";
