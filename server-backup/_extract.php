<?php
// Temporary extraction helper — DELETE after use
$secret = 'OT_EXTRACT_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$action = $_GET['action'] ?? '';
$base = dirname(__DIR__); // /home2/avadpnmy

switch ($action) {
    case 'site':
        // Extract site.tar.gz into public_html/
        $file = __DIR__ . '/site.tar.gz';
        if (!file_exists($file)) die("site.tar.gz not found");
        exec("cd " . escapeshellarg(__DIR__) . " && tar xzf site.tar.gz 2>&1", $out, $rc);
        echo "site extract: rc=$rc\n" . implode("\n", $out);
        break;

    case 'vendor':
        // Extract vendor.tar.gz into public_html/
        $file = __DIR__ . '/vendor.tar.gz';
        if (!file_exists($file)) die("vendor.tar.gz not found");
        exec("cd " . escapeshellarg(__DIR__) . " && tar xzf vendor.tar.gz 2>&1", $out, $rc);
        echo "vendor extract: rc=$rc\n" . implode("\n", $out);
        break;

    case 'kits':
        // Extract shared-kits.tar.gz into ~/shared/
        $file = __DIR__ . '/shared-kits.tar.gz';
        $dest = $base . '/shared';
        if (!file_exists($file)) die("shared-kits.tar.gz not found");
        exec("cd " . escapeshellarg($dest) . " && tar xzf " . escapeshellarg($file) . " 2>&1", $out, $rc);
        echo "kits extract: rc=$rc\n" . implode("\n", $out);
        break;

    case 'uploads':
        // Extract uploads.tar.gz into public_html/uploads/
        $file = __DIR__ . '/uploads.tar.gz';
        @mkdir(__DIR__ . '/uploads', 0755, true);
        exec("cd " . escapeshellarg(__DIR__ . '/uploads') . " && tar xzf " . escapeshellarg($file) . " 2>&1", $out, $rc);
        echo "uploads extract: rc=$rc\n" . implode("\n", $out);
        break;

    case 'cleanup':
        // Remove tar files and this script
        @unlink(__DIR__ . '/site.tar.gz');
        @unlink(__DIR__ . '/vendor.tar.gz');
        @unlink(__DIR__ . '/shared-kits.tar.gz');
        @unlink(__DIR__ . '/uploads.tar.gz');
        echo "Archives cleaned. Now delete _extract.php manually.";
        break;

    case 'info':
        echo "PHP " . PHP_VERSION . "\n";
        echo "Home: " . $base . "\n";
        echo "Doc root: " . __DIR__ . "\n";
        echo "exec available: " . (function_exists('exec') ? 'yes' : 'NO') . "\n";
        echo "tar: "; exec("which tar 2>&1", $t); echo implode('', $t) . "\n";
        break;

    default:
        echo "Actions: info, site, vendor, kits, uploads, cleanup";
}
