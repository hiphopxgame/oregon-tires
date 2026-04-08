<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

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

// Check schema first
echo "=== oretir_admins columns ===\n";
$cols = $pdo->query("DESCRIBE oretir_admins")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== Current admin row ===\n";
$stmt = $pdo->prepare("SELECT * FROM oretir_admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if ($admin) {
    foreach ($admin as $k => $v) {
        if ($k !== 'password_hash' && $k !== 'setup_token') echo "  $k: $v\n";
    }
} else {
    echo "  NOT FOUND\n";
}

echo "\n=== Members row ===\n";
$stmt2 = $pdo->prepare("SELECT id, email, display_name, role FROM members WHERE email = ?");
$stmt2->execute([$email]);
$member = $stmt2->fetch(PDO::FETCH_ASSOC);
if ($member) {
    foreach ($member as $k => $v) echo "  $k: $v\n";
} else {
    echo "  NOT FOUND\n";
}

// Update
echo "\n=== Updating to superadmin ===\n";
if ($admin) {
    $roleCol = array_column($cols, 'Field');
    if (in_array('role', $roleCol)) {
        $pdo->prepare("UPDATE oretir_admins SET role = 'superadmin' WHERE email = ?")->execute([$email]);
        echo "oretir_admins.role → superadmin\n";
    }
}
if ($member) {
    $pdo->prepare("UPDATE members SET role = 'admin' WHERE email = ?")->execute([$email]);
    echo "members.role → admin\n";
}

// Verify
echo "\n=== Verified ===\n";
$v = $pdo->prepare("SELECT * FROM oretir_admins WHERE email = ?");
$v->execute([$email]);
$r = $v->fetch(PDO::FETCH_ASSOC);
if ($r) echo "  role: {$r['role']}\n";

// All admins
echo "\n=== All Admins ===\n";
$all = $pdo->query("SELECT * FROM oretir_admins ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $a) {
    echo "  #{$a['id']} {$a['email']} — role: {$a['role']}\n";
}

// Cleanup
echo "\nDone. Delete this script.\n";
