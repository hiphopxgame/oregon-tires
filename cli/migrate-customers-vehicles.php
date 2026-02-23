<?php
/**
 * Oregon Tires — One-time Customer & Vehicle Migration
 * Extracts unique customers and vehicles from existing appointments
 * and populates oretir_customers + oretir_vehicles tables.
 *
 * Usage: php cli/migrate-customers-vehicles.php [--dry-run]
 *
 * Run AFTER migrate-009-ro-foundation.sql has been executed.
 */

declare(strict_types=1);

// ─── Bootstrap ───────────────────────────────────────────────────────────────
require_once __DIR__ . '/../public_html/includes/bootstrap.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

echo "Oregon Tires — Customer & Vehicle Migration\n";
echo str_repeat('=', 50) . "\n";
echo $dryRun ? "MODE: DRY RUN (no changes will be made)\n\n" : "MODE: LIVE\n\n";

$db = getDB();

// ─── Step 1: Extract unique customers by email ──────────────────────────────
echo "Step 1: Extracting unique customers from appointments...\n";

$stmt = $db->query(
    'SELECT email, first_name, last_name, phone, language,
            COUNT(*) as appointment_count,
            MIN(id) as first_appt_id,
            MAX(id) as last_appt_id
     FROM oretir_appointments
     WHERE email IS NOT NULL AND email != \'\'
     GROUP BY email
     ORDER BY MIN(created_at) ASC'
);
$uniqueEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "  Found " . count($uniqueEmails) . " unique email addresses\n";

$customersCreated = 0;
$customersSkipped = 0;
$customerMap = []; // email => customer_id

foreach ($uniqueEmails as $row) {
    $email = trim($row['email']);
    if (empty($email)) continue;

    // Check if customer already exists
    $existing = $db->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
    $existing->execute([$email]);
    $existingRow = $existing->fetch(PDO::FETCH_ASSOC);

    if ($existingRow) {
        $customerMap[$email] = (int) $existingRow['id'];
        $customersSkipped++;
        continue;
    }

    if (!$dryRun) {
        $ins = $db->prepare(
            'INSERT INTO oretir_customers (first_name, last_name, email, phone, language, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $ins->execute([
            trim($row['first_name']),
            trim($row['last_name']),
            $email,
            trim($row['phone'] ?? '') ?: null,
            $row['language'] ?? 'english',
        ]);
        $customerMap[$email] = (int) $db->lastInsertId();
    } else {
        $customerMap[$email] = -1; // placeholder for dry run
    }
    $customersCreated++;
}

echo "  Created: {$customersCreated} | Skipped (already exist): {$customersSkipped}\n\n";

// ─── Step 2: Extract vehicles from appointments ─────────────────────────────
echo "Step 2: Extracting vehicles from appointments...\n";

