<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');
if (function_exists('opcache_reset')) opcache_reset();

// Direct DB connection (bypass bootstrap/opcache issues)
$envFile = '/home2/avadpnmy/.env.oregon-tires';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

$pdo = new PDO(
    "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
    $env['DB_USER'], $env['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$email = 'oregontirespdx@gmail.com';

// Check current state
echo "=== Current State ===\n";
$stmt = $pdo->prepare("SELECT id, email, name, role FROM oretir_admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    echo "oretir_admins: #{$admin['id']} — {$admin['name']} — role: {$admin['role']}\n";
} else {
    echo "NOT in oretir_admins\n";
}

$stmt2 = $pdo->prepare("SELECT id, email, display_name, role FROM members WHERE email = ?");
$stmt2->execute([$email]);
$member = $stmt2->fetch(PDO::FETCH_ASSOC);
if ($member) {
    echo "members: #{$member['id']} — {$member['display_name']} — role: {$member['role']}\n";
} else {
    echo "NOT in members\n";
}

// Update to superadmin
echo "\n=== Updating ===\n";
if ($admin) {
    $pdo->prepare("UPDATE oretir_admins SET role = 'superadmin' WHERE email = ?")->execute([$email]);
    echo "oretir_admins.role → superadmin\n";
}
if ($member) {
    $pdo->prepare("UPDATE members SET role = 'admin' WHERE email = ?")->execute([$email]);
    echo "members.role → admin\n";
}

// Verify
echo "\n=== Verified ===\n";
$v1 = $pdo->prepare("SELECT id, email, name, role FROM oretir_admins WHERE email = ?");
$v1->execute([$email]);
$r1 = $v1->fetch(PDO::FETCH_ASSOC);
echo "oretir_admins: {$r1['name']} — role: {$r1['role']}\n";

$v2 = $pdo->prepare("SELECT id, email, role FROM members WHERE email = ?");
$v2->execute([$email]);
$r2 = $v2->fetch(PDO::FETCH_ASSOC);
if ($r2) echo "members: role: {$r2['role']}\n";

// Show all admins
echo "\n=== All Admins ===\n";
$all = $pdo->query("SELECT id, email, name, role FROM oretir_admins ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $a) {
    echo "  #{$a['id']} {$a['email']} — {$a['name']} — {$a['role']}\n";
}
