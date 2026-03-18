<?php
/**
 * Sync oretir_admins → members table
 * Ensures every active admin has a members record with role='admin'.
 * Safe to run multiple times (idempotent).
 *
 * Usage: php cli/sync-admins-to-members.php [--dry-run]
 */
declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv ?? [], true);

require_once __DIR__ . '/../vendor/autoload.php';
$envDir = dirname(__DIR__, 3);
$envFile = '.env.oregon-tires';
if (!file_exists($envDir . '/' . $envFile)) {
    $envDir = __DIR__ . '/..';
    $envFile = '.env';
}
$dotenv = Dotenv\Dotenv::createImmutable($envDir, $envFile);
$dotenv->load();
require_once __DIR__ . '/../includes/db.php';
$db = getDB();

// Get all active admins
$admins = $db->query('SELECT id, email, display_name, password_hash FROM oretir_admins WHERE is_active = 1 ORDER BY id')
    ->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($admins) . " active admin(s) in oretir_admins\n\n";

$created = 0;
$updated = 0;
$skipped = 0;

foreach ($admins as $admin) {
    $email = $admin['email'];
    echo "  [{$admin['id']}] {$email} ({$admin['display_name']})\n";

    // Check if member record exists
    $stmt = $db->prepare('SELECT id, role FROM members WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        if ($member['role'] === 'admin') {
            echo "    → Already exists as admin (member id={$member['id']})\n";
            $skipped++;
        } else {
            echo "    → Exists but role='{$member['role']}' — upgrading to admin\n";
            if (!$dryRun) {
                $db->prepare('UPDATE members SET role = ? WHERE id = ?')
                    ->execute(['admin', $member['id']]);
            }
            $updated++;
        }
    } else {
        echo "    → No member record — creating with role=admin\n";
        if (!$dryRun) {
            $db->prepare('INSERT INTO members (email, display_name, password_hash, role, status, email_verified_at, created_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())')
                ->execute([
                    $email,
                    $admin['display_name'] ?: $email,
                    $admin['password_hash'] ?: '',
                    'admin',
                    'active',
                ]);
        }
        $created++;
    }
}

echo "\n" . ($dryRun ? '[DRY RUN] ' : '') . "Done: {$created} created, {$updated} upgraded, {$skipped} already OK\n";
