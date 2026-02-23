<?php
/**
 * Oregon Tires — Admin Customer Management
 * GET    /api/admin/customers.php          — List customers (paginated, searchable)
 * GET    /api/admin/customers.php?id=N     — Get single customer with vehicles + history
 * POST   /api/admin/customers.php          — Create customer
 * PUT    /api/admin/customers.php          — Update customer
 *
 * Replaces the old appointment-aggregated customer view.
 * Now uses oretir_customers table for persistent customer records.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    startSecureSession();
    $admin = requireAdmin();
    requireMethod('GET', 'POST', 'PUT');
    $db = getDB();

    $method = $_SERVER['REQUEST_METHOD'];

    // ─── GET: List or single customer ──────────────────────────────────────
    if ($method === 'GET') {
        // Single customer detail
        if (!empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $db->prepare('SELECT * FROM oretir_customers WHERE id = ?');
            $stmt->execute([$id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$customer) jsonError('Customer not found.', 404);

            $vStmt = $db->prepare('SELECT * FROM oretir_vehicles WHERE customer_id = ? ORDER BY created_at DESC');
            $vStmt->execute([$id]);
            $customer['vehicles'] = $vStmt->fetchAll(PDO::FETCH_ASSOC);

            $aStmt = $db->prepare('SELECT COUNT(*) FROM oretir_appointments WHERE customer_id = ?');
            $aStmt->execute([$id]);
            $customer['appointment_count'] = (int) $aStmt->fetchColumn();

            $raStmt = $db->prepare(
                'SELECT id, reference_number, service, preferred_date, preferred_time, status, created_at
                 FROM oretir_appointments WHERE customer_id = ? ORDER BY preferred_date DESC LIMIT 10'
            );
            $raStmt->execute([$id]);
            $customer['recent_appointments'] = $raStmt->fetchAll(PDO::FETCH_ASSOC);

            $roStmt = $db->prepare('SELECT COUNT(*) FROM oretir_repair_orders WHERE customer_id = ?');
            $roStmt->execute([$id]);
            $customer['ro_count'] = (int) $roStmt->fetchColumn();

            jsonSuccess($customer);
        }

        // List customers (paginated)
        $limit  = max(1, min(500, (int) ($_GET['limit'] ?? 50)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $search = sanitize((string) ($_GET['search'] ?? ''), 200);
        $sortBy = sanitize((string) ($_GET['sort_by'] ?? 'created_at'), 30);
        $sortOrder = strtoupper(sanitize((string) ($_GET['sort_order'] ?? 'DESC'), 4));

        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts, true)) $sortBy = 'created_at';
        if (!in_array($sortOrder, ['ASC', 'DESC'], true)) $sortOrder = 'DESC';

        $where = 'WHERE is_active = 1';
        $params = [];

        if (!empty($search)) {
            $where .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $s = "%{$search}%";
            $params = [$s, $s, $s, $s];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM oretir_customers {$where}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT c.*,
                  (SELECT COUNT(*) FROM oretir_vehicles WHERE customer_id = c.id) as vehicle_count,
                  (SELECT COUNT(*) FROM oretir_appointments WHERE customer_id = c.id) as appointment_count
                FROM oretir_customers c {$where}
                ORDER BY {$sortBy} {$sortOrder}
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $page = (int) floor($offset / $limit) + 1;
        jsonList($customers, $total, $page, $limit);
    }

    // ─── POST: Create customer ─────────────────────────────────────────────
    if ($method === 'POST') {
        verifyCsrf();
        $data = getJsonBody();

        $missing = requireFields($data, ['first_name', 'last_name', 'email']);
        if (!empty($missing)) jsonError('Missing required fields: ' . implode(', ', $missing));

        $firstName = sanitize((string) $data['first_name'], 100);
        $lastName  = sanitize((string) $data['last_name'], 100);
        $email     = sanitize((string) $data['email'], 255);
        $phone     = sanitize((string) ($data['phone'] ?? ''), 30);
        $language  = sanitize((string) ($data['language'] ?? 'english'), 20);
        $notes     = sanitize((string) ($data['notes'] ?? ''), 2000);

        if (!isValidEmail($email)) jsonError('Invalid email address.');
        if (!in_array($language, ['english', 'spanish'], true)) $language = 'english';

        $dupeStmt = $db->prepare('SELECT id FROM oretir_customers WHERE email = ? LIMIT 1');
        $dupeStmt->execute([$email]);
        if ($dupeStmt->fetch()) jsonError('A customer with this email already exists.', 409);

        $stmt = $db->prepare(
            'INSERT INTO oretir_customers (first_name, last_name, email, phone, language, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        );
        $stmt->execute([$firstName, $lastName, $email, $phone ?: null, $language, $notes ?: null]);

        jsonSuccess(['id' => (int) $db->lastInsertId(), 'message' => 'Customer created.']);
    }

    // ─── PUT: Update customer ──────────────────────────────────────────────
    if ($method === 'PUT') {
        verifyCsrf();
        $data = getJsonBody();

        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) jsonError('Customer ID is required.');

        $fields = [];
        $params = [];

        if (isset($data['first_name'])) { $fields[] = 'first_name = ?'; $params[] = sanitize((string) $data['first_name'], 100); }
        if (isset($data['last_name']))  { $fields[] = 'last_name = ?';  $params[] = sanitize((string) $data['last_name'], 100); }
        if (isset($data['email'])) {
            $email = sanitize((string) $data['email'], 255);
            if (!isValidEmail($email)) jsonError('Invalid email address.');
            $fields[] = 'email = ?'; $params[] = $email;
        }
        if (isset($data['phone']))    { $fields[] = 'phone = ?';    $params[] = sanitize((string) $data['phone'], 30) ?: null; }
        if (isset($data['language'])) {
            $lang = sanitize((string) $data['language'], 20);
            if (in_array($lang, ['english', 'spanish'], true)) { $fields[] = 'language = ?'; $params[] = $lang; }
        }
        if (isset($data['notes']))     { $fields[] = 'notes = ?';     $params[] = sanitize((string) $data['notes'], 2000) ?: null; }
        if (isset($data['is_active'])) { $fields[] = 'is_active = ?'; $params[] = (int) (bool) $data['is_active']; }

        if (empty($fields)) jsonError('No fields to update.');

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $db->prepare('UPDATE oretir_customers SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
        jsonSuccess(['message' => 'Customer updated.']);
    }

} catch (\Throwable $e) {
    error_log("Oregon Tires api/admin/customers.php error: " . $e->getMessage());
    jsonError('Server error', 500);
}
