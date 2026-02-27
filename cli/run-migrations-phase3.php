<?php
/**
 * Phase 3+4 migration runner — run once, then delete
 */
require __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();

$files = [
    '/tmp/migrate-013-sms-opt-in.sql',
    '/tmp/migrate-014-utm-fields.sql',
    '/tmp/migrate-016-blog.sql',
    '/tmp/migrate-017-promotions.sql',
    '/tmp/migrate-018-care-plans.sql',
    '/tmp/seed-blog-posts.sql',
];

foreach ($files as $f) {
    if (!file_exists($f)) {
        echo "SKIP $f (not found)\n";
        continue;
    }
    $sql = file_get_contents($f);
    try {
        $pdo->exec($sql);
        echo "OK   $f\n";
    } catch (Exception $e) {
        echo "ERR  $f: " . $e->getMessage() . "\n";
    }
}

echo "\nDone. Delete this file now.\n";
