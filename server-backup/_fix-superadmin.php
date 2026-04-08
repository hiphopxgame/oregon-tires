<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (function_exists('opcache_reset')) opcache_reset();

require_once __DIR__ . '/includes/bootstrap.php';

$db = getDB();
$email = 'oregontirespdx@gmail.com';

// Check current state
$stmt = $db->prepare("SELECT id, email, name, role FROM oretir_admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch();

echo "=== Current State ===\n";
if ($admin) {
    echo "Admin found: #{$admin['id']} — {$admin['name']} — role: {$admin['role']}\n";
} else {
    echo "NOT found in oretir_admins\n";
}

// Check members table too
$stmt2 = $db->prepare("SELECT id, email, display_name, role FROM members WHERE email = ?");
$stmt2->execute([$email]);
$member = $stmt2->fetch();
if ($member) {
    echo "Member found: #{$member['id']} — {$member['display_name']} — role: {$member['role']}\n";
} else {
    echo "NOT found in members\n";
}

// Update to superadmin
if ($admin) {
    $db->prepare("UPDATE oretir_admins SET role = 'superadmin' WHERE email = ?")->execute([$email]);
    echo "\n=== Updated ===\n";
    echo "oretir_admins.role → superadmin\n";
}
if ($member) {
    $db->prepare("UPDATE members SET role = 'admin' WHERE email = ?")->execute([$email]);
    echo "members.role → admin\n";
}

// Verify
$stmt3 = $db->prepare("SELECT id, email, name, role FROM oretir_admins WHERE email = ?");
$stmt3->execute([$email]);
$verified = $stmt3->fetch();
echo "\n=== Verified ===\n";
echo "oretir_admins: {$verified['name']} — role: {$verified['role']}\n";

$stmt4 = $db->prepare("SELECT id, email, role FROM members WHERE email = ?");
$stmt4->execute([$email]);
$verified2 = $stmt4->fetch();
if ($verified2) {
    echo "members: role: {$verified2['role']}\n";
}

// Also run migration 073 logic — ensure owner is superadmin
echo "\n=== All admins ===\n";
$all = $db->query("SELECT id, email, name, role FROM oretir_admins ORDER BY id")->fetchAll();
foreach ($all as $a) {
    echo "  #{$a['id']} {$a['email']} — {$a['name']} — {$a['role']}\n";
}
