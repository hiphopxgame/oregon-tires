<?php
/**
 * One-time script: Link existing customers, employees, vehicles, and appointments
 * to their matching member records by email.
 *
 * Usage: php cli/link-members.php [--dry-run]
 */

declare(strict_types=1);

// Server: flat structure (no public_html/), Local: has public_html/
$bootstrap = __DIR__ . '/../includes/bootstrap.php';
if (!file_exists($bootstrap)) {
    $bootstrap = __DIR__ . '/../public_html/includes/bootstrap.php';
}
require_once $bootstrap;

$pdo = getDB();
$dryRun = in_array('--dry-run', $argv, true);

if ($dryRun) {
    echo "=== DRY RUN (no changes will be made) ===\n\n";
}

// 1. Link customers to members by email
$stmt = $pdo->query(
    'SELECT c.id, c.email, m.id as member_id
     FROM oretir_customers c
     JOIN members m ON c.email = m.email
     WHERE c.member_id IS NULL'
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Customers to link: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  Customer #{$r['id']} ({$r['email']}) → Member #{$r['member_id']}\n";
}
if (!$dryRun && count($rows) > 0) {
    $pdo->exec(
        'UPDATE oretir_customers c
         JOIN members m ON c.email = m.email
         SET c.member_id = m.id
         WHERE c.member_id IS NULL'
    );
    echo "  Done.\n";
}

// 2. Link employees to members by email (requires migration 034)
try {
    $stmt = $pdo->query(
        'SELECT e.id, e.email, m.id as member_id
         FROM oretir_employees e
         JOIN members m ON e.email = m.email
         WHERE e.member_id IS NULL'
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nEmployees to link: " . count($rows) . "\n";
    foreach ($rows as $r) {
        echo "  Employee #{$r['id']} ({$r['email']}) → Member #{$r['member_id']}\n";
    }
    if (!$dryRun && count($rows) > 0) {
        $pdo->exec(
            'UPDATE oretir_employees e
             JOIN members m ON e.email = m.email
             SET e.member_id = m.id
             WHERE e.member_id IS NULL'
        );
        echo "  Done.\n";
    }
} catch (\Throwable $e) {
    echo "\nEmployees: SKIPPED (member_id column missing — run migration 034 first)\n";
    echo "  Error: " . $e->getMessage() . "\n";
}

// 3. Link vehicles to members via customer
$stmt = $pdo->query(
    'SELECT v.id, c.email, c.member_id
     FROM oretir_vehicles v
     JOIN oretir_customers c ON v.customer_id = c.id
     WHERE v.member_id IS NULL AND c.member_id IS NOT NULL'
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nVehicles to link: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  Vehicle #{$r['id']} (customer email: {$r['email']}) → Member #{$r['member_id']}\n";
}
if (!$dryRun && count($rows) > 0) {
    $pdo->exec(
        'UPDATE oretir_vehicles v
         JOIN oretir_customers c ON v.customer_id = c.id
         SET v.member_id = c.member_id
         WHERE v.member_id IS NULL AND c.member_id IS NOT NULL'
    );
    echo "  Done.\n";
}

// 4. Link appointments to members via customer
$stmt = $pdo->query(
    'SELECT a.id, a.reference_number, c.email, c.member_id
     FROM oretir_appointments a
     JOIN oretir_customers c ON a.customer_id = c.id
     WHERE a.member_id IS NULL AND c.member_id IS NOT NULL'
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nAppointments to link: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  Appointment #{$r['id']} ({$r['reference_number']}) → Member #{$r['member_id']}\n";
}
if (!$dryRun && count($rows) > 0) {
    $pdo->exec(
        'UPDATE oretir_appointments a
         JOIN oretir_customers c ON a.customer_id = c.id
         SET a.member_id = c.member_id
         WHERE a.member_id IS NULL AND c.member_id IS NOT NULL'
    );
    echo "  Done.\n";
}

echo "\nComplete" . ($dryRun ? " (dry run — no changes made)" : "") . ".\n";
