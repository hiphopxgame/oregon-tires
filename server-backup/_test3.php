<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "open_basedir: " . ini_get('open_basedir') . "\n";
echo "doc_root: " . ini_get('doc_root') . "\n";

// Test file access at various levels
$paths = [
    '/home2/avadpnmy/.env.oregon-tires',
    '/home2/avadpnmy/public_html/.env',
    '/home2/avadpnmy/public_html/includes/bootstrap.php',
];
foreach ($paths as $p) {
    echo "$p: " . (file_exists($p) ? 'accessible' : 'NOT accessible') . "\n";
}
