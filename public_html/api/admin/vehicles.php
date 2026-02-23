<?php
/**
 * Oregon Tires — Admin Vehicle Management
 * GET    /api/admin/vehicles.php?customer_id=N  — List vehicles for customer
 * GET    /api/admin/vehicles.php?id=N           — Get single vehicle
 * POST   /api/admin/vehicles.php                — Create vehicle
 * PUT    /api/admin/vehicles.php                — Update vehicle
 * DELETE /api/admin/vehicles.php?id=N           — Delete vehicle
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'PUT', 'DELETE');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET ───────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $db->prepare(
                'SELECT v.*, c.first_name, c.last_name, c.email as customer_email
                 FROM oretir_vehicles v
                 JOIN oretir_customers c ON c.id = v.customer_id
                 WHERE v.id = ?'
            );
            $stmt->execute([$id]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$vehicle) jsonError('Vehicle not found.', 404);
            jsonSuccess($vehicle);
        }

        $customerId = (int) ($_GET['customer_id'] ?? 0);
        if ($customerId <= 0) jsonError('customer_id is required.');

        $stmt = $db->prepare('SELECT * FROM oretir_vehicles WHERE customer_id = ? ORDER BY created_at DESC');
        $stmt->execute([$customerId]);
        jsonSuccess($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // ─── POST: Create vehicle ──────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $customerId = (int) ($data['customer_id'] ?? 0);
        if ($customerId <= 0) jsonError('customer_id is required.');

        $cStmt = $db->prepare('SELECT id FROM oretir_customers WHERE id = ?');
        $cStmt->execute([$customerId]);
        if (!$cStmt->fetch()) jsonError('Customer not found.', 404);

        $vin          = sanitize((string) ($data['vin'] ?? ''), 17);
        $year         = sanitize((string) ($data['year'] ?? ''), 4);
        $make         = sanitize((string) ($data['make'] ?? ''), 50);
        $model        = sanitize((string) ($data['model'] ?? ''), 50);
        $trimLevel    = sanitize((string) ($data['trim_level'] ?? ''), 100);
        $engine       = sanitize((string) ($data['engine'] ?? ''), 100);
        $transmission = sanitize((string) ($data['transmission'] ?? ''), 50);
        $driveType    = sanitize((string) ($data['drive_type'] ?? ''), 50);
        $bodyClass    = sanitize((string) ($data['body_class'] ?? ''), 50);
        $doors        = sanitize((string) ($data['doors'] ?? ''), 10);
        $tireFront    = sanitize((string) ($data['tire_size_front'] ?? ''), 30);
        $tireRear     = sanitize((string) ($data['tire_size_rear'] ?? ''), 30);
        $pressureFront = !empty($data['tire_pressure_front']) ? (int) $data['tire_pressure_front'] : null;
        $pressureRear  = !empty($data['tire_pressure_rear']) ? (int) $data['tire_pressure_rear'] : null;
        $mileage      = !empty($data['mileage']) ? (int) $data['mileage'] : null;
        $licensePlate = sanitize((string) ($data['license_plate'] ?? ''), 20);
        $color        = sanitize((string) ($data['color'] ?? ''), 30);
        $notes        = sanitize((string) ($data['notes'] ?? ''), 2000);

        if ($vin !== '' && !preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', $vin)) {
            jsonError('Invalid VIN format.');
        }

        $stmt = $db->prepare(
            'INSERT INTO oretir_vehicles
                (customer_id, vin, year, make, model, trim_level, engine, transmission,
                 drive_type, body_class, doors, tire_size_front, tire_size_rear,
                 tire_pressure_front, tire_pressure_rear, mileage, license_plate, color, notes,
                 created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([
            $customerId, $vin ?: null, $year ?: null, $make ?: null, $model ?: null,
            $trimLevel ?: null, $engine ?: null, $transmission ?: null,
            $driveType ?: null, $bodyClass ?: null, $doors ?: null,
            $tireFront ?: null, $tireRear ?: null,
            $pressureFront, $pressureRear, $mileage,
            $licensePlate ?: null, $color ?: null, $notes ?: null,
        ]);

        jsonSuccess(['id' => (int) $db->lastInsertId(), 'message' => 'Vehicle created.']);
    }

    // ─── PUT: Update vehicle ───────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Vehicle ID is required.');

        $fields = [];
        $params = [];

        $strFields = ['vin' => 17, 'year' => 4, 'make' => 50, 'model' => 50, 'trim_level' => 100,
                       'engine' => 100, 'transmission' => 50, 'drive_type' => 50, 'body_class' => 50,
                       'doors' => 10, 'tire_size_front' => 30, 'tire_size_rear' => 30,
                       'license_plate' => 20, 'color' => 30, 'notes' => 2000];

        foreach ($strFields as $f => $maxLen) {
            if (isset($data[$f])) { $fields[] = "{$f} = ?"; $params[] = sanitize((string) $data[$f], $maxLen) ?: null; }
        }

        $intFields = ['tire_pressure_front', 'tire_pressure_rear', 'mileage'];
        foreach ($intFields as $f) {
            if (isset($data[$f])) { $fields[] = "{$f} = ?"; $params[] = $data[$f] !== '' && $data[$f] !== null ? (int) $data[$f] : null; }
        }

        if (empty($fields)) jsonError('No fields to update.');

        if (isset($data['vin']) && $data['vin'] !== '' && !preg_match('/^[A-HJ-NPR-Z0-9]{17}$/i', (string) $data['vin'])) {
            jsonError('Invalid VIN format.');
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $db->prepare('UPDATE oretir_vehicles SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
        jsonSuccess(['message' => 'Vehicle updated.']);
    }

    // ─── DELETE ────────────────────────────────────────────────────────────
    if ($method === 'DELETE') {
        verifyCsrf();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) jsonError('Vehicle ID is required.');

        $roCheck = $db->prepare('SELECT COUNT(*) FROM oretir_repair_orders WHERE vehicle_id = ?');
        $roCheck->execute([$id]);
        if ((int) $roCheck->fetchColumn() > 0) {
            jsonError('Cannot delete vehicle with existing repair orders.', 409);
        }

        $db->prepare('DELETE FROM oretir_vehicles WHERE id = ?')->execute([$id]);
        jsonSuccess(['deleted' => $id]);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/vehicles.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
