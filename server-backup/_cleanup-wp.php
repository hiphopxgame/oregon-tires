<?php
$secret = 'OT_CLEANUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$dirs = ['wp-admin', 'wp-content', 'wp-includes'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        exec('rm -rf ' . escapeshellarg($path) . ' 2>&1', $out, $rc);
        echo "$dir: " . ($rc === 0 ? 'REMOVED' : "FAILED (rc=$rc)") . "\n";
    } else {
        echo "$dir: not found\n";
    }
}

// Also remove leftover WP files
$wpFiles = ['error_log'];
foreach ($wpFiles as $f) {
    $p = __DIR__ . '/' . $f;
    if (file_exists($p)) { @unlink($p); echo "$f: removed\n"; }
}

// Self-destruct
echo "\nDone. Delete this script now.\n";
