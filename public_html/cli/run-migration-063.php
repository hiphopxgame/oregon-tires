<?php
/**
 * Run migration 063 — Estimate Templates
 * Usage: php cli/run-migration-063.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = getDB();

$sqlFile = dirname(__DIR__, 2) . '/sql/migrate-063-estimate-templates.sql';
if (!file_exists($sqlFile)) {
    // Fallback for server path (no public_html nesting)
    $sqlFile = __DIR__ . '/../../sql/migrate-063-estimate-templates.sql';
}

if (!file_exists($sqlFile)) {
    echo "ERROR: Migration file not found.\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);

// Split on semicolons (respecting quoted strings)
$statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));

echo "Running migration 063 — Estimate Templates...\n";

foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    try {
        $db->exec($stmt);
        $preview = substr(preg_replace('/\s+/', ' ', $stmt), 0, 60);
        echo "  OK: {$preview}...\n";
    } catch (\Throwable $e) {
        echo "  ERR: " . $e->getMessage() . "\n";
        echo "  SQL: " . substr($stmt, 0, 100) . "...\n";
    }
}

// Verify
$count = $db->query('SELECT COUNT(*) FROM oretir_estimate_templates')->fetchColumn();
echo "\nDone. Templates in DB: {$count}\n";