$stmt = $db->query(
    'SELECT email, vehicle_year, vehicle_make, vehicle_model, vehicle_vin,
            COUNT(*) as usage_count
     FROM oretir_appointments
     WHERE (vehicle_year IS NOT NULL AND vehicle_year != \'\')
        OR (vehicle_make IS NOT NULL AND vehicle_make != \'\')
        OR (vehicle_model IS NOT NULL AND vehicle_model != \'\')
     GROUP BY email, vehicle_year, vehicle_make, vehicle_model, vehicle_vin
     ORDER BY email ASC'
);
$vehicleRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "  Found " . count($vehicleRows) . " unique customer-vehicle combinations\n";

$vehiclesCreated = 0;
$vehiclesSkipped = 0;
$vehicleMap = []; // "email|year|make|model" => vehicle_id

foreach ($vehicleRows as $vRow) {
    $email = trim($vRow['email'] ?? '');
    if (empty($email) || !isset($customerMap[$email])) continue;

    $customerId = $customerMap[$email];
    if ($customerId <= 0) continue; // dry run placeholder

    $year  = trim($vRow['vehicle_year'] ?? '');
    $make  = trim($vRow['vehicle_make'] ?? '');
    $model = trim($vRow['vehicle_model'] ?? '');
    $vin   = trim($vRow['vehicle_vin'] ?? '');

    if (empty($year) && empty($make) && empty($model) && empty($vin)) continue;

    $mapKey = "{$email}|{$year}|{$make}|{$model}";

    // Check for existing vehicle
    if (!empty($vin)) {
        $existing = $db->prepare('SELECT id FROM oretir_vehicles WHERE vin = ? AND customer_id = ? LIMIT 1');
        $existing->execute([$vin, $customerId]);
    } else {
        $existing = $db->prepare('SELECT id FROM oretir_vehicles WHERE customer_id = ? AND year = ? AND make = ? AND model = ? LIMIT 1');
        $existing->execute([$customerId, $year, $make, $model]);
    }
    $existingVehicle = $existing->fetch(PDO::FETCH_ASSOC);

    if ($existingVehicle) {
        $vehicleMap[$mapKey] = (int) $existingVehicle['id'];
        $vehiclesSkipped++;
        continue;
    }

    if (!$dryRun) {
        $ins = $db->prepare(
            'INSERT INTO oretir_vehicles (customer_id, vin, year, make, model, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $ins->execute([
            $customerId,
            $vin ?: null,
            $year ?: null,
            $make ?: null,
            $model ?: null,
        ]);
        $vehicleMap[$mapKey] = (int) $db->lastInsertId();
    } else {
        $vehicleMap[$mapKey] = -1;
    }
    $vehiclesCreated++;
}

echo "  Created: {$vehiclesCreated} | Skipped (already exist): {$vehiclesSkipped}\n\n";

// ─── Step 3: Update appointments with customer_id and vehicle_id ────────────
echo "Step 3: Linking appointments to customer and vehicle records...\n";

$stmt = $db->query(
    'SELECT id, email, vehicle_year, vehicle_make, vehicle_model, vehicle_vin, customer_id
     FROM oretir_appointments
     WHERE email IS NOT NULL AND email != \'\'
     ORDER BY id ASC'
);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$skipped = 0;

foreach ($appointments as $appt) {
    // Skip if already linked
    if (!empty($appt['customer_id'])) {
        $skipped++;
        continue;
    }

    $email = trim($appt['email']);
    $customerId = $customerMap[$email] ?? null;
    if (!$customerId || $customerId <= 0) continue;

    $year  = trim($appt['vehicle_year'] ?? '');
    $make  = trim($appt['vehicle_make'] ?? '');
    $model = trim($appt['vehicle_model'] ?? '');
    $mapKey = "{$email}|{$year}|{$make}|{$model}";
    $vehicleId = $vehicleMap[$mapKey] ?? null;
    if ($vehicleId && $vehicleId <= 0) $vehicleId = null;

    if (!$dryRun) {
        $db->prepare('UPDATE oretir_appointments SET customer_id = ?, vehicle_id = ? WHERE id = ?')
           ->execute([$customerId, $vehicleId, $appt['id']]);
    }
    $updated++;
}

echo "  Updated: {$updated} | Skipped (already linked): {$skipped}\n\n";

// ─── Summary ─────────────────────────────────────────────────────────────────
echo str_repeat('=', 50) . "\n";
echo "Migration Summary:\n";
echo "  Customers created: {$customersCreated}\n";
echo "  Vehicles created:  {$vehiclesCreated}\n";
echo "  Appointments linked: {$updated}\n";

if ($dryRun) {
    echo "\n  ** DRY RUN — no changes were made **\n";
    echo "  Run without --dry-run to apply changes.\n";
} else {
    echo "\n  Migration complete!\n";
    echo "  You can now add FKs with:\n";
    echo "    ALTER TABLE oretir_appointments ADD CONSTRAINT fk_appt_customer FOREIGN KEY (customer_id) REFERENCES oretir_customers(id) ON DELETE SET NULL;\n";
    echo "    ALTER TABLE oretir_appointments ADD CONSTRAINT fk_appt_vehicle FOREIGN KEY (vehicle_id) REFERENCES oretir_vehicles(id) ON DELETE SET NULL;\n";
}

echo "\n";
