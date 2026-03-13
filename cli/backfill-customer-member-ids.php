<?php
/**
 * One-time backfill: link oretir_customers to members table via email match.
 * Also populates visit_count from completed appointments.
 *
 * Usage: php cli/backfill-customer-member-ids.php [--dry-run]
 */

declare(strict_types=1);

require_once __DIR__ . '/../public_html/includes/bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);
$pdo = getDB();

echo "=== Oregon Tires: Backfill Customer Member IDs ===\n";
echo $dryRun ? "[DRY RUN]\n\n" : "\n";

// 1. Link customers to members via email match
$stmt = $pdo->query(
    'SELECT c.id, c.email FROM oretir_customers c
     WHERE c.member_id IS NULL AND c.email IS NOT NULL AND c.email != ""'
);
$unlinked = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($unlinked) . " customers without member_id\n";

$linked = 0;
foreach ($unlinked as $cust) {
    $memberStmt = $pdo->prepare('SELECT id FROM members WHERE email = ? LIMIT 1');
    $memberStmt->execute([$cust['email']]);
    $memberId = $memberStmt->fetchColumn();

    if ($memberId) {
        if (!$dryRun) {
            $pdo->prepare('UPDATE oretir_customers SET member_id = ? WHERE id = ?')
                ->execute([(int) $memberId, $cust['id']]);
        }
        $linked++;
        echo "  Linked customer #{$cust['id']} ({$cust['email']}) → member #{$memberId}\n";
    }
}
echo "Linked: {$linked}\n\n";

// 2. Populate visit_count from completed appointments
$stmt = $pdo->query(
    'SELECT c.id, COUNT(a.id) AS visits, MAX(a.preferred_date) AS last_visit
     FROM oretir_customers c
     LEFT JOIN oretir_appointments a ON a.customer_id = c.id AND a.status = "completed"
     GROUP BY c.id'
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
foreach ($rows as $row) {
    $visits = (int) $row['visits'];
    if ($visits > 0) {
        if (!$dryRun) {
            $pdo->prepare('UPDATE oretir_customers SET visit_count = ?, last_visit_at = ? WHERE id = ?')
                ->execute([$visits, $row['last_visit'], $row['id']]);
        }
        $updated++;
    }
}
echo "Updated visit_count for {$updated} customers\n";
echo "\nDone.\n";
